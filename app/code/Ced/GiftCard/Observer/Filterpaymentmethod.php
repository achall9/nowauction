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
namespace Ced\GiftCard\Observer; 

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Filterpaymentmethod
 * @package Ced\GiftCard\Observer
 */
class Filterpaymentmethod implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeconfig;

    /** 
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeconfig
     */
    public function __construct( 
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeconfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession; 
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $needToCheck =$this->scopeconfig->getValue(
                'giftcard/giftcard/enable_gift_allowed_payment_methods'
            );

            if($needToCheck == 1){
                $method = $observer->getEvent()->getMethodInstance();
                $result = $observer->getEvent()->getResult();

                $showPaymentMethods=$this->scopeconfig->getValue(
                    'giftcard/giftcard/gift_allowed_payment_methods'
                );
                $showPaymentMethods = explode(',', $showPaymentMethods);

                /** @var \Magento\Quote\Model\Quote  */
                $quote = $this->checkoutSession->getQuote();

                $onlyAllowOnlinePaymentmethod = false;
                $hasGiftCard = false;
                foreach ($quote->getAllItems() as $item){
                    if ($item->getProductType()  == 'giftcard'){
                        $onlyAllowOnlinePaymentmethod = true;
                        $hasGiftCard = true;
                        break;
                    }
                }
                if ($hasGiftCard){
                    if (!empty($showPaymentMethods)) {
                        if (!in_array($method->getCode(), $showPaymentMethods)) {
                            $result->setData('is_available', false);
                        }
                    }
                }
            }
        }catch(\Exception $e){ }
    }
}