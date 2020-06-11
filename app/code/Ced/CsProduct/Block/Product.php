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

namespace Ced\CsProduct\Block;

/**
 * Class Product
 * @package Ced\CsProduct\Block
 */
class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'product.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'product';
        $this->_blockGroup = 'Ced_CsProduct';
        $this->_headerText = __('Products');
        parent::_construct();
    }


    /**
     * Product constructor.
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $set
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $set,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
        $this->storeManager = $storeManager;
        $this->type = $type;
        $this->set = $set;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Ced\CsProduct\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsProduct\Block\Product\Grid', 'ced.csproduct.vendor.product.grid')
        );

        return parent::_prepareLayout();

    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );
        $allowedType = $this->type->getAllowedType($this->storeManager->getStore()->getId());
        foreach ($types as $typeId => $type) {
            if (!in_array($typeId, $allowedType))
                continue;
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
                'href' => $this->_getProductCreateUrl($typeId)
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        $attributeSetId = $this->_productFactory->create()->getDefaultAttributeSetId();

        $allowedSet = $this->set->getAllowedSet($this->storeManager->getStore()->getId());
        if (is_array($allowedSet)) {
            $attributeSetId = current($allowedSet);
        }
        return $this->getUrl(
            'csproduct/*/new',
            ['set' => $attributeSetId, 'type' => $type]
        );


    }

    /**
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            'area' => 'adminhtml'
        ];

        return $splitButtonOptions;
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            '*/*/new'
        );
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}