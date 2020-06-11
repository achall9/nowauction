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

namespace Ced\CsOrder\Block\ListInvoice;

use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class Grid
 * @package Ced\CsOrder\Block\ListInvoice
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory
     */
    protected $csOrderInvoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $csOrderInvoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $csOrderInvoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->_resource = $resource;
        $this->_invoiceRepository =$invoiceRepository;
        $this->csOrderInvoiceCollectionFactory = $csOrderInvoiceCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
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
        $this->setDefaultSort('created_at');
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
        $vcollection = $this->csOrderInvoiceCollectionFactory->create()->addFieldToFilter('vendor_id', $vendor_id);
        $vendorCreditmemoarray = array_column($vcollection->getData(), 'invoice_id');
        $collection = $this->invoiceCollectionFactory->create()->addAttributeToSelect('*');
        $coreResource = $this->_resource;
        $salesorderGridTable = $coreResource->getTableName('sales_order_grid');
        $collection->getSelect()->joinLeft(
            array('order_item'=> $salesorderGridTable),
            'order_item.entity_id = main_table.order_id',
            array(
                'main_table.increment_id' => 'main_table.increment_id',
                'billing_name' => 'billing_name',
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
            'real_order_id',
            [
                'header' => __('Invoice #'),
                'type' => 'text',
                'index' => 'main_table.increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


        $this->addColumn(
            'created_at',
            [
                'header' => __('Invoiced On'),
                'type' => 'date',
                'index' => 'main_table.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );

        $this->addColumn(
            'sales_order_id',
            [
                'header' => __('Order #'),
                'type' => 'text',
                'index' => 'order_item.increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );
        $this->addColumn(
            'order_date',
            [
                'header' => __('Order Date'),
                'type' => 'date',
                'index' => 'order_item.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => __('Billing To Name'),
                'type' => 'text',
                'index' => 'billing_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );

        $this->addColumn(
            'order_total',
            [
                'header' => __('G.T.'),
                'type' => 'number',
                'currency'=>'base_currency_code',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'sortable'  => false,
                'index' => 'grand_total'
                //'renderer'=>'Ced\CsOrder\Block\Order\Invoice\Renderer\Grandtotal'
            ]
        );

        $this->addColumn(
            'state',
            [
                'header' => __('Status'),
                'type' => 'options',
                'index' => 'state',
                'options'=>$this->getStatus(),
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __('View'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '*/*/view'
                        ],
                        'field' => 'invoice_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
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
            ['invoice_id' => $row->getEntityId()]
        );
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        $options = array();
        foreach ($this->_invoiceRepository->create()->getStates() as $id => $state) {
            $options[$id] = $state->render();
        }
        return $options;
    }
}
