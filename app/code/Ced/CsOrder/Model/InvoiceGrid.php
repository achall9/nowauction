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
 * @package   Ced_CsOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class InvoiceGrid
 * @package Ced\CsOrder\Model
 */
class InvoiceGrid extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * Payment states
     */
    const STATE_OPEN = 1;

    const STATE_PAID = 2;

    const STATE_CANCELED = 3;

    const STATE_REFUND = 4;

    const STATE_REFUNDED = 5;

    const ORDER_NEW_STATUS = 1;

    const ORDER_CANCEL_STATUS = 3;

    const STATE_PARTIALLY_PAID = 6;

    /**
     * @var null
     */
    protected $_items = null;

    protected static $_states;

    /**
     * @var string
     */
    protected $_eventPrefix = 'csorder_invoice';

    /**
     * @var string
     */
    protected $_eventObject = 'vinvoice';

    /**
     * @var null
     */
    public $_vendorstatus = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItems;

    /**
     * InvoiceGrid constructor.
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItems
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory $orderItems,
        \Magento\Customer\Model\Session $session,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->orderItems = $orderItems;
        $this->session = $session;
        $this->vordersFactory = $vordersFactory;
        $this->csorderHelper = $csorderHelper;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Ced\CsOrder\Model\ResourceModel\Invoice');
    }

    /**
     * @param $invoice
     * @return bool
     */
    public function canInvoiceIncludeShipment($invoice)
    {
        if (is_object($invoice)) {

            $vendorId = $this->session->getVendorId();
            $invoicedCollection = $this->getCollection()
                ->addFieldTofilter('invoice_order_id', $invoice->getOrderId())
                ->addFieldTofilter('vendor_id', $vendorId)
                ->addFieldTofilter('shipping_code', array('notnull' => true));
            if (count($invoicedCollection) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $invoice
     * @param bool $view
     * @return mixed
     */
    public function updateTotal($invoice, $view = true)
    {

        $vorder = $this->vordersFactory->create()->setVendorId($this->getVendorId())->getVorderByInvoice($invoice);
        if (!$this->_registry->registry('current_vorder')) {
            $this->_registry->register('current_vorder', $vorder);
        }

        $helperData = $this->csorderHelper;
        if (!is_object($vorder)) {
            return $invoice;
        }

        if (!$vorder->isAdvanceOrder() && $vorder->getCode()) {
            $invoice->setOrder($vorder->getOrder(false, true));
            if ($view && $vInvoice = $this->updateInvoiceGridTotal($invoice)) {

                $invoice->setShippingAmount($vInvoice->getShippingAmount());
                $invoice->setBaseShippingAmount($vInvoice->getBaseShippingAmount());
            } else if ($this->canInvoiceIncludeShipment($invoice)) {

                $invoice->setShippingAmount($vorder->getShippingAmount());
                $invoice->setBaseShippingAmount($vorder->getBaseShippingAmount());
            }

            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $discount = $this->getItemDiscountByInvoice($invoice);
            $invoice->setTaxAmount($tax);
            $invoice->setGrandTotal($subtotal - $discount + $tax + $invoice->getBaseShippingAmount());
        }

        if (!$helperData->canShowShipmentBlock($vorder)) {
            $invoice->setShippingAmount(0);
            $invoice->setBaseShippingAmount(0);
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $discount = $this->getItemDiscountByInvoice($invoice);
            $invoice->setTaxAmount($tax);


            $invoice->setGrandTotal($subtotal - $discount + $tax + $invoice->getBaseShippingAmount());
        }

        return $invoice;
    }

    /**
     * @param $invoice
     * @return bool|\Magento\Framework\DataObject
     */
    public function updateInvoiceGridTotal($invoice)
    {

        if (is_object($invoice)) {
            $vendorId = $this->getVendorId();
            $invoicedCollection = $this->getCollection()
                ->addFieldTofilter('invoice_id', $invoice->getId())
                ->addFieldTofilter('vendor_id', $vendorId)
                ->addFieldTofilter('shipping_code', array('notnull' => true));
            if (count($invoicedCollection) > 0) {
                return $invoicedCollection->getFirstItem();
            }
        }

        return false;
    }


    /**
     * @param $invoice
     * @return mixed
     */
    public function updateTotalGrid($invoice)
    {

        $vorder = $this->vordersFactory->create()->getVorderByInvoice($invoice);
        $helperData = $this->csorderHelper;
        if (!is_object($vorder)) {
            return $invoice;
        }


        if (!$vorder->isAdvanceOrder() && $vorder->getCode()) {
            $invoice->setOrder($vorder->getOrder(false, true));
            if ($vInvoice = $this->updateInvoiceGridTotal($invoice)) {
                $invoice->setShippingAmount($vInvoice->getShippingAmount());
                $invoice->setBaseShippingAmount($vInvoice->getBaseShippingAmount());
            }
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $invoice->setTaxAmount($tax);
            $invoice->setGrandTotal($subtotal + $tax + $invoice->getShippingAmount());
        }

        if (!$helperData->canShowShipmentBlock($vorder)) {
            $invoice->setShippingAmount(0);
            $invoice->setBaseShippingAmount(0);
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $invoice->setTaxAmount($tax);


            $invoice->setGrandTotal($subtotal + $tax + $invoice->getShippingAmount());
        }

        return $invoice;
    }


    /**
     * @param $invoice
     * @return int
     */
    public function getItemSubtotalByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create()->load($_item->getOrderItemId());
            if ((is_object($_item->getOrderItem()) && $_item->getOrderItem()->getParentItem()) || $vendorProduct->getVendorId() != $vendorId )
            {
                continue;
            }

            $total += $_item->getRowTotal();
        }

        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemTaxByInvoice($invoice)
    {

        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create()->load($_item->getOrderItemId());
            if ((is_object($_item->getOrderItem()) && $_item->getOrderItem()->getParentItem()) || $vendorProduct->getVendorId() != $vendorId )
            {
                continue;
            }
            $total += $_item->getBaseTaxAmount();
        }

        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemDiscountByInvoice($invoice)
    {

        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create()->load($_item->getOrderItemId());
            if ((is_object($_item->getOrderItem()) && $_item->getOrderItem()->getParentItem()) || $vendorProduct->getVendorId() != $vendorId )
            {
                continue;
            }
            $total += $_item->getBaseDiscountAmount();
        }

        return $total;
    }

    /**
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
                self::STATE_PARTIALLY_PAID => __('Partially Paid'),
            );
        }
        return self::$_states;
    }

}
