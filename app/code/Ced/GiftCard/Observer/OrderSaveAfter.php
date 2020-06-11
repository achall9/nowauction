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
 * Class OrderSaveAfter
 * @package Ced\GiftCard\Observer
 */
Class OrderSaveAfter implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Ced\GiftCard\Model\GiftCoupon
     */
    protected $_giftCoupon;

    /**
     * OrderSaveAfter constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\GiftCard\Model\GiftCoupon $_giftCoupon
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\GiftCard\Model\GiftCoupon $_giftCoupon
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->_giftCoupon = $_giftCoupon;
    }

    /**
     * @param $amount
     * @param null $store
     * @param null $currencyCode
     * @return float
     */
    public function getFormatedPrice($amount, $store = null, $currencyCode = null)
    {
        return $this->priceCurrency->convert($amount, $store, $currencyCode);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->getCsGiftcouponAmount() && $order->getCsGiftcouponCode()) {
            if (!$order->getData('cs_giftcoupon_applied')) {
                $giftCoupon = $this->_giftCoupon->load($order->getCsGiftcouponCode(), 'code');
                try {
                    if ($giftCoupon->getCurrentAmount() >= $order->getCsGiftcouponAmount()) {
                        $currentAmount = $giftCoupon->getCurrentAmount() - $order->getCsGiftcouponAmount();
                        $giftCoupon->setCurrentAmount($currentAmount);
                        $giftCoupon->setTimeUsed($giftCoupon->getTimeUsed() + 1);
                        $giftCoupon->save();
                        $order->setData('cs_giftcoupon_applied', true)->save();
                    } else {
                        throw new \Exception(__('Coupon Code %1 dosent have sufficient Amount.', $order->getCsGiftcouponCode()));
                    }
                } catch (\Exception $e) {
                    throw new \Exception(__('Unable to apply gift coupon.'));
                }
            }
        } 
    }

}
