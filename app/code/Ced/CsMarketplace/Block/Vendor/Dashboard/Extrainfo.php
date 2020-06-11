<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vendor\Dashboard;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Extrainfo
 * @package Ced\CsMarketplace\Block\Vendor\Dashboard
 */
class Extrainfo extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var null
     */
    protected $_associatedOrders = null;

    /**
     * @var null
     */
    protected $_associatedPayments = null;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    public $invoiceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * Extrainfo constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Locale\Currency $currency
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Locale\Currency $currency
    )
    {
        $this->currency = $currency;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->storeManager = $context->getStoreManager();
        $this->priceCurrency = $priceCurrency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        if ($this->getVendorId()) {
            $ordersCollection = $this->getVendor()->getAssociatedOrders()->setOrder('id', 'DESC');
            $main_table = 'main_table';
            $order_total = 'order_total';
            $shop_commission_fee = 'shop_commission_fee';
            $ordersCollection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})")))->order('created_at DESC')->limit(5);

            $this->setVorders($ordersCollection);
        }
    }

    /**
     * Return order view link
     *
     * @param string $order
     * @return String
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('*/vorders/view', ['order_id' => $order->getId()]);
    }

    /**
     * @return \Magento\Framework\Locale\Currency
     */
    public function getVcurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getOrderStatusArray(){
        $statuses = $this->statusCollectionFactory->create()->toOptionArray();
        $status_arr = [];
        foreach( $statuses as $status ){
            $status_arr[$status['value']] = $status['label'];
        }
        return $status_arr;
    }
}
