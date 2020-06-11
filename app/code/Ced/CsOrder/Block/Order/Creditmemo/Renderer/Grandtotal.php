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
namespace Ced\CsOrder\Block\Order\Creditmemo\Renderer;

use Magento\Backend\Block\Context;

/**
 * Class Grandtotal
 * @package Ced\CsOrder\Block\Order\Creditmemo\Renderer
 */
class Grandtotal extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditMemo;

    /**
     * @var \Ced\CsOrder\Model\CreditmemoGrid
     */
    protected $creditMemoGrid;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * Grandtotal constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @param \Ced\CsOrder\Model\CreditmemoGrid $creditMemoGrid
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Creditmemo $creditMemo,
        \Ced\CsOrder\Model\CreditmemoGrid $creditMemoGrid,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->creditMemo = $creditMemo;
        $this->creditMemoGrid = $creditMemoGrid;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return float|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $vendorId = $this->customerSession->getVendorId();
        $Creditmemo = $this->creditMemo->load($row->getEntityId());
        $Creditmemo = $this->creditMemoGrid->setVendorId($vendorId)->updateTotal($Creditmemo);
        return $this->pricingHelper->currency($Creditmemo->getGrandTotal(),true,false);

    }
}
