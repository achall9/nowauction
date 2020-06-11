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

namespace Ced\CsOrder\Block\Order\Invoice\Renderer;

/**
 * Class Grandtotal
 * @package Ced\CsOrder\Block\Order\Invoice\Renderer
 */
class Grandtotal extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoice;

    /**
     * @var \Ced\CsOrder\Model\InvoiceGrid
     */
    protected $invoiceGridFactory;

    /**
     * Grandtotal constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Ced\CsOrder\Model\InvoiceGridFactory $invoiceGrid
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Ced\CsOrder\Model\InvoiceGridFactory $invoiceGridFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->invoice = $invoice;
        $this->invoiceGridFactory = $invoiceGridFactory;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return float|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $vendorId = $this->customerSession->getVendorId();
        $invoice = $this->invoice->load($row->getEntityId());
        $invoice = $this->invoiceGridFactory->create()->setVendorId($vendorId)->updateTotal($invoice);
        return $this->pricingHelper->currency($invoice->getGrandTotal(),true,false);
    }
}
