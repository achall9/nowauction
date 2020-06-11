<?php
namespace Ced\CsCommission\Block\Adminhtml\Commission;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
	protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    protected $_vendorFactory;
    protected $_backendSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
		\Ced\CsCommission\Model\ResourceModel\Commission\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
		$this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_backendSession = $context->getBackendSession();
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('productGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
       
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		try{
			$collection =$this->_collectionFactory->load();
            if($this->_backendSession->getCedtype()){
                $collection->addFieldToFilter('type',$this->_backendSession->getCedtype());
                $collection->addFieldToFilter('type_id',$this->_backendSession->getCedtypeid());
            }

            if($this->_backendSession->getCedVendorId()){
                $collection->addFieldToFilter('vendor',$this->_backendSession->getCedVendorId());
            }
		    

			$this->setCollection($collection);

			parent::_prepareCollection();
		  
			return $this;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();die;
		}
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
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
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'category',
            [
                'header' => __('Category'),
                'index' => 'category',
                'class' => 'category',
                'type'  => 'options',
                'options' => $this->_getCategoryOptions(),
            ]
        );
        $this->addColumn(
            'vendor',
            [
                'header' => __('Vendor'),
                'index' => 'vendor',
                'class' => 'vendor',
                'type'  => 'options',
                'options' => $this->_getVendorOptions(),
            ]
        );
		$this->addColumn(
            'method',
            [
                'header' => __('Method'),
                'index' => 'method',
                'class' => 'method'
            ]
        );
		$this->addColumn(
            'fee',
            [
                'header' => __('Fee'),
                'index' => 'fee',
                'class' => 'fee'
            ]
        );
		$this->addColumn(
            'priority',
            [
                'header' => __('Priority'),
                'index' => 'priority',
                'class' => 'priority'
            ]
        );
		$this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'date',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

     /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        if($this->getRequest()->getParam('popup')  )
        {
            $action = $this->getUrl('cscommission/*/massDelete',['popup'=>true]);
        }
        else
        {
            $action = $this->getUrl('cscommission/*/massDelete');
        }
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $action,
                'confirm' => __('Are you sure?')
            )
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        if($this->getRequest()->getParam('popup')  )
        {
            return $this->getUrl('cscommission/*/grid', ['_current' => true,'popup'=>true]);
        }
        else
        {
            return $this->getUrl('cscommission/*/grid', ['_current' => true]);
        }
        
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if($this->getRequest()->getParam('popup')  )
        {
            return $this->getUrl(
                'cscommission/*/edit',
                ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId(),'popup'=>true]
            );
        }
        else
        {
           return $this->getUrl(
                'cscommission/*/edit',
                ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
            ); 
        }    
        
    }

    /**
     * Get category options
     *
     * @return array
     */
    protected function _getCategoryOptions()
    {
        $items = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )/*->setPageSize(
            3
        )*/->load()->getItems();

        $result = [];
        foreach($items as $item) {
            
            $result[$item->getEntityId()] = $item->getName();
        }
       
        return $result;
    }

    /**
     * Get vendor options
     *
     * @return array
     */
    protected function _getVendorOptions()
    {
        $items = $this->_vendorFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )/*->setPageSize(
            3
        )*/->load()->getItems();

        $result = [];
        $result[0] = 'All';
        foreach($items as $item) {
            
            $result[$item->getEntityId()] = $item->getName();
        }
       
        return $result;
    }

}
