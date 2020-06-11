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

namespace Ced\CsProduct\Block\Product;

/**
 * Class Grid
 * @package Ced\CsProduct\Block\Product
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $_vproduct;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Grid constructor.
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\Vproducts $vproduct
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->_type = $type;
        $this->moduleManager = $moduleManager;
        $this->_vproduct = $vproduct;
        $this->registry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorproductGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $vendorId = $this->registry->registry('vendor')['entity_id'];

        $collection = $this->_vproduct->getCollection();
        $inventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_item');

        $prefix = str_replace("cataloginventory_stock_item", "", $inventoryTable);

        $collection->getSelect()->joinLeft($prefix . 'cataloginventory_stock_item', '`main_table`.`product_id` = ' . $prefix . 'cataloginventory_stock_item.product_id',
            array('quantity' => $prefix . 'cataloginventory_stock_item.qty'));
        $collection->addFieldToFilter('vendor_id', $vendorId);
        $collection->addFieldToFilter('check_status', array('nin' => 3));
        $collection->addFilterToMap('product_id', 'main_table.product_id');
        $collection->addFilterToMap('quantity', 'main_table.qty');
        $collection->addFieldToFilter('is_multiseller', 0);
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product Id'),
                'index' => 'product_id',
                'type' => 'number',
                'filter_index' => 'main_table.product_id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name']
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'type' => 'options',
                'options' => $this->_type->getOptionArray(),
            ]
        );

        $this->addColumn('price',
            array(
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
            ));


        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn('quantity',
                array(
                    'header' => __('Qty'),
                    'width' => '50px',
                    'type' => 'number',
                    'index' => 'quantity',
                ));
        }

        $this->addColumn(
            'check_status',
            [
                'header' => __('Status'),
                'index' => 'check_status',
                'type' => 'options',
                'options' => $this->_vproduct->getVendorOptionArray(),
                'renderer' => 'Ced\CsProduct\Block\Product\Grid\Renderer\ProductStatus',
                'filter_condition_callback' => array($this, '_productStatusFilter')
            ]
        );


        $this->addColumn(
            'edits',
            [
                'header' => __('Edit'),
                'caption' => __('Edit'),
                'renderer' => 'Ced\CsProduct\Block\Product\Grid\Renderer\Edit',
                'sortable' => false,
                'filter' => false
            ]
        );


        return parent::_prepareColumns();
    }


    /**
     * @param $collection
     * @param $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _productStatusFilter($collection, $column)
    {
        if (!strlen($column->getFilter()->getValue())) {
            return $this;
        }
        if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $this->getCollection()
                ->addFieldToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS));
            //->addFieldToFilter('status', array('eq' =>\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
        } else if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $this->getCollection()
                ->addFieldToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS));
            //->addAttributeToFilter('status', array('eq' =>\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));
        } else
            $this->getCollection()->addFieldToFilter('check_status', array('eq' => $column->getFilter()->getValue()));
        return $this;
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {

        $product = $this->productFactory->create()->load($row->getProductId());


        $attributeSetId = $product->getAttributeSetId();


        return $this->getUrl('*/*/edit', ['set' => $attributeSetId, 'id' => $row->getProductId(), 'store' => (int)$this->getRequest()->getParam('store', 0), 'type' => $product->getTypeId()]);
    }

    /**
     * @return string
     */
    protected function _getUrlModelClass()
    {
        return 'core/url';
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product_id');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('csproduct/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        return $this;
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
