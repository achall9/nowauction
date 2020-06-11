<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Custom
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Custom\Cron;

use Ced\Custom\Helper\Notification as NotificationHelper;

class Notification {

    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $_quotesFactory;
    /*
    * @var \Psr\Log\LoggerInterface $logger
    */
    protected $logger;

    protected  $_storeIds = [];
    protected  $integrationKey  = null;
    /*
    * @var NotificationHelper $_notificationHelper
    */
    protected $_notificationHelper;

    /*
    * @param \Psr\Log\LoggerInterface $logger,
    */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        NotificationHelper $_notificationHelper
    ) {
        $this->logger = $logger;
        $this->_notificationHelper = $_notificationHelper;
        $this->_quotesFactory = $quotesFactory;
        /*$this->_storeIds = [0,1,27];*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/notification_testing.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $this->testlogger = $logger;

    }

    public function execute() {

        try{
            $this->testlogger->info('==========================START==========================');

            $collection = $this->_quotesFactory->create();
            $timezone = $this->_notificationHelper->getTimezoneInterface();
            $collection->prepareForAbandonedReport($this->_storeIds);

            $today = $timezone->date()->format('Y-m-d H:i:s');
            $this->logger->info('Notification cron is running at '.$today);
            $this->integrationKey = $this->_notificationHelper->getNotificationIntegrationKey();
            $collForE1 = clone $collection;
            $collForE2 = clone $collection;
            $collForE3 = clone $collection;

            $hours_96 = strtotime($today)- 96*60*60;/*96hours*/
            $eventCount = 3;
            $this->loopAndCallApi($collForE3, $hours_96, $eventCount);

            $hours_24 = strtotime($today)- 24*60*60;/*24hours*/
            $eventCount = 2;
            $this->loopAndCallApi($collForE2, $hours_24, $eventCount);

            $mins_90 = strtotime($today) - 90*60;/*90mins*/
            $eventCount = 1;
            $this->loopAndCallApi($collForE1, $mins_90, $eventCount);

        }catch(\Exception $e){
            $this->testlogger->info($e->getMessage());
            $this->logger->info($e->getMessage());
            $this->logger->info('Exception Occure');
        }

        $this->testlogger->info('==========================END==========================');
        $this->logger->info('Notification cron is END at '.$today);
    }
    private function loopAndCallApi($collection, $mins, $event_count){
        $timezone = $this->_notificationHelper->getTimezoneInterface();

        $today_after_mins = $timezone->date((int) $mins, 
            null, 
            false
        )->format('Y-m-d H:i:s');
        
        $today_after_mins_utc = $timezone->convertConfigTimeToUtc($today_after_mins);

        $condition = [
            'lt'=> $today_after_mins_utc
        ];

        $this->testlogger->info('$mins => '.$mins);
        $this->testlogger->info('$event_count => '.$event_count);
        $this->testlogger->info('$condition => '.json_encode($condition));
        $this->testlogger->info('$timezone->date((int) $mins) => '.$timezone->date((int) $mins)->format('Y-m-d H:i:s'));
        $collection->addFieldToFilter(
            'updated_at',
            [$condition]
        )->addFieldToFilter(
            'notification_flag',
            ['eq'=>$event_count-1]
        );

        foreach($collection as $cart){
            try{
                $this->testlogger->info('Cart ID '.$cart->getEntityId());
                $this->logger->info('Cart ID '.$cart->getEntityId());
                $quote = $this->_notificationHelper->getQuote(
                    (int)$cart->getEntityId(),
                    [$cart->getStoreId()]
                );

                $customer = $this->_notificationHelper->getCustomer($cart->getCustomerId());
                $header = [
                    NotificationHelper::INTEGRATION_TOKEN => $this->integrationKey
                ];

                $params = [
                    NotificationHelper::CUSTOMER => $customer,
                    NotificationHelper::CART => $quote,
                    NotificationHelper::EVENT_COUNT => $event_count
                ];

                $this->_notificationHelper->callApi(NotificationHelper::ABANDOND_CART_URI, $header, $params);

                $cart->setNotificationFlag($event_count)->save();

            }catch(\Exception $e){
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
            }
        }
    }
}
