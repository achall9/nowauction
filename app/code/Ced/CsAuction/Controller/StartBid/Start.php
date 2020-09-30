<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsAuction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Controller\StartBid;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Ced\Auction\Model\BidDetails;
use Ced\Auction\Model\ResourceModel;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\AuctionFactory;
use Magento\Framework\Controller\ResultFactory;
use Ced\Auction\Model\Winner;
use Ced\Auction\Helper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Ced\CsMarketplace\Model\Vproducts;

class Start extends \Ced\Auction\Controller\StartBid\Start
{
    public function __construct(
        AuctionFactory $auctionFactory,
        Context $context,
        Session $session,
        PageFactory $resultPageFactory,
        BidDetails $bidDetails,
        Auction $auction,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        Winner $winner,
        ResourceModel\BidDetails $bidResourceModel,
        ResourceModel\Winner $winnerResource,
        Helper\SendEmail $emailHelper,
        Helper\ConfigData $configHelper,
        DateTime $datetime,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        Data $data,
        TimezoneInterface $timezoneInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Vproducts $vproducts,
        Helper\Data $helper
    )
    {
        $this->vproduct = $vproducts;
        $this->auctionFactory = $auctionFactory;
        $this->helper = $helper;
        parent::__construct($auctionFactory, $context, $session, $resultPageFactory, $bidDetails, $auction, $auctionCollection, $resourceBidDetails, $winner, $bidResourceModel, $winnerResource, $emailHelper, $configHelper, $datetime, $winnerCollection, $data, $timezoneInterface, $resultJsonFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsExceptionhistory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPost();
        if ($data['remove']) {
            if (!empty($data['auction']) && !empty($data['status'])) {
                $auction = $this->auctionFactory->create()->load($data['auction']);
						 
                if($data['status'] == 'not started'){
                    $status = 'processing';
                    $auction->setStatus($status);
                    $auction->save();
                }
                else
				{
				    $auction->setEndDatetime($data['finalDate']);
					  $auction->save();
                    $this->helper->closeAuction();
                }
                return $resultJson->setData('success');
            }
        }

        $call = $this->setError();
        if (isset($call)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;

        }

        $productId = $this->getRequest()->getParam('productId');

        $bidId = $this->resourceBidDetails->create()
            ->addFieldToFilter('customer_id', $this->customerSesion->getCustomerId())
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'bidding')
            ->getFirstItem()->getId();
        $this->bidDetails->load($bidId);
        $this->bidDetails->addData($this->getBidData());
        $this->bidResourceModel->save($this->bidDetails);
        $this->messageManager->addSuccessMessage(__('You Successfully Placed The Bid'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBidData()
    {
        $date = $this->datetime->gmtDate();
        $currenttime = $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');

        $data = [];
        $data['product_id'] = $this->getRequest()->getParam('productId');
        $data['customer_id'] = $this->customerSesion->getCustomerId();
        $data['customer_name'] = $this->customerSesion->getCustomerData()->getFirstname();
        $data['bid_price'] = $this->getRequest()->getParam('bidprice');
        $data['bid_date'] = $currenttime;
        $data['status'] = 'bidding';
        if($this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'))){
            $data['vendor_id'] = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));
        }

        $productId = $this->getRequest()->getParam('productId');
		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		//$productCollection = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
		//$productPriceById = $productCollection->getPrice();

		
		
        $bidId = $this->resourceBidDetails->create()
            ->addFieldToFilter('customer_id', $this->customerSesion->getCustomerId())
            ->addFieldToFilter('product_id', $productId);
        if ($bidId->count() == 0) {
            $data['winner'] = 1;
        } else {
            $bid = $bidId->getData();
            $data['winner'] = $bid[0]['winner'] + 1;
        }
        if($this->getAuctionProduct()->getMaxPrice()!= null && $this->getAuctionProduct()->getMaxPrice() != 0 ) {
            if ($this->getRequest()->getParam('bidprice') >= $this->getAuctionProduct()->getMaxPrice()) {
                $winnerData = [];
                $winnerData['product_id'] = $this->getRequest()->getParam('productId');
                $winnerData['customer_id'] = $this->customerSesion->getCustomerId();
                $winnerData['customer_name'] = $this->customerSesion->getCustomerData()->getFirstname();
                $winnerData['bid_date'] = $currenttime;
                $winnerData['status'] = 'not purchased';
                $winnerData['auction_price'] = $this->getAuctionProduct()->getMaxPrice();
                $winnerData['winning_price'] = $this->getRequest()->getParam('bidprice');
                $winnerData['add_to_cart'] = false;
                //$id = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));

                if ($this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'))) {
                    $winnerData['vendor_id'] = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));
                }

                $data['status'] = 'won';
                $this->_eventManager->dispatch('auction_winner', ['winner_data' => $winnerData, 'winner' => $this->winner]);
                $this->winnerResource->save($this->winner);

                $allWinnerData = $this->resourceBidDetails->create()->addFieldToFilter('product_id', $this->getRequest()->getParam('productId'))
                    ->addFieldToFilter('status', 'bidding');
                $allWinnerData->setDataToAll('status', 'biddingClosed');
                $allWinnerData->save();

                $this->messageManager->addSuccessMessage(__('You Won This Bid'));
                $this->closeAuction();

                $enableMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_winner'), true);
                if ($enableMail) {
                    $this->emailHelper->sendMailtoWinner(
                        $this->customerSesion->getCustomerId(),
                        $this->getRequest()->getParam('productId'));
                }

                /*if ($this->customerSesion->getVendorId()) {
                    $this->emailHelper->sendWinningMailtoVendor(
                        $this->customerSesion->getVendorId(),
                        $this->getRequest()->getParam('productId'));
                }*/

                $enableAdminMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_admin_auctionclosed'), true);
                if($enableAdminMail){
                    $this->emailHelper->sendAuctionClosedMailtoAdmin(
                        $this->customerSesion->getCustomerId(),
                        $this->getRequest()->getParam('productId'));
                }
            }
        }
        return $data;
    }

}
