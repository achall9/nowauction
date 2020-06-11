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
 * @package     Ced_CsTransaction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search;

/**
 * Class Grid
 * @package Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search
 */
class Grid extends \Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid
{
    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $_vtorders;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $_vorders;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_csMarketplaceHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $orderHelper;

    /**
     * Grid constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsTransaction\Model\Items $vtorders
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Ced\CsOrder\Model\Invoice $invoice
     * @param \Ced\CsOrder\Helper\Data $orderHelper
     * @param \Ced\CsMarketplace\Model\vendor $vendor
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ced\CsTransaction\Model\Items $vtorders,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Ced\CsOrder\Model\Invoice $invoice,
        \Ced\CsOrder\Helper\Data $orderHelper,
        \Ced\CsMarketplace\Model\vendor $vendor,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($vtorders, $vorders, $helperData, $invoice, $orderHelper, $vendor, $context, $backendHelper, $data);

        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
        $this->setId('ced_csmarketplace_order_search_grid');

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @param bool $flag
     * @return object
     */
    protected function _prepareCollection($flag = false)
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) && in_array($params['type'], array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates())) ? $params['type'] : \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : 0;
        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
        if ($this->orderHelper->isActive()) {

            $collection = $this->_vtorders
                ->getCollection()
                ->addFieldToFilter('vendor_id', array('eq' => $vendorId));
            if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
                $collection->addFieldToFilter('qty_ready_to_refund', array('gt' => 0));

            } else {
                $collection->getSelect()->where('(`qty_ordered` = `qty_ready_to_pay`+`qty_refunded`) AND (qty_ordered !=qty_refunded)');
            }
            $collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee})")));
            $collection->getSelect()->joinLeft($orderTable, 'main_table.order_increment_id =' . $orderTable . '.increment_id', array('*'));
            $this->setCollection($collection);
        } else {
            parent::_prepareCollection(true);
        }

        return parent::_prepareCollection(true);
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        if ($this->orderHelper->isActive()) {
            parent::_prepareColumns();
            $this->removeColumn('relation_id');
            $this->removeColumn('vendor_id');
            $this->removeColumn('order_payment_state');
            $this->removeColumn('payment_state');
            $this->removeColumn('action');
            $params = $this->getRequest()->getParams();
            $type = isset($params['type']) && in_array($params['type'], array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates())) ? $params['type'] : \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;

            if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {

                $this->removeColumn('qty_ready_to_pay');

                $this->removeColumn('qty_paid');
                $this->removeColumn('net_vendor_earn');

            } else {

                $this->removeColumn('qty_ready_to_refund');
                $this->removeColumn('qty_refunded');
                $this->removeColumn('net_vendor_return');
            }

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


        } else {
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
        }

        return $this;
    }

    /**
     * prepare return url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block' => 'search_grid', '_current' => true, 'collapse' => null));
    }

    /**
     * get selected orders
     *
     * @return array
     */
    protected function _getSelectedOrders()
    {
        $params = $this->getRequest()->getParams();
        $orderIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : array();
        return $orderIds;
    }


    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return $this
     */
    public function removeColumn($columnId)
    {
        if ($this->getColumnSet()->getChildBlock($columnId)) {
            $this->getColumnSet()->unsetChild($columnId);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = array_pop($this->getColumnSet()->getChildNames());
            }
        }
        return $this;
    }
}