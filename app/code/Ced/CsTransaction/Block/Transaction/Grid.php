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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Transaction;

/**
 * Class Grid
 * @package Ced\CsTransaction\Block\Transaction
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $itemOrders;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Grid constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsTransaction\Model\Items $itemOrders
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $session,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsTransaction\Model\Items $itemOrders,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->_vtorders = $itemOrders;
        $this->_csMarketplaceHelper = $csmarketplaceHelper;
        $this->session = $session;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $sales_order_table = $this->resourceConnection->getTableName('sales_order');
        $collection = $this->_vtorders
            ->getCollection()
            ->addFieldToFilter('vendor_id', array('eq' => $this->getVendorId()));
        $collection->getSelect()->joinLeft($sales_order_table, 'main_table.order_increment_id=' . $sales_order_table . '.increment_id ', ['created_at']);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'order_date',
            [
                'header' => __('Order Date'),
                'index' => 'created_at',
                'type' => 'date'
            ]
        );

        $this->addColumn('order_id', ['header' => __('Order Id'), 'index' => 'order_increment_id']);

        $this->addColumn(
            'pending_amount',
            [
                'header' => __('Pending Amount'),
                'type' => 'currency',
                //'currency_code'=>"currency",
                'index' => 'item_fee',
                // 'filter_condition_callback' => array($this, '_vendorpaymentFilter'),
                'align' => 'left'
            ]
        );
        $this->addColumn(
            'cancelled_amount',
            [
                'header' => __('Cancelled Amount'),
                'type' => 'currency',
                //'currency_code'=>"currency",
                'index' => 'amount_refunded',
                // 'filter_condition_callback' => array($this, '_vendorpaymentFilter'),
                'align' => 'left'
            ]
        );

        $this->addColumn(
            'edits',
            [
                'header' => __('Action'),
                'caption' => __('Action'),
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Ced\CsTransaction\Block\Transaction\Grid\Renderer\Action',
            ]
        );


        return parent::_prepareColumns();
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _vendorpaymentFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $order_total = $this->_csMarketplaceHelper->getTableKey('order_total');
        $shop_commission_fee = $this->_csMarketplaceHelper->getTableKey('shop_commission_fee');
        if (isset($value['from'])) {
            $collection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) >='" . $value['from'] . "'");
        }
        if (isset($value['to'])) {
            $collection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) <='" . $value['to'] . "'");
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->session->getVendorId();
    }
}
