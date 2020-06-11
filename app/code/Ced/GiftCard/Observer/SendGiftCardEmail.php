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
use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class SendGiftCardEmail
 * @package Ced\GiftCard\Observer
 */
Class SendGiftCardEmail implements ObserverInterface
{    
    /**
     * @var \Ced\GiftCard\Helper\Email 
     */ 
    protected $helperMail;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    
    /**
     * @var \Ced\GiftCard\Helper\Coupon
     */ 
    protected $helperCoupon;

    /**
     * SendGiftCardEmail constructor.
     * @param \Ced\GiftCard\Helper\Coupon $helperCoupon
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Ced\GiftCard\Helper\Email $helperMail
     */
    public function __construct(
        \Ced\GiftCard\Helper\Coupon $helperCoupon,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\GiftCard\Helper\Email $helperMail
    )
    {
        $this->helperMail = $helperMail;
        $this->helperCoupon = $helperCoupon;
        $this->_request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getDataObject();

        $order = $invoice->getOrder();
        $incrementId = $order->getIncrementId();
        $productOptions = [];
        try{
          $invoicedItems = $this->_request->getParam('invoice', []);
          if (isset($invoicedItems['items'])){
              $invoicedItems = $invoicedItems['items'];
          }
          foreach ($order->getItems() as $item){

              if ($item->getIsVirtual() == 1 && $item->getProductType() == GiftCard::TYPE_CODE){

                  $info_buyRequest = $item->getProductOptions()['info_buyRequest'];

                  if (isset($info_buyRequest['giftcard'])){

                      if (!empty($info_buyRequest) && isset($invoicedItems[$item->getItemId()])){
                        $coupon_code = '';
                        $info_buyRequest['incrementId'] = $incrementId;
                        $info_buyRequest['gift_price'] = $item->getBasePrice();

                        $info_buyRequest['qty_ordered'] =  $invoicedItems[$item->getItemId()];
                        $cedGiftCouponArray = $this->helperCoupon->createGiftCoupon($info_buyRequest);

                        foreach ($cedGiftCouponArray as $cedGiftCoupon){
                            $coupon_code .= $cedGiftCoupon->getCode().', ';
                            $info_buyRequest['expiration_date'] = $cedGiftCoupon->getExpirationDate();
                            $info_buyRequest['expiration_date'] = date('d-m-y', strtotime($info_buyRequest['expiration_date']));
                        }
                        if ($info_buyRequest['deliverydate'] == null) {
                          $this->helperMail->email($info_buyRequest, $coupon_code);
                        }
                        $additionalOptions = $item->getProductOptionByCode();
                        foreach($additionalOptions['additional_options'] as &$val){
                            if($val['code'] == 'gift_card_detail'){
                                $val['value'] = $val['value'].', '.__('Coupon Codes').' : '.$coupon_code;
                            }
                        }
                        $item->setProductOptions($additionalOptions)->save();
                      }
                  }
              }
          }
        }catch(\Exception $e){
          throw new \Magento\Framework\Exception\LocalizedException(
          		__($e->getMessage())
          );
        }
    }
 
}
