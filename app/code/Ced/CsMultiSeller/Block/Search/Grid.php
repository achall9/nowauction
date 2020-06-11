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

namespace Ced\CsMultiSeller\Block\Search;

use Ced\CsMarketplace\Model;

/**
 * Class Grid
 * @package Ced\CsMultiSeller\Block\Search
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var int
     */
    protected $_count = 0;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\CsMultiSeller\Model\System\Config\Source\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollectionFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteFactory;

    /**
     * Grid constructor.
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Ced\CsMultiSeller\Model\System\Config\Source\Type $type
     * @param Model\Session $session
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\CsMultiSeller\Model\System\Config\Source\Type $type,
        \Ced\CsMarketplace\Model\Session $session,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->_storeManager = $context->getStoreManager();
        $this->type = $type;
        $this->session = $session;
        $this->resourceConnection = $resourceConnection;
        $this->storeFactory = $storeFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->vproductsCollectionFactory = $vproductsCollectionFactory;
        $this->websiteFactory = $websiteFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', "adminhtml");
    }

    /**
     * Set Grid Parameters
     */

    public function _construct()
    {
        parent::_construct();
        $this->setId('searchGrid');
        $this->setDefaultSort('entity_id');

        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('search_filter');

    }

    /**
     *Get Store Id
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     *Column Filter Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepares Collection For Grid
     */
    protected function _prepareCollection()
    {
        $product_collection = array();
        $store = $this->_getStore();
        $types = $this->type->toFilterOptionArray(true, false, $store->getId());
        $types = array_keys($types);
        $this->_count = count($types);
        $vendorId = $this->session->getVendorId();
        $tablename = $this->resourceConnection->getTableName('ced_csmarketplace_vendor_products');
        $storeId = 0;
        if ($this->getRequest()->getParam('store')) {
            $websiteId = $this->storeFactory->create()->load($this->getRequest()->getParam('store'))->getWebsiteId();
            if ($websiteId) {
                if (in_array($websiteId, $this->vproductsFactory->create()->getAllowedWebsiteIds())) {
                    $storeId = $this->getRequest()->getParam('store');
                }
            }
        }
        $product_collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array('in' => $types))
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', array('neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE));
        $product_collection->getSelect()
            ->joinleft(array('vproducts' => $tablename), 'vproducts.product_id=e.entity_id', array('vproducts.check_status', 'vproducts.is_multiseller', 'vproducts.vendor_id'));
        $product_collection->getSelect()
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`check_status` WHEN 1 THEN 1 WHEN 0 THEN 0 ELSE 1 END') . "='1'")
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`is_multiseller` WHEN 1 THEN 1 WHEN 0 THEN 0 ELSE 0 END') . " ='0'")
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`vendor_id` WHEN ' . $vendorId . ' THEN ' . $vendorId . ' WHEN 0 THEN 0 ELSE 0 END') . " <> '" . $vendorId . "'");
        if ($storeId)
            $product_collection->addStoreFilter($storeId);

        /**
         * remove already created products
         */
        $created_product = $this->vproductsCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->getColumnValues('parent_id');
        if (!empty($created_product))
            $product_collection->addAttributeToFilter('entity_id', array('nin' => $created_product));
        $product_collection->addWebsiteNamesToResult();
        $this->setCollection($product_collection);

        parent::_prepareCollection();

        return $this;
    }


    /**
     * Prepares Column For Grid
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '5px',
                'type' => 'number',
                'align' => 'left',
                'index' => 'entity_id',
            ]
        );


        $this->addColumn('name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text'
            ]);
        if ($this->_count > 1) {
            $this->addColumn('type_id',
                [
                    'header' => __('Type'),
                    'width' => '20px',
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => $this->type->toFilterOptionArray(true, false, $store->getId()),
                ]);
        }
        $this->addColumn('sku',
            [
                'header' => __('SKU'),
                'type' => 'text',
                'width' => '20px',
                'index' => 'sku',
            ]);

        $this->addColumn('price',
            [
                'header' => __('Price'),
                'type' => 'number',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            ]);

        if (!$this->_storeManager->isSingleStoreMode()) {

            $this->addColumn('websites',
                [
                    'header' => __('Websites'),
                    'width' => '100px',
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->websiteFactory->create()->toOptionHash(),
                ]);


        }
        $this->addColumn('sell',
            [
                'header' => __('Sell Product'),
                'width' => '100',
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'index' => 'sell',
                'renderer' => 'Ced\CsMultiSeller\Block\Search\Renderer\Sell',
            ]);
        return parent::_prepareColumns();
    }


    /**
     * Prepares Filter Buttons
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

    /**Return Grid Url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridsearch', ['_current' => true]);
    }


    /*   FILTER FOR PRODUCT NAME   */
    /**
     * @param $collection
     * @param \Magento\Framework\DataObject $column
     * @return $this|void
     */
    protected function _productNameFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $collection->addFieldToFilter('name', array('like' => '% ' . $value . ' %'));

        $vendor_id = array();
        foreach ($vendors as $_vendor) {
            $vendor_id[] = $_vendor->getSku();
        }

        $this->getCollection()->addAttributeToFilter('sku', array('in' => $vendor_id));
        return $this;
    }
}
