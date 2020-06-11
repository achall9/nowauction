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
namespace Ced\GiftCard\Block\Customer;
 
use Magento\Framework\App\RequestInterface;

/**
 * Class CardCheck
 * @package Ced\GiftCard\Block\Customer
 */
class CardCheck extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftCoupon\Collection
     */
    protected $_giftCoupon;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var Object
     */
    protected $_giftCoupons = [];

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * CardCheck constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     * @param RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\GiftCard\Model\ResourceModel\GiftCoupon\CollectionFactory $GiftCoupon
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\GiftCard\Model\ResourceModel\GiftCoupon\CollectionFactory $GiftCoupon,
        array $data = []
    ) {

        $this->_logo = $logo;
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->_giftCoupon = $GiftCoupon;
        $this->_customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context,$data);

    }

    /**
     * @param $amount
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * @return Object
     */
    public function getCollection(){
        $email = $this->_customerSession->create()->getCustomer()->getEmail();
        if ($email) {   
            if (null != $this->_giftCoupons){
                return $this->_giftCoupons;
            }
            $collection = $this->_giftCoupon->create();
            $collection->addFieldToFilter(
                'delivery_date',
                [
                    ['lteq' => Date('Y-m-d H:i:s')],
                    ['null' => true]
                ]);
            $sales_order = $collection->getResource()->getTable('sales_order');
            $collection->getSelect()->join([$sales_order],
                'main_table.increment_id = '.$sales_order.'.increment_id',
                ['entity_id']
            );
            $order_item = $collection->getResource()->getTable('sales_order_item');

            $collection->getSelect()->join([$order_item],
                $sales_order.'.entity_id = '.$order_item.'.order_id AND '.$order_item.'.ced_gift_to_mail = "'.$email.'"',
                ['to_email' => $order_item.'.ced_gift_to_mail']
            );

            $collection->getSelect()->group('code')->order('created_at DESC');
            $this->_giftCoupons = $collection;
        }

        return $this->_giftCoupons;
    }

    /**
     * @return string
     */
    public function checkCouponCodeURL(){
        return $this->getUrl('giftcard/check/couponcode');
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'giftcard.grid.couponcode.pager'
            )->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
        }

        return $this;
    }
 
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * Get Store Currency Label
     *
     * @return string
     */
    public function getStoreCurrency(){
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
    /**
     * Get Post
     *
     * @return array()
     */
    public function getPostValue()
    {
        return $this->request->getPostValue();
    }
    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {    
        return $this->_logo->getLogoSrc();
    }
    /**
     * Get logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {    
        return $this->_logo->getLogoAlt();
    }
 
    /**
     * Get coupon details 
     *
     * @return object()
     */
    public function getGiftCards(){
        return $this->_giftCoupons;
    }
}
