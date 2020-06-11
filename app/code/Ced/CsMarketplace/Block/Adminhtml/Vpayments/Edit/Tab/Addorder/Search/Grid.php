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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search;
/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $_vordersCollFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollFactory,
        array $data = []
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_resourceConnection = $resourceConnection;
        $this->_vordersCollFactory = $vordersCollFactory;

        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
        $this->setId('ced_csmarketplace_order_search_grid123');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

    }


    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        } else {
            return $this->_storeManager->getStore();
        }
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block' => 'search_grid', '_current' => true, 'collapse' => null));
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return $this
     */
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $flag = false;
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) &&
        in_array($params['type'], array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates())) ? $params['type'] :
            \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : 0;
        $orderIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : array();
        $orderTable = $this->_resourceConnection->getTableName('sales_order');
        $collection = $this->_vordersCollFactory->create();


        $collection->addFieldToFilter('main_table.vendor_id', array('eq' => $vendorId));

        if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
            $collection->addFieldToFilter('main_table.order_payment_state',
                array('eq' => \Magento\Sales\Model\Order\Invoice::STATE_PAID))
                ->addFieldToFilter('main_table.payment_state',
                    array('eq' => \Ced\CsMarketplace\Model\Vorders::STATE_REFUND));
        } else {

            $collection->addFieldToFilter('main_table.order_payment_state',
                array('eq' => \Magento\Sales\Model\Order\Invoice::STATE_PAID))
                ->addFieldToFilter('main_table.payment_state',
                    array('eq' => \Ced\CsMarketplace\Model\Vorders::STATE_OPEN));
        }

        $collection
            ->getSelect()
            ->columns(
                array('net_vendor_earn' => new \Zend_Db_Expr(
                                                        '(main_table.order_total - main_table.shop_commission_fee)')
                                                )
            );
        /*$collection->getSelect()
            ->joinLeft($orderTable, 'main_table.order_id =' . $orderTable . '.increment_id', array('*'));*/


        $this->setCollection($collection);

        return $this;
    }

    /**
     * Prepare columns
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
        $this->addColumn('order_id', array(
            'header' => __('Order ID#'),
            'align' => 'left',
            'index' => 'order_id',
            'filter_index' => 'order_id',
        ));

        $this->addColumn('base_order_total', array(
            'header' => __('G.T. (Base)'),
            'index' => 'base_order_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',

        ));
        $this->addColumn('order_total', array(
            'header' => __('G.T.'),
            'index' => 'order_total',
            'type' => 'currency',
            'currency' => 'currency',
        ));


        $this->addColumn('shop_commission_fee', array(
            'header' => __('Commission Fee'),
            'index' => 'shop_commission_fee',
            'type' => 'currency',
            'currency' => 'currency',

        ));

        $this->addColumn('net_vendor_earn', array(
            'header' => __('Vendor Payment'),
            'index' => 'net_vendor_earn',
            'type' => 'currency',
            'currency' => 'currency',
        ));
        $this->addColumnAfter('relation_id', array(
            'header' => __('Select'),
            'sortable' => false,
            'header_css_class' => 'a-center',
            'inline_css' => 'csmarketplace_relation_id checkbox',
            'index' => 'id',
            'type' => 'checkbox',
            'field_name' => 'in_orders',
            'values' => $this->_getSelectedOrders(),
            'disabled_values' => array(),
            'align' => 'center',
        ), 'net_vendor_earn');
        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedOrders()
    {
        $params = $this->getRequest()->getParams();
        $orderIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : array();
        return $orderIds;
    }
}