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
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Cron;

class GiftMail {

	/*
	* @var \Psr\Log\LoggerInterface $logger
	*/
    protected $logger;

	/*
	* @var \Psr\Log\LoggerInterface $logger
	*/
    protected $helperMail;


	/*
	* @var Vorders $_vorder
	*/
    protected $_vorder;

    /*
    * @param \Psr\Log\LoggerInterface $logger, 
    * @param \Ced\GiftCard\Helper\Email $helperMail,
    * @param Vorders $_vorder
    */
    public function __construct(
    	\Psr\Log\LoggerInterface $logger,
        \Ced\GiftCard\Model\GiftCoupon $_giftCoupon,
        \Ced\GiftCard\Helper\Email $helperMail,
    	\Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->logger = $logger;
        $this->_giftCoupon = $_giftCoupon;
        $this->helperMail = $helperMail;
    	$this->order = $order;
    }

    public function execute() {
        $this->logger->info('START Gift Card CRON');
    	try{
		    $todaysDate = date('y-m-d');
			$collection = $this->_giftCoupon->getCollection()->addFieldToFilter('delivery_date', $todaysDate);

			/*check if value is exist*/
			if (!empty($collection->getData())) {
				foreach ($collection->getData() as $i => $maildata) {

					if (null !== $maildata['increment_id']) {
						$order = $this->order->loadByIncrementId($maildata['increment_id']);

						foreach ($order->getItems() as $item){
						    if ($item->getIsVirtual() == 1 && $item->getProductType() == 'giftcard'){

						    	$info_buyRequest = $item->getProductOptions()['info_buyRequest'];
						    	if (isset($info_buyRequest['giftcard'])){
						    		if (!empty($info_buyRequest)){

						    			$info_buyRequest['expiration_date'] = $maildata['expiration_date'];

						    			$info_buyRequest['incrementId'] = $maildata['increment_id'];
						    			$info_buyRequest['gift_price'] = $info_buyRequest['gift_price'] * $item->getQtyOrdered();


						    			$this->helperMail->email($info_buyRequest, $maildata['code']);
										$this->logger->info('Gift Card  Email sent for coupon code -> '. $maildata['code']);

						    		}
						    	}
						    }
						}
					}
				}
			}
    	}catch(\Exception $e){
        	$this->logger->info($e->getMessage());
        	$this->logger->info('Exception Occure IN Gift Card ');
    	}

    	$this->logger->info('END Gift Card CRON');
    }
}