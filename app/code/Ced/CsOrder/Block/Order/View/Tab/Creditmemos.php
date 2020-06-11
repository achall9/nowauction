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
class Creditmemos extends \Magento\Backend\Block\Widget\Grid\Extended  implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory
     */
    protected $_creditMemoFactory;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * Creditmemos constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $creditMemoFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param Session $customerSession
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $creditMemoFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        Session $customerSession,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_creditMemoFactory = $creditMemoFactory;
        $this->_creditmemo = $creditmemo;
        $this->session = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->vendor = $vendor;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_creditmemos');
        $this->setDefaultSort('id');
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
        $collection = $this->_creditMemoFactory->create();
        $invoiceGridTable = $this->resourceConnection->getTableName('sales_creditmemo_grid');
        $collection->getSelect()->join(
            array('creditmemo_flat'=> $invoiceGridTable),
            'creditmemo_flat.entity_id = main_table.creditmemo_id',
            array(
                'creditmemo_flat.*' ,
                'main_table.vendor_id'
            )
        )->where("vendor_id = ".$vendor_id." and creditmemo_flat.order_id = ".$this->getOrder()->getId());
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
                'header' => __('Credit Memo #'),
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
                'header' => __('Created At'),
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
                'options' => $this->_creditmemo->getStates(),
                'index' => 'state',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'

            ]
        );

        if($this->getVorder()->isAdvanceOrder()) {
            $this->addColumn(
                'base_grand_total',
                [
                    'header' => __('Amount'),
                    'type' => 'currency',
                    'index' => 'base_grand_total',
                    'renderer'  => 'Ced\CsOrder\Block\Order\Creditmemo\Renderer\Grandtotal'
                ]
            );
        }else{
            $this->addColumn(
                'base_grand_total',
                [
                    'header' => __('Amount'),
                    'type' => 'currency',
                    'index' => 'base_grand_total',
                    'renderer'  => 'Ced\CsOrder\Block\Order\Creditmemo\Renderer\Grandtotal'
                ]
            );
        }

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
        return $this->getUrl('*/*/creditmemos', ['_current' => true,'_secure'=>true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/creditmemo/view',
            array(
                'creditmemo_id'=> $row->getEntityId(),
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

        if (count($vendorIds) > 0) {
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
        return __('Credit Memos');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Credit Memos');
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
