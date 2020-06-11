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
namespace Ced\CsOrder\Block\ListCreditmemo;

use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class Grid
 * @package Ced\CsOrder\Block\ListCreditmemo
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $_creditmemoRepository;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory
     */
    protected $csOrderCreditMemoCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $creditMemoCollectionFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * Grid constructor.
     * @param \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $csOrderCreditMemoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditMemoCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $csOrderCreditMemoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditMemoCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_creditmemoRepository=$creditmemoRepository;
        $this->sessionFactory = $sessionFactory;
        $this->csOrderCreditMemoCollectionFactory = $csOrderCreditMemoCollectionFactory;
        $this->creditMemoCollectionFactory = $creditMemoCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
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
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $vendor_id = $this->sessionFactory->create()->getVendorId();

        /** @var \Ced\CsOrder\Model\ResourceModel\Creditmemo\Collection $vcollection */
        $vcollection = $this->csOrderCreditMemoCollectionFactory->create()->addFieldToFilter('vendor_id', $vendor_id);
        $vendorCreditmemoarray = array_column($vcollection->getData(), 'creditmemo_id');

        /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $collection */
        $collection = $this->creditMemoCollectionFactory->create()->addAttributeToSelect('*');
        $coreResource   = $this->_resource;
        $salesorderGridTable = $coreResource->getTableName('sales_order_grid');
        $collection->getSelect()->joinLeft(
            array('order_item'=> $salesorderGridTable),
            'order_item.entity_id = main_table.order_id',
            array('main_table.increment_id'=>'main_table.increment_id','shipping_name'=>'shipping_name','order_item.increment_id'=>'order_item.increment_id', 'order_item.created_at'=>'order_item.created_at', 'main_table.created_at'=>'main_table.created_at')
        );
        $collection->addFieldToFilter('main_table.entity_id', array('in'=>$vendorCreditmemoarray));
        $collection->addFilterToMap('base_grand_total','main_table.base_grand_total');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id', [
                'header'    => __('Creditmemo ID #'),
                'type'      => 'text',
                'index'     => 'main_table.increment_id',
            ]
        );


        $this->addColumn(
            'created_at', [
                'header'    => __('Created'),
                'index'     => 'main_table.created_at',
                'type'      => 'datetime',
            ]
        );

        $this->addColumn(
            'order_id', [
                'header'    => __('Order #'),
                'index'     => 'order_item.increment_id',
                'type'      => 'text',
            ]
        );

        $this->addColumn(
            'order_created_at', [
                'header'    => __('Order Date'),
                'index'     => 'order_item.created_at',
                'type'      => 'datetime',
            ]
        );

        $this->addColumn(
            'shipping_name', [
                'header' => __('Ship to Name'),
                'index' => 'shipping_name',
            ]
        );
        $this->addColumn(
            'base_grand_total', [
                'header' => __('Refunded'),
                'type'  =>'number',
                'currency_code'=>'base_currency_code',
                'index' => 'base_grand_total',
                'renderer'=>'Ced\CsOrder\Block\Order\Creditmemo\Renderer\Grandtotal',
                'filter_condition_callback' => array($this, '_vendorrefund')
            ]
        );
        $this->addColumn(
            'state', [
                'header' => __('Status'),
                'index' => 'state',
                'type'=>'options',
                'options'=> $this->getStatus(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => 'View',
                        'url'     => array('base'=>'*/*/view'),
                        'field'   => 'creditmemo_id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ]
        );

        return parent::_prepareColumns();
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

    /**
     * Filter store condition
     *
     * @param                                         \Magento\Framework\Data\Collection $collection
     * @param                                         \Magento\Framework\DataObject      $column
     * @return                                        void
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
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/view',
            ['creditmemo_id' => $row->getEntityId()]
        );
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _vendorrefund($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if($value['from'])
        {
            $collection->addFieldToFilter('base_grand_total', array('gteq'=>$value['from']));
        }

        if ($value['to'])
        {
            $collection->addFieldToFilter('base_grand_total', array('lteq'=>$value['to']));
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        $options = array();
        foreach ($this->_creditmemoRepository->create()->getStates() as $id => $state) {
            $options[$id] = $state->render();
        }
        return $options;
    }
}
