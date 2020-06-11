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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsTransaction\Block\Vpayments\Stats;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Request
 * @package Ced\CsTransaction\Block\Vpayments\Stats
 */
class Request extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsTransaction\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $items;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * Request constructor.
     * @param \Ced\CsTransaction\Helper\Payment $paymentHelper
     * @param \Ced\CsTransaction\Model\Items $items
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsTransaction\Helper\Payment $paymentHelper,
        \Ced\CsTransaction\Model\Items $items,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->paymentHelper = $paymentHelper;
        $this->items = $items;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @return |null
     */
    public function getPendingAmountOfVendor()
    {
        if ($this->getVendor() && $this->getVendor()->getId()) {
            $collection = $this->paymentHelper->_getTransactionsStats($this->getVendor());
            return $collection->getFirstItem()->getNetAmount();
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getRequestedAmount()
    {

        $collection = $this->items->getCollection()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->addFieldToFilter('is_requested', '1')
            ->addFieldToFilter('item_payment_state', ['nin'=> [2,3]]);

        $collection->getSelect()->reset('columns')->columns(['requested_amount'=>new \Zend_Db_Expr('SUM(item_fee)')]);
        return $collection->getFirstItem()->getRequestedAmount();

    }

    /**
     * @return mixed
     */
    public function getPendingAmount()
    {

        $collection = $this->items->getCollection()->addFieldToFilter('vendor_id', $this->getVendorId())
        ->addFieldToFilter('item_payment_state', ['nin'=> [2,3]])->addFieldToFilter('is_requested', ['in'=>[0,1]]);
        $collection->getSelect()->reset('columns')->columns(['pending_amount' => new \Zend_Db_Expr('SUM(item_fee)')]);
        return $collection->getFirstItem()->getPendingAmount();
    }

    /**
     * @return mixed
     */
    public function getCancelledAmount()
    {

        $collection = $this->items->getCollection()->addFieldToFilter('vendor_id', $this->getVendorId());

        $collection->getSelect()->reset('columns')
            ->columns(['cancelled_amount' => new \Zend_Db_Expr('SUM(amount_refunded)')]);
        return $collection->getFirstItem()->getCancelledAmount();
    }
}
