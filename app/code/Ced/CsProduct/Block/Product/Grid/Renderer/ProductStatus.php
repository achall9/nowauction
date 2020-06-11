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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Block\Product\Grid\Renderer;

/**
 * Class ProductStatus
 * @package Ced\CsProduct\Block\Product\Grid\Renderer
 */
class ProductStatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $_vproduct;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * ProductStatus constructor.
     * @param \Ced\CsMarketplace\Model\Vproducts $vproduct
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->_vproduct = $vproduct;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->productFactory->create()->load($row->getProductId());
        $vOptionArray = $this->_vproduct->getVendorOptionArray();
        if ($row->getCheckStatus() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS)
            return $vOptionArray[$row->getCheckStatus() . $product->getStatus()];
        else
            return $vOptionArray[$row->getCheckStatus()];
    }
}
