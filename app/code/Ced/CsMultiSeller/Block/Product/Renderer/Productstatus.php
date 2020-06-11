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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiSeller\Block\Product\Renderer;

/**
 * Class Productstatus
 * @package Ced\CsMultiSeller\Block\Product\Renderer
 */
class Productstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Productstatus constructor.
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Status
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $vOptionArray = $this->vproductsFactory->create()->getVendorOptionArray();
        if ($row->getCheckStatus() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS)
            return $vOptionArray[$row->getCheckStatus() . $row->getStatus(0)];
        else
            return $vOptionArray[$row->getCheckStatus()];
    }

}

?>