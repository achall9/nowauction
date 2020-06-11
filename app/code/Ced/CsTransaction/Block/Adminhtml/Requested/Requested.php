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

namespace Ced\CsTransaction\Block\Adminhtml\Requested;

/**
 * Class Requested
 * @package Ced\CsTransaction\Block\Adminhtml\Requested
 */
class Requested extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory
     */
    protected $vpaymentCollection;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $vpaymentRequested;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Requested constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory $vpaymentCollection
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory $vpaymentCollection,
        \Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->vpaymentCollection = $vpaymentCollection;
        $this->vpaymentRequested = $vpaymentRequested;
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
        $this->setDefaultSort('created_');
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
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $collection = $this->vpaymentCollection->create();
        if ($vendor_id) {

            $collection->addFieldToFilter('vendor_id', array('eq' => $vendor_id));
        }

        $collection = $this->vpaymentRequested->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn('created_at', array(
            'header' => __('Request Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('vendor_id', array(
            'header' => __('Vendor Name'),
            'align' => 'left',
            'index' => 'vendor_id',
            'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname',
            'filter_condition_callback' => array($this, '_vendornameFilter'),
        ));

        $this->addColumn('order_id', array(
            'header' => __('Order IDs#'),
            'align' => 'left',
            'index' => 'order_id',
            'renderer' => 'Ced\CsTransaction\Block\Adminhtml\Requested\Renderer\Orderdesc',
        ));


        $this->addColumn('amount',
            array(
                'header' => __('Amount To Pay'),
                'index' => 'amount',
                'type' => 'currency',
                'currency' => 'base_currency'
            ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'options' => \Ced\CsMarketplace\Model\Vpayment\Requested::getStatuses(),
            'renderer' => 'Ced\CsTransaction\Block\Adminhtml\Requested\Renderer\Paynow',
            'filter_condition_callback' => array($this, '_requestedFilter'),
        ));

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
     * @param $collection
     * @param \Magento\Framework\DataObject $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _vendornameFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('name', ['like' => $value . '%']);
        $vendor_id = array();
        foreach ($vendors as $_vendor) {
            $vendor_id[] = $_vendor->getId();
        }
        $this->getCollection()->addFieldToFilter('vendor_id', array('eq' => $vendor_id));
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
        return $this->getUrl('cstransaction/vpayments/grid', ['_current' => true]);
    }

    /**
     * @param $collection
     * @param \Magento\Framework\DataObject $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _requestedFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addFieldToFilter('status', array('eq' => $column->getFilter()->getValue()));
    }

}
