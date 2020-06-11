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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsMarketplace\Model\VPaymentsFactory
     */
    protected $_vpaymentFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
        
        $this->_vpaymentFactory = $vpaymentFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
	    $vendor_id = $this->getRequest()->getParam('vendor_id',0);
        $collection = $this->_vpaymentFactory->create()->getCollection();
        if($vendor_id) {
            $collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
        }
        $this->setCollection($collection);
    
        parent::_prepareCollection();
        return $this;
    }
 

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
    	$this->addExportType('*/*/massCsvExport',__('CSV'));
    	$this->addExportType('*/*/massXmlExport',__('XML'));
		$this->addColumn('created_at', array(
            'header' => __('Transaction Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
		
	$this->addColumn('transaction_id', array(
  			'header'    => __('Transaction ID#'),
  			'align'     => 'left',
   			'index'     => 'transaction_id',
  			'filter_index'  => 'transaction_id',

  	));
	
	$this->addColumn('vendor_id', array(
  			'header'    => __('Vendor Name'),
  			'align'     => 'left',
   			'index'     => 'vendor_id',
			'is_system' => true,
			'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname',
			'filter_condition_callback' => array($this, '_vendornameFilter'),
		));
		
	$this->addColumn('payment_method', array(
  			'header'    => __('Payment Mode'),
  			'align'     => 'left',
   			'index'     => 'payment_method',
  			'filter_index'  => 'payment_method',
			'type'  => 'options',
			'options' => \Ced\CsMarketplace\Helper\Acl::$PAYMENT_MODES
  	));
	
	$this->addColumn('transaction_type',
        array(
            'header'=> __('Transaction Type'),
            'index' => 'transaction_type',
			'type'  => 'options',
			'options' => \Ced\CsMarketplace\Model\Vpayment::getStates(),
    ));
	
	$this->addColumn('base_amount',
        array(
            'header'=> __('Amount'),
            'index' => 'base_amount',
			'type'          => 'currency',
            'currency' => 'base_currency'
    ));
	
	
	$this->addColumn('base_fee',
        array(
            'header'=> __('Adjustment Amount'),
            'index' => 'base_fee',
			'type'          => 'currency',
           'currency' => 'base_currency'
    ));
	
	
	$this->addColumn('base_net_amount',
        array(
            'header'=> __('Net Amount'),
            'index' => 'base_net_amount',
			'type'          => 'currency',
            'currency' => 'base_currency'
    ));
	
	$this->addColumn('amount_desc',
		array(
				'header'=> __('Amount Description'),
				'index' => 'amount_desc',
				'type'   => 'text',
				'is_system' => true,
				'renderer'=> 'Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc',
		));
		
		$this->addColumn('amount_details',
		array(
				'header'=> __('Amount Description'),
				'index' => 'amount_details',
				'column_css_class'=>'no-display',
                'header_css_class'=>'no-display',
				'type'          => 'text',
				'renderer'=> 'Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\OrderDetails',
		));
			
	$this->addColumn('action',
			array(
					'header'    =>  __('Action'),
					'width'     => '100',
					'type'      => 'action',
					'getter'    => 'getId',
					'actions'   => array(
							array(
									'caption'   => __('View'),
									'url'       => array('base'=> '*/vpayments/details'),
									'field'     => 'id'
							)
					),
					'filter'    => false,
					'sortable'  => false,
					'index'     => 'stores',
					'is_system' => true,
			));
        return parent::_prepareCollection();
    }
 
    /**
     * After load collection
     *
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

   protected function _vendornameFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $this->vendorFactory->create()->getCollection()
                        ->addAttributeToFilter('name',['like'=>$value.'%']);
        $vendor_id = array();
        foreach ($vendors as $_vendor) {
                $vendor_id[] = $_vendor->getId();
            }        
        $this->getCollection()->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
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
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/vpaymentsgrid', ['_current' => true]);
    }
 
    /**
     * @param \Ced\CsMarketplace\Model\Vpayments|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/vpayments/details',
            ['id' => $row->getId()]
        );
    }
}
