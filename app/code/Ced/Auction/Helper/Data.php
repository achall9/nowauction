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
                                VproductsFactory $vproductsFactory
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

                if ($endTime <= $currenttime) {

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
                        $winnerData['winning_price'] = $bid['bid_price'];
                        $winnerData['add_to_cart'] = false;

                        if ($this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId())) {
                            $winnerData['vendor_id'] = $this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId());
                        }

                        $this->winner->setData($winnerData);
                        $this->winner->save();


                        $enableMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_winner'), true);
                        if ($enableMail) {
                            $this->emailHelper->sendMailtoWinner(
                                $this->customerSession->getCustomerId(),
                                $auction->getProductId());
                        }
                    }
                }
            }
        }
        return false;
    }
}