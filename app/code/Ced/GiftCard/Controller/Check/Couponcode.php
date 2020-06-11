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
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Controller\Check;

/**
 * Class Couponcode
 * @package Ced\GiftCard\Controller\Check
 */
class Couponcode extends \Magento\Framework\App\Action\Action
{


    /**
     * GiftCoupon Collection.
     *
     * @var \Ced\GiftCard\Model\ResourceModel\GiftCoupon\Collection $GiftCoupon
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
     * @var \Magento\Customer\Model\Session
     */
    protected $_custmerSesion;
    /**
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\GiftCard\Model\ResourceModel\GiftCoupon\Collection $GiftCoupon,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Model\Session $session
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_giftCoupon = $GiftCoupon;
        $this->_custmerSesion = $session;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $data = [];
        if($this->_custmerSesion->isLoggedIn())
        {
            $postData = $this->getRequest()->getPostValue();

            $couponcode = (isset($postData['couponcode']))?$postData['couponcode']:' ';

            if($couponcode != ' '){
                $_giftCoupon = $this->_giftCoupon->addFieldToFilter('code', $couponcode);

                foreach ($_giftCoupon as $coupons){
                    $return = [];
                    $return['code'] = $coupons->getCode();
                    $return['coupon_price'] = $this->getFormatedPrice($coupons->getCouponPrice());
                    $return['created_at'] = $coupons->getCreatedAt();
                    $return['current_amount'] = $this->getFormatedPrice($coupons->getCurrentAmount());
                    $return['delivery_date'] = date('d-m-y', strtotime($coupons->getDeliveryDate()));;
                    $return['expiration_date'] = date('d-m-y', strtotime($coupons->getExpirationDate()));
                    $data[] = $return;
                }
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * @param $amount
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }
}
