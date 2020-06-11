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
 * Class CouponAddBefore
 * @package Ced\GiftCard\Observer
 */
Class CouponAddBefore implements ObserverInterface
{    
  
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * CouponAddBefore constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository 
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;  
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer 
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {  
        try{

          $couponCode = $observer->getRequest()->getParam('remove') == 1
            ? ''
            : trim($observer->getRequest()->getParam('coupon_code'));
            if($couponCode !== ''){
             
              /** @var  \Magento\Quote\Model\Quote $quote */
              $quote = $this->checkoutSession->getQuote();
              $hasGiftCard = false;
              foreach($quote->getAllItems() as $item){
                if ($item->getProductType() == 'giftcard' ) {
                  $hasGiftCard = true;
                }
              }

              if($hasGiftCard){  
                $this->_messageManager->addErrorMessage(__('Could not apply coupon code on the giftcard product.'));
                $observer->getRequest()->setParam('coupon_code', '');
                $observer->getRequest()->setPostValue('coupon_code', ''); 
              }
            }
             
        }catch(\Exception $e){ 
          $this->_messageManager->addErrorMessage(__( $e->getMessage()));
        }
    }
 
}
