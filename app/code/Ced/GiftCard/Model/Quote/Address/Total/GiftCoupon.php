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
namespace Ced\GiftCard\Model\Quote\Address\Total;

/**
 * Class GiftCoupon
 * @package Ced\GiftCard\Model\Quote\Address\Total
 */
class GiftCoupon extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     *
     */
    const APPLIED = 1;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
 
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency [description]
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
         
        $this->checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param $amount
     * @return float
     */
    public function getFormatedPrice($amount)
    {
        return $this->_priceCurrency->convert($amount);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|\Magento\Quote\Model\Quote\Address\Total\AbstractTotal
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        try{

            if (!is_null($quote->getCsGiftcouponCode())
                && $quote->getCsGiftcouponCode() !== ''){

            	$items = $quote->getAllItems();
	            foreach ($items as $key => $item) {
	                if ($item->getProductType() == 'giftcard') {
                		$this->resetGiftCoupon($quote);	
        				return $this;	                    
	                } 
	            }
                if (true){
                    $discountAmount = $quote->getCsGiftcouponAmount();

                    if ($discountAmount > 0){
                        
                        if (!($this->checkoutSession->getCedGiftcouponAmount())){
                            $this->checkoutSession->setCedGiftcouponAmount($discountAmount);
                        }else{
                            $this->checkoutSession->unsCedGiftcouponAmount();
                        }
                        /*check if value is already set*/

                        $total->addTotalAmount('cs_giftcoupon_amount', -$this->getFormatedPrice($discountAmount));

                        $total->addBaseTotalAmount('cs_giftcoupon_amount', -$discountAmount);
                        $total->setGrandTotalAmount('cs_giftcoupon_amount', -$this->getFormatedPrice($discountAmount));

                        $total->setDiscountDescription($quote->getCsGiftcouponCode());

                        $total->setDiscountAmount(-$this->getFormatedPrice($discountAmount));
                        $total->setBaseDiscountAmount(-$discountAmount);

                        $total->setSubtotalWithDiscount(
                            $total->getSubtotalWithDiscount() -
                            $this->getFormatedPrice($discountAmount)
                        );
                        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotalWithDiscount() - $discountAmount);

                        $total->setCsGiftcouponAmount($discountAmount);

                        $total->setGrandtotalWithDiscount(
                            $quote->getGrandtotalWithDiscount() -
                            $this->getFormatedPrice($discountAmount)
                        );
                        $total->setBaseGrandtotalWithDiscount($quote->getBaseGrandtotalWithDiscount() - $discountAmount);

                        if ($quote->getCsGiftcouponApplied() != self::APPLIED){
                            $quote->setCsGiftcouponApplied(self::APPLIED);
                        }
                    }
                }
            }
        }catch(\Exception $e){

        }

        return $this;
    }

    /**
     * @param $quote
     * @throws \Exception
     */
    public function resetGiftCoupon($quote){

        $quote->setCsGiftcouponAmount(null);
        $quote->setCsGiftcouponCode(null);
        $quote->setCsGiftcouponApplied(null);
        $this->checkoutSession->getQuote()->collectTotals()->save();
    }

    /**
     * @param $quote
     * @return float
     * @throws \Exception
     */
    public function getCouponPrice($quote){
        /*if getCouponPrice is greater than the Subtotal With Discount then */

        if ($quote->getCsGiftcouponAmount() > $quote->getGrandTotal()){

            $couponPrice = 0;
            if ($quote->getCsGiftcouponApplied() == 1) {
                $couponPrice = $quote->getCsGiftcouponAmount();
            }else{
                $this->resetGiftCoupon($quote);
            }
        }else{
            $couponPrice = $quote->getCsGiftcouponAmount();
        }
        return (float)$couponPrice;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'gift_coupon_code',
            'title' => __('Gift Coupon %1',$quote->getCsGiftcouponCode()),
            'value' =>  $this->getFormatedPrice($quote->getCsGiftcouponAmount())
        ];
    }

    /**
     * get label
     * @return string
     */
    public function getLabel()
    {
        return __('Gift Coupon ');
    }
}