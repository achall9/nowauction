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

namespace Ced\CsMultiSeller\Block\Product;

/**
 * Class Grid
 * @package Ced\CsMultiSeller\Block\Product
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var string
     */
    protected $_massactionBlockName = 'Ced\CsMultiSeller\Block\Product\Grid\Massaction';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Ced\CsMultiSeller\Model\System\Config\Source\Type
     */
    protected $type;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Grid constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsMultiSeller\Model\System\Config\Source\Type $type
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ced\CsMultiSeller\Model\System\Config\Source\Type $type,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        array $data = []
    )
    {
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $context->getStoreManager();
        $this->registry = $registry;
        $this->vproductsCollectionFactory = $vproductsCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->type = $type;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', "adminhtml");
    }

    /**
     * Set grid parameters.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('product_id');

        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    /**
     * @return int $storeId
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepares Mass Action
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('product_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * Prepares Grid Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $vendorId = $this->registry->registry('vendor')['entity_id'];

        $collection = $this->vproductsCollectionFactory->create();
       
        $collection->addFieldToFilter('vendor_id', $vendorId);
        $collection->addFieldToFilter('check_status', array('nin' => 3));
        $collection->addFieldToFilter('is_multiseller', 1);

        $this->getColumn('massaction')->setUseIndex(true);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepares Grid Columns
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $this->addColumn('product_id',
            [
                'header' => __('ID'),
                'width' => '5px',
                'type' => 'number',
                'align' => 'left',
                'index' => 'product_id',
                'renderer' => '\Ced\CsMultiSeller\Block\Product\Renderer\Productid'
            ]);
        $this->addColumn('name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text'
            ]);
        $this->addColumn('sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'filter_condition_callback' => array($this, '_productNameFilter')
            ]);
        if (count($this->type->toFilterOptionArray(true, false, $store->getId())) > 1) {
            $this->addColumn('type_id',
                [
                    'header' => __('Type'),
                    'width' => '10px',
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => $this->type->toFilterOptionArray(true, false, $store->getId()),
                ]);
        }
        $this->addColumn('price',
            [
                'header' => __('Price'),
                'type' => 'number',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            ]);

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn('qty',
                [
                    'header' => __('Qty'),
                    'width' => '50px',
                    'type' => 'number',
                    'index' => 'qty',
                ]);
        }
        $this->addColumn('check_status',
            [
                'header' => __('Status'),
                'width' => '70px',
                'index' => 'check_status',
                'type' => 'options',
                'renderer' => 'Ced\CsMultiSeller\Block\Product\Renderer\Productstatus',
                'options' => $this->vproductsFactory->create()->getVendorOptionArray(),
                'filter_condition_callback' => array($this, '_productStatusFilter')
            ]);

        return parent::_prepareColumns();
    }

    /**
     * Prepares Filter Buttons
     */
    protected function _prepareFilterButtons()
    {
        $this->addChild(
            'submit_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Submit'),
                'class' => 'save submit-button primary',
                'onclick' => 'submitShipment(this);',
                'area' => 'adminhtml'
            ]
        );
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
     * @return $this
     */
    protected function _productNameFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection = $this->vproductsCollectionFactory->create();
        $collection->addFieldToFilter('sku', array('like' => '%' . $column->getFilter()->getValue() . '%'));

        return $this;
    }

    /**
     * @return $this
     */
    protected function _productStatusFilter($collection, $column)
    {
        $collection = $this->vproductsCollectionFactory->create();
        if (!strlen($column->getFilter()->getValue())) {
            return $this;
        }
        if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $collection
                ->addFieldToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS))
                ->addFieldToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
        } else if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $collection
                ->addFieldToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS))
                ->addFieldToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));
        } else
            $collection->addFieldToFilter('check_status', array('eq' => $column->getFilter()->getValue()));
        return $this;
    }

    /**
     * Return Grid Url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridproduct', array(
            'store' => $this->getRequest()->getParam('store')));
    }

    /**
     * Return Row Url
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
                'store' => $this->getRequest()->getParam('store'),
                'id' => $row->getProductId())
        );
    }

}
