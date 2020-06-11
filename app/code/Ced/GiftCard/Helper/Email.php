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


/**
 * Class Email
 * @package Ced\GiftCard\Helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
  
    /**
     * @var \Ced\GiftCard\Model\GiftCoupon $GiftCoupon
     */
    protected $_giftCoupon;
   
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    private $_transportBuilder;

    /**
     * @var Image
     */
    private $helperImage;
    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        Image $helperImage, 
        \Ced\GiftCard\Model\GiftCoupon $GiftCoupon
    )
    {
        $this->helperImage = $helperImage;
        $this->_giftCoupon = $GiftCoupon;
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
 
        parent::__construct($context); 
    }
    /*
    * @note: send email for Gift CardCoupon Code 
    *
    */
    /**
     * @param $productOptions
     * @param $coupon_code
     */
    public function email($productOptions, $coupon_code)
    {

      if(!isset($productOptions['gift_to_email'])){
          $productOptions = array_values($productOptions)[0];
      }
      $emailTemplate = 'cedgiftcard_default_email_template';
 
      if (isset($productOptions)) {
          try {
              $emailTemplateVariables = [];
              $emailTemplateVariables = $productOptions;
              $emailTemplateVariables['coupon_code'] = $coupon_code;
               
              $adminMail = $this->scopeConfig->getValue(
                  'trans_email/ident_sales/email',
                  \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
              );
              $adminName = $this->scopeConfig->getValue(
                  'trans_email/ident_sales/name',
                  \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
              );
              $adminDetail = [
                  'name' => $adminName,
                  'email' => $adminMail,
              ];

              if(isset($productOptions['image'])){
                 
                  if (substr( $productOptions['image'], 0, 9 ) === "giftcard/") {
                    $productOptions['image'] = $this->helperImage->retreiveImage($productOptions['image'], ''); 
                  }else{
                    $productOptions['image'] = $this->helperImage->retreiveImage($productOptions['image']); 
                  }
                  
                  if(getimagesize($productOptions['image'])){
                      $emailTemplateVariables['image_url'] = $productOptions['image'];
                      $emailTemplateVariables['image'] =  $productOptions['image'];
                  }else{
                      $emailTemplateVariables['image_url'] = '';
                  }
              }else{
                  $emailTemplateVariables['image_url'] = '';
              }

              $emailTemplateVariables['currency_code'] = $this->storeManager->getStore()->getCurrentCurrencyCode();
              $emailTemplateVariables['gift_from_name'] = ($emailTemplateVariables['gift_from_name'])?$emailTemplateVariables['gift_from_name']:_('SomeOne');
              $emailTemplateVariables['image_label'] = ($emailTemplateVariables['image_label'])?$emailTemplateVariables['image_label']:_('Gift Card');
              
              $this->_transportBuilder
                  ->setTemplateIdentifier($emailTemplate)
                  ->setTemplateOptions(
                      [
                          'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                          'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                      ])
                  ->setTemplateVars($emailTemplateVariables)
                  ->setFrom($adminDetail)
                  ->addTo($productOptions['gift_to_email'], $productOptions['gift_to_name'])
                  ->getTransport()
                  ->sendMessage();

          } catch (\Exception $e) { 
          }
        }
  } 
}
