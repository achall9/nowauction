<?php
namespace Ced\CsAuction\Controller\PreAucionList;

use Ced\Auction\Model\ResourceModel;
use Ced\Auction\Model\Winner;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{
    protected $bidDetails;
    protected $customizeHelper;
    public $timezoneInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ced\Auction\Model\BidDetailsFactory $bidDetails,
        \Ced\CustomizeAuction\Helper\Data $customizeHelper,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        Winner $winner,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        ResourceModel\Winner $winnerResource,
        ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface
    ) {
        $this->bidDetails = $bidDetails;
        $this->customizeHelper = $customizeHelper;
        $this->auctionCollection = $auctionCollection;
        $this->winner = $winner;
        $this->vproduct = $vproducts;
        $this->winnerResource = $winnerResource;
        $this->resourceBidDetails = $resourceBidDetails;
        $this->datetime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    public function execute()
    {    
        $rowId = $this->getRequest()->getParam('id');
        $bidDetails = $this->bidDetails->create()->load($rowId);
        $orderData = [];
        $orderData['product_id'] = $bidDetails->getProductId();
        $orderData['customer_id'] = $bidDetails->getCustomerId();
        $orderData['winning_price'] = $bidDetails->getTotalBidPrice();
        $orderData['shipping_detail'] = $bidDetails->getShippingDetail();
        $orderData['payment_detail'] = $bidDetails->getPaymentDetail();
        $orderData['shipping_amount'] = $bidDetails->getShippingAmount();
        $orderData['payment_token'] = $bidDetails->getPaymentToken();

        $auctionCollection = $this->auctionCollection->create()->addFieldToFilter('product_id', $bidDetails->getProductId())->getFirstItem();

        if($auctionCollection->getStatus() == 'closed' || $auctionCollection->getStatus() == 'cancelled'){
            $this->messageManager->addErrorMessage('Order has been placed by someone. Already closed this bid.');  
        } else {

            $date = $this->datetime->gmtDate();
            $currenttime = $this->timezoneInterface->date(new \DateTime($date))->format('Y-m-d H:i:s');

            $this->customizeHelper->createMageOrder($orderData);

            $auctionCollection->setStatus('closed');
            $auctionCollection->setProductSold($currenttime);
            $auctionCollection->save();
            
            $winnerData = [];
            $winnerData['product_id'] = $bidDetails->getProductId();
            $winnerData['customer_id'] = $bidDetails->getCustomerId();
            $winnerData['customer_name'] = $bidDetails->getCustomerName();
            $winnerData['bid_date'] = $currenttime;
            $winnerData['status'] = 'order placed';
            $winnerData['auction_price'] = $auctionCollection->getMaxPrice();
            $winnerData['winning_price'] = $bidDetails->getBidPrice();
            $winnerData['add_to_cart'] = false;

            if($this->vproduct->getVendorIdByProduct($bidDetails->getProductId())){
                $winnerData['vendor_id'] = $this->vproduct->getVendorIdByProduct($bidDetails->getProductId());
            }

            $this->_eventManager->dispatch('auction_winner', ['winner_data' => $winnerData, 'winner' => $this->winner]);
            $this->winnerResource->save($this->winner);

            $allWinnerData = $this->resourceBidDetails->create()->addFieldToFilter('product_id', $bidDetails->getProductId())
                ->addFieldToFilter('status', 'bidding');
            $allWinnerData->setDataToAll('status', 'biddingClosed');
            $allWinnerData->save();


            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/preauctionorder.log');
                            $logger = new \Zend\Log\Logger();
                            $logger->addWriter($writer);
                            $logger->info($orderData);

            $this->messageManager->addSuccessMessage(__('Order has been placed successfully'));
        }
        return $this->_redirect('csauction/auctionlist/index');   
    }
}