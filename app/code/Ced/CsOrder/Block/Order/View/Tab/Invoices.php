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

namespace Ced\CsOrder\Block\Order\View\Tab;

use Magento\Customer\Model\Session;

/**
 * Order Shipments grid
 */
class Invoices extends \Magento\Backend\Block\Widget\Grid\Extended  implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory
     */
    protected $_invoiceFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_invoice;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Invoices constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $invoiceFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param Session $customerSession
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $invoiceFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Invoice $invoice,
        Session $customerSession,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->_invoiceFactory = $invoiceFactory;
        $this->resourceConnection = $resourceConnection;
        $this->_coreRegistry = $coreRegistry;
        $this->_invoice = $invoice;
        $this->session = $customerSession;
        $this->vendor = $vendor;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_invoices');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('filter');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $vendor_id = $this->session->getVendorId();
        $collection = $this->_invoiceFactory->create();
        $invoiceGridTable = $this->resourceConnection->getTableName('sales_invoice_grid');
        $collection->getSelect()->join(
            array('invoice_flat'=> $invoiceGridTable),
            'invoice_flat.entity_id = main_table.invoice_id',
            array(
                'invoice_flat.*',
                'main_table.vendor_id'
            )
        )->where("vendor_id = ".$vendor_id." and invoice_flat.order_id = ".$this->getOrder()->getId());
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
            'increment_id',
            [
                'header' => __('Invoice #'),
                'type' => 'text',
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => __('Bill to Name'),
                'type' => 'text',
                'index' => 'billing_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );


        $this->addColumn(
            'created_at',
            [
                'header' => __('Invoice Date'),
                'type' => 'datetime',
                'index' => 'created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',

            ]
        );

        $this->addColumn(
            'state',
            [
                'header' => __('Status'),
                'type' => 'options',
                'options' => $this->_invoice->getStates(),
                'index' => 'state',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'

            ]
        );


        $this->addColumn(
            'grand_total',
            [
                'header' => __('Amount'),
                'type' => 'currency',
                'index' => 'grand_total',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'renderer' =>  'Ced\CsOrder\Block\Order\Invoice\Renderer\Grandtotal'

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
     * @param $collection
     * @param \Magento\Framework\DataObject $column
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
        return $this->getUrl('*/*/invoices', ['_current' => true,'_secure'=>true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/invoice/view',
            array(
                'invoice_id'=> $row->getEntityId(),
                'order_id'  => $this->getRequest()->getParam('order_id'),
                '_secure'=>true
            )
        );
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _vendornameFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $vendorIds =   $this->vendorCollectionFactory->create()
            ->addAttributeToFilter('name', array('like' => '%'.$column->getFilter()->getValue().'%'))
            ->getAllIds();

        if(count($vendorIds)>0) {
            $this->getCollection()->addFieldToFilter('vendor_id', array('in', $vendorIds));
        }
        else{
            $this->getCollection()->addFieldToFilter('vendor_id', '');
        }
        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @return mixed
     */
    public function getVorder()
    {
        return $this->_coreRegistry->registry('current_vorder');
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
