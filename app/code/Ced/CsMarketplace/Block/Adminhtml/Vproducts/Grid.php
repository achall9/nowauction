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
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Adminhtml\Vproducts;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vproducts
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
         * @var \Magento\Framework\Module\Manager
         */
        protected $moduleManager;

        /**
         * @var  \Ced\CsMarketplace\Model\Vproducts
         */
        protected $_vproducts;

        /**
         * @var \Magento\Framework\Registry
         */
        protected $registry;

        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
         */
        protected $productCollection;

        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         */
        protected $storeManager;

        /**
         * @var \Magento\Catalog\Model\Product\Type
         */
        protected $type;

        /**
         * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
         */
        protected $setCollection;

    protected $vProductFilter = '';

        /**
         * Grid constructor.
         * @param \Magento\Backend\Block\Template\Context $context
         * @param \Magento\Backend\Helper\Data $backendHelper
         * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
         * @param \Magento\Framework\Module\Manager $moduleManager
         * @param \Magento\Framework\Registry $registry
         * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
         * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Catalog\Model\Product\Type $type
         * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection
         * @param \Magento\Catalog\Model\ResourceModel\Product $product
         * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
         * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
         * @param array $data
         */
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Ced\CsMarketplace\Model\Vproducts $vproducts,
            \Magento\Framework\Module\Manager $moduleManager,
            \Magento\Framework\Registry $registry,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Model\Product\Type $type,
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection,
            \Magento\Catalog\Model\ResourceModel\Product $product,
            \Magento\Store\Model\WebsiteFactory $websiteFactory,
            \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
            array $data = []
        )
        {
            $this->_vproducts = $vproducts;
            $this->moduleManager = $moduleManager;
            $this->registry = $registry;
            $this->productCollection = $productCollection;
            $this->storeManager = $storeManager;
            $this->type = $type;
            $this->setCollection = $setCollection;
            $this->product = $product;
            $this->websiteFactory = $websiteFactory;
            $this->vendorFactory = $vendorFactory;
            parent::__construct($context, $backendHelper, $data);
        }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
 protected function _prepareCollection()
    {
	
		$vendor_id = $this->getRequest()->getParam('vendor_id',0);
		$allowedIds = array();
    	if($this->registry->registry('usePendingProductFilter')){
            $this->vProductFilter = 'pending';
    			$vproducts = $this->_vproducts->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::PENDING_STATUS,0,0,-1);
    			$this->registry->unregister('usePendingProductFilter');
    			$this->registry->unregister('useApprovedProductFilter');
    	} elseif($this->registry->registry('useApprovedProductFilter') ){
            $this->vProductFilter = 'approved';
    			$vproducts = $this->_vproducts->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS,0,0,-1);
    			$this->registry->unregister('useApprovedProductFilter');
    			$this->registry->unregister('usePendingProductFilter');
    	} else {
			$vproducts = $this->_vproducts->getVendorProducts('',0,0,-1);
		}
		foreach($vproducts as $vproduct) {
			$allowedIds[] = $vproduct->getProductId();
		}		
    	   
        $store = $this->_getStore();
        $collection = $this->productCollection->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
			->addAttributeToFilter('entity_id',array('in'=>$allowedIds));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            $adminStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
        }
        $collection->joinField('check_status','ced_csmarketplace_vendor_products', 'check_status','product_id=entity_id',null,'left');
        $collection->joinField('vendor_id','ced_csmarketplace_vendor_products', 'vendor_id','product_id=entity_id',null,'left');
        $collection->joinField('reason','ced_csmarketplace_vendor_products', 'reason','product_id=entity_id',null,'left');
        $collection->joinField('website_id','ced_csmarketplace_vendor_products', 'website_id','product_id=entity_id',null,'left');
        
		if($vendor_id) {
			$collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
		}

       
        $this->setCollection($collection);
		
        parent::_prepareCollection();
        
        
        $this->getCollection()->addWebsiteNamesToResult();
        
        return $this;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );
        $this->addColumn('vendor_id',
            array(
                'header' => __('Vendor Name'),
                'align' => 'left',
                'width' => '100px',
                'index' => 'vendor_id',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname',
                'filter_condition_callback' => array($this, '_vendornameFilter'),
            ));


        $this->addColumn('type_id',
            array(
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->type->getOptionArray(),
            ));
        $sets = $this->setCollection->create()
            ->setEntityTypeFilter($this->product->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header' => __('Attrib. Set Name'),
                'width' => '60px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ));

        $this->addColumn('sku',
            array(
                'header' => __('SKU'),
                'index' => 'sku',
            ));
        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            ));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header' => __('Qty'),
                    'width' => '50px',
                    'type' => 'number',
                    'index' => 'qty',
                ));
        }


        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'website_id',
                    'type' => 'options',
                    'options' => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites'
                ]
            );
        }

        if (!$this->registry->registry('usePendingProductFilter') && !$this->registry->registry('useApprovedProductFilter')) {
            $this->addColumn('check_status',
                array(
                    'header' => __('Status'),
                    'width' => '70px',
                    'index' => 'check_status',
                    'type' => 'options',
                    'options' => $this->_vproducts->getOptionArray(),
                ));
        }

        $this->addColumn('action',
            array(
                'header' => __('Action'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\Action',
                'index' => 'action',
            ));

         $this->addColumn('reason',
            array(
                'header' => __('Disapproval Reason'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\Reason',
                'index' => 'action',
            ));

        $this->addColumn('view',
            array(
                'header' => __('View'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\View',
                'index' => 'view',
            ));

        return parent::_prepareColumns();
    }

    /**
     * After load collection
     * @param $collection
     * @param \Magento\Framework\DataObject $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _vendornameFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('name', ['like' => $value . '%']);
        $vendor_id = array();
        foreach ($vendors as $_vendor) {
            $vendor_id[] = $_vendor->getId();
        }
        $this->getCollection()->addFieldToFilter('vendor_id', array('in' => $vendor_id));
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
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        $statuses = $this->_vproducts->getMassActionArray();

        $this->getMassactionBlock()->addItem('status', array(
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',

                    'label' => __('Status'),
                    'default' => '-1',
                    'values' => $statuses,
                )
            )
        ));
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $param = [
            '_secure' => true,
            '_current' => true,
            'vproduct_filter' => $this->vProductFilter
        ];
        return $this->getUrl('*/*/vproductgrid', $param);
    }

   
    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
