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
 * @category  Ced
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class Applycoupon
 * @package Ced\GiftCard\Controller\Checkout
 */
class Applycoupon extends \Magento\Framework\App\Action\Action
{     
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftCoupon $_giftCoupon
     */
    protected $_giftCoupon;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;


    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @deprecated 101.1.0
     */
    protected $priceCurrency;

    /**
     * Applycoupon constructor.
     * @param Context $context
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\Product $_product
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\GiftCard\Model\GiftCoupon $_giftCoupon
     */
    public function __construct(
        Context $context,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $_product,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\GiftCard\Model\GiftCoupon $_giftCoupon
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_giftCoupon = $_giftCoupon; 
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_product = $_product;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @note: check if quote contain gift card product
     *
     * @param $quote
     * @return array
     */
    public function initialCheck($quote)
    {
        $items = $quote->getAllItems();
        /*check for email id*/
        $quote->getBillingAddress();
        $quote->getShippingAddress();
        if ($quote->getCouponCode()){
            return [
                'result' => false,
                'message' => __('Error: Cannot apply more than one coupon codes on same order.')
            ];
        }
        foreach ($items as $key => $item) {
            if ($item->getProductType() == 'giftcard') {
                return ['result'=>false, 'message' => __('Error: Coupon code not available for gift card product')];
            }
            $product =  $this->_product->load($item->getProduct()->getEntityId());
            if ((int)$product->getIsGiftCardAllowed() != 1){
                return [
                    'result'=>false,
                    'message' => __('Error: The Product %1s does\'t allow checkout with gift coupon.',$product->getName())
                ];
            }
        }
        return [
            'result'=> true,
            'message' => __('Success')
        ];
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    { 
        try{
            $quote = $this->checkoutSession->getQuote();
            $resultJson = $this->resultJsonFactory->create();

            $coupon = $this->getRequest()->getParam('coupon');

            /*check if coupon code is already set */

            if ($coupon == 'remove') {

                if (null != $quote->getCsGiftcouponCode()) {
                    $code = $quote->getCsGiftcouponCode();

                    # remove previous coupon code and set coupon price to 0...
                    $quote->setCsGiftcouponCode(null);
                    $quote->setCsGiftcouponApplied(null);
                    $quote->setCsGiftcouponAmount(null);
                    $quote->save();

                    $this->checkoutSession->getQuote()->collectTotals()->save();
                    $response = __('Success: Removed Coupon code %1',$code);

                } else{
                    $response = __('Success: Coupon code is already removed.');
                }
                $resultJson->setData($response);
                return $resultJson;
            }

            /*perform some initial checks*/
            $initialCheck = $this->initialCheck($quote);
          
            if ($initialCheck['result']){

                $cartId = $this->getRequest()->getParam('quoteId');
                $couponCode = $this->getRequest()->getParam('couponCode');
                if ($couponCode) {
                    $giftCoupon = $this->_giftCoupon->load($couponCode, 'code');
                    $currentAmount = ($giftCoupon->getCurrentAmount() > 0)?$giftCoupon->getCurrentAmount():0;

                    $isValidCouponCode = $this->isValidCouponCode($giftCoupon);
                   
                    if ($isValidCouponCode['result']) {
                        $grandTotal = $quote->getBaseGrandTotal();
                        $amount = ($currentAmount > $grandTotal)? ($grandTotal) :($currentAmount);

                        $quote->setCsGiftcouponCode($couponCode);
                        $quote->setCsGiftcouponAmount($amount);
                        $quote->save();

                        $this->checkoutSession->getQuote()->collectTotals()->save();

                        $response = __('Success: Your coupon was successfully applied.');
                    } else{
                        $response = __($isValidCouponCode['message']);
                    }
                }
            }else if (!$initialCheck['result'] && isset($initialCheck['message'])){
                $response = $initialCheck['message'];
            }else{
                $response = __('Error: Unable to apply coupon code.');
            }
        }catch(\Exception $e){ 
            $response = __('Error: Unable to apply coupon code.');             
            $response = $e->getMessage();
        }
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @param $quote
     * @param $giftCoupon
     * @return mixed
     */
    public function getCouponPrice($quote, $giftCoupon){

        /*if getCouponPrice is greater than the Subtotal With Discount then */
        if ($giftCoupon->getCouponPrice() >= $quote->getSubtotalWithDiscount() ){
            $couponPrice = $quote->getSubtotalWithDiscount();
        }else{
            $couponPrice = $quote->getSubtotalWithDiscount() -  $giftCoupon->getCouponPrice();
        }

        return $couponPrice;
    }

    /**
     * @param $amount
     * @return float
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * @param $giftCoupon
     * @return array
     */
    public function isValidCouponCode($giftCoupon)
    {
        try{ 
            if (count($giftCoupon->getData()) == 0) {
                return ['result'=>false, 'message' => __('Error: Not a valid Coupon code.')];
            }
            if ($giftCoupon->getCurrentAmount() <= 0) {
                return ['result'=>false, 'message' => __('Error: The Coupon code is already used.')];
            }

            if (strtotime($giftCoupon->getExpirationDate()) < strtotime(date('d M y'))) {
                return ['result'=>false, 'message' => __('Error: Coupon code is expired')];
            }

            if (strtotime($giftCoupon->getDeliveryDate()) > strtotime(date('d M y'))) {
                return ['result'=>false, 'message' => __('Error: Not a valid Coupon code')];
            }
            return ['result'=>true, 'message' => __('Success: Succesfully applied the coupon code.')];
        }catch(\Exception $e){
            return ['result'=>false, 'message' => __('Error: Not a valid Coupon code')];
        }
    }
}