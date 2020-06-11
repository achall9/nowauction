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
namespace Ced\GiftCard\Helper;

use Zend\Validator\Date;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Math\Random;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class Coupon
 * @package Ced\GiftCard\Helper
 */
class Coupon extends \Magento\Framework\App\Helper\AbstractHelper
{
  
    /**
     * @var \Ced\GiftCard\Model\GiftCouponFactory $GiftCouponFactory
     */
    protected $_giftCouponFactory;

    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftTemplate $GiftTemplate
     */
    private $giftTemplate;
    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     */
    private $messageManager; 
    /**
     * @var \Magento\Framework\Math\Random $random
     */
    private $_random;
    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;
    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\GiftCard\Model\GiftCouponFactory $GiftCouponFactory,
        \Ced\GiftCard\Model\GiftTemplate $GiftTemplate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date, 
        Random $random,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\Product $_product
    )
    {
        $this->messageManager = $messageManager;
        $this->_random = $random;
        $this->_giftCouponFactory = $GiftCouponFactory;
        $this->giftTemplate = $GiftTemplate;
        $this->date = $date;
        $this->_product = $_product;
 
        parent::__construct($context); 
    }

    /**
     * @param $info_buyRequest
     * @return array
     */
    public function createGiftCoupon($info_buyRequest){

      $this->_product = $this->_product->load($info_buyRequest['product']);
       
      if($info_buyRequest['deliverydate']){
          $expiration_date = $this->date->gmtDate(null, $info_buyRequest['deliverydate'].'+'.$this->_product->getGiftExpiryDays().' days');
          $created_at = $this->date->gmtDate(null,$info_buyRequest['deliverydate']);
      }
      else{
          $expiration_date = $this->date->gmtDate(null, '+'.$this->_product->getGiftExpiryDays().' days');
          $created_at = $this->date->gmtDate(null);
      }

      $giftcouponData = [
        'coupon_price' => $info_buyRequest['gift_price'],
        'current_amount' => $info_buyRequest['gift_price'],
        'increment_id' => $info_buyRequest['incrementId'],
        'time_used' => 0,
        'template_id'=> $info_buyRequest['template_id'],
        'product_id' => $this->_product->getId(),
        'delivery_date' => ($info_buyRequest['deliverydate'])?$info_buyRequest['deliverydate']:'',
        'expiration_date' => $expiration_date,
        'created_at' => $created_at,
      ];
      
      if ((int)$info_buyRequest['qty_ordered'] == 1){
          $coupon_code = $this->createCoupon($info_buyRequest['template_id']);
          $giftcouponData['code'] = $coupon_code;
          $cedGiftCoupon = $this->_giftCouponFactory->create();
          $cedGiftCoupon->setData($giftcouponData);
          $cedGiftCoupon->save();
          $cedGiftCoupon = [$cedGiftCoupon];
      }else{
          $cedGiftCouponObject = [];
          for ($c=0; $c<(int)$info_buyRequest['qty_ordered']; $c++){

              $coupon_code = $this->createCoupon($info_buyRequest['template_id']);
              $giftcouponData['code'] = $coupon_code;
              $cedGiftCoupon = $this->_giftCouponFactory->create();
              $cedGiftCoupon->setData($giftcouponData);
              $cedGiftCoupon->save();
              $cedGiftCouponObject[] = $cedGiftCoupon;
          }
          $cedGiftCoupon = $cedGiftCouponObject;
      }
      return $cedGiftCoupon;

    }

    /**
     * @param $template_id
     * @return array|mixed|string
     */
    public function createCoupon($template_id){
  
        $tempelate = $this->giftTemplate->load($template_id, 'id');
     
        $code_template = $tempelate->getCodeTemplate();
 
        $code_template = str_replace(' ', '_', $code_template);
 
        $code = $this->generateRandomCouponCode($code_template);
        $giftcouponFactory = $this->_giftCouponFactory;

        /*to check if coupon code already exist and regenrate new one*/
        $runCount = 0;
        while (true) {
          $giftcoupon = $giftcouponFactory->create();
          $isExist = $giftcoupon->load($code, 'code'); 

          if (!empty($isExist->getData())) {
            $code = $this->generateRandomCouponCode($code);
          }else{
            /*break the while loop if coupon code dosn't exist in db*/
            break;
          }
          /*to handle the infinite while loop*/
          if ($runCount++ > 10) {
            $code = $code.'_'.time().rand();
            break;
          }
        }
        return $code;
    }

    /**
     * @param $code
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateRandomCouponCode($code){
      $code = explode("{", $code);

      foreach ($code as & $value) {
        if (strpos( $value , 'S}' ) !== false) {
          $value = $this->_random->getRandomString(1, Random::CHARS_UPPERS);
        } 
        if (strpos( $value , 'N}' ) !== false) {
          $value = $this->_random->getRandomNumber(0, 9);
        }
        if (strpos( $value , 's}' ) !== false) {
          $value = $this->_random->getRandomString(1, Random::CHARS_UPPERS);
        }
      }  
      $code = implode('', $code); 
      $code = str_replace('{', '', $code);
      $code = str_replace('}', '', $code);
      return $code;
    }
  }
