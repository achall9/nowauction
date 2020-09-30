<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/3/19
 * Time: 9:05 PM
 */

namespace Ced\Auction\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ced\Auction\Model;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Customer\Model\Session;
use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Helper\SendEmail;
use Ced\CsMarketplace\Model\VproductsFactory;

class Data extends AbstractHelper
{
    public function __construct(Context $context,
                                Model\ResourceModel\Auction\CollectionFactory $auctionCollection,
                                ProductRepository $productCollection,
                                DateTime $datetime,
                                TimezoneInterface $timezoneInterface,
                                CollectionFactory $bidDetails,
                                \Ced\Auction\Model\Winner $winner,
                                ConfigData $configHelper,
                                SendEmail $emailHelper,
                                Session $customerSession,
                                VproductsFactory $vproductsFactory,
                                \Ced\CustomizeAuction\Helper\Data $customizeHelper
    )
    {
        $this->customerSession = $customerSession;
        $this->productCollection = $productCollection;
        $this->auctionCollection = $auctionCollection;
        $this->dateTime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->bidDetails = $bidDetails;
        $this->winner = $winner;
        $this->configHelper = $configHelper;
        $this->emailHelper = $emailHelper;
        $this->vproductsFactory = $vproductsFactory;
        $this->customizeHelper = $customizeHelper;
        parent::__construct($context);
    }

    public function closeAuction()
    { 
        $auctionRunning = $this->auctionCollection->create()->addFieldToFilter('status', 'processing');
        if (count($auctionRunning->getData()) != false) {
            foreach ($auctionRunning as $auction) {

                $endTime = $auction->getEndDatetime();

                $date = $this->dateTime->gmtDate();
                $currenttime = $this->timezoneInterface
                    ->date(new \DateTime($date))
                    ->format('Y-m-d H:i:s');

                $isCloseAuction = $this->customerSession->getIsCloseAuction();
                if ($endTime <= $currenttime || $isCloseAuction == 1) {

                    if (count($auction->getData()) != false) {
                        $auction->setData('status', 'closed');
                        $auction->setData('product_sold', $endTime);
                        $auction->save();
                    }

                    $bidExist = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                        ->addFieldToFilter('status', 'bidding');

                    if (count($bidExist->getData()) != false) {

                        $bid = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                            ->addFieldToFilter('status', 'bidding')
                            ->setOrder('bid_price', 'ASC')->getLastItem();

                        if (count($bid->getData()) != false) {
                            $bid->setStatus('won');
                            $bid->save();
                        }

                        $allWinnerData = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                            ->addFieldToFilter('status', 'bidding');

                        if (count($allWinnerData->getData()) != false) {
                            $allWinnerData->setDataToAll('status', 'biddingClosed');
                            $allWinnerData->save();
                        }

                        $winnerData = [];
                        $winnerData['product_id'] = $auction->getProductId();
                        $winnerData['customer_id'] = $bid['customer_id'];
                        $winnerData['customer_name'] = $bid['customer_name'];
                        $winnerData['auction_price'] = $auction->getMaxPrice();
                        $winnerData['bid_date'] = $endTime;
                        $winnerData['status'] = 'not purchased';
                        $winnerData['winning_price'] = $bid['total_bid_price'];
                        $winnerData['add_to_cart'] = false;
                        $winnerData['shipping_detail'] = $bid['shipping_detail'];
                        $winnerData['payment_detail'] = $bid['payment_detail'];
                        $winnerData['shipping_amount'] = $bid['shipping_amount'];
                        $winnerData['payment_token'] = $bid['payment_token'];

                        if ($this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId())) {
                            $winnerData['vendor_id'] = $this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId());
                        }

                        $this->winner->setData($winnerData);
                        $this->winner->save();

                        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/closeAuction.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info($winnerData);

                        $this->customizeHelper->createMageOrder($winnerData);

                        $enableMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_winner'), true);
                        if ($enableMail) {
                            $this->emailHelper->sendMailtoWinner(
                                $this->customerSession->getCustomerId(),
                                $auction->getProductId());
                        }
                    }
                }
                $this->customerSession->unsIsCloseAuction();
            }
        }
        return false;
    }
}