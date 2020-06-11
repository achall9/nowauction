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

namespace Ced\CsOrder\Block\ListShipment;

use Magento\Customer\Model\SessionFactory;

/**
 * Class Grid
 * @package Ced\CsOrder\Block\ListShipment
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $csOrderShipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * Grid constructor.
     * @param \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $csOrderShipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param SessionFactory $customerSession
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $csOrderShipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        SessionFactory $customerSession,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->csOrderShipmentCollectionFactory = $csOrderShipmentCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_resource = $resource;
        $this->sessionFactory = $customerSession;
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
        $vcollection = $this->csOrderShipmentCollectionFactory->create()->addFieldToFilter('vendor_id', $vendor_id);
        $vendorCreditmemoarray = array_column($vcollection->getData(), 'shipment_id');
        $collection=$this->shipmentCollectionFactory->create()->addAttributeToSelect('*');

        $coreResource   = $this->_resource;
        $salesorderGridTable = $coreResource->getTableName('sales_order_grid');
        $collection->getSelect()->join(
            array('order_item'=> $salesorderGridTable),
            'order_item.entity_id = main_table.order_id',
            array(
                'main_table.increment_id' => 'main_table.increment_id',
                'shipping_name' => 'shipping_name',
                'order_item.increment_id' => 'order_item.increment_id',
                'order_item.created_at' => 'order_item.created_at',
                'main_table.created_at' => 'main_table.created_at'
            )
        );
        $collection->addFieldToFilter('main_table.entity_id', array('in'=>$vendorCreditmemoarray));
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
                'header'    => __('Shipment #'),
                'type'      => 'text',
                'index'     => 'main_table.increment_id',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Date Shipped'),
                'type' => 'date',
                'index' => 'main_table.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

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
            'order_created_at',
            [
                'header' => __('Order Date'),
                'type' => 'date',
                'index' => 'order_item.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );

        $this->addColumn(
            'shipping_name', [
                'header' => __('Ship to Name'),
                'index' => 'shipping_name',
            ]
        );

        $this->addColumn(
            'total_qty', [
                'header' => __('Shipping Qty'),
                'index' => 'total_qty',
                'renderer'=>'Ced\CsOrder\Block\Order\Shipment\Renderer\Totalqty'
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
                        'field'   => 'shipment_id'
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
     * @return \Magento\Backend\Block\Widget\Grid\Extended|void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter store condition
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
            ['shipment_id' => $row->getEntityId()]
        );
    }
}
