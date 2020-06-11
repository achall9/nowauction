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
namespace Ced\GiftCard\Observer\Creditmemo;

use Magento\Framework\Event\ObserverInterface;
use \Ced\GiftCard\Model\GiftCouponFactory;

/**
 * Class CartAddBefore
 * @package Ced\GiftCard\Observer
 */
class SaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var GiftCouponFactory
     */
    protected $_giftCoupons;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        GiftCouponFactory $_giftCoupons,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
        $this->_giftCoupons = $_giftCoupons;
        $this->request = $request;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $updatedGiftCard = false;
            $requestData = $this->request->getPost('creditmemo');
            if(isset($requestData['giftcard_amount']) && is_array($requestData['giftcard_amount'])){
                foreach($requestData['giftcard_amount'] as $code => $amount){
                    $giftCoupon = $this->_giftCoupons->create();
                    $giftCoupon->load($code, 'code');
                    $couponPrice = $giftCoupon->getCouponPrice() - $amount;
                    $currentAmount = $giftCoupon->getCurrentAmount() - $amount;

                    $giftCoupon->setCouponPrice($couponPrice)
                        ->setCurrentAmount($currentAmount)
                        ->save();
                    
                    $updatedGiftCard = true;
                }
            }
            if($updatedGiftCard){
                $this->messageManager->addSuccessMessage(__('Reduced amount from the gift coupon.'));
            }
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__('Unable to reduce amount from the gift coupon.'));
        }
        return $this;
    }

}