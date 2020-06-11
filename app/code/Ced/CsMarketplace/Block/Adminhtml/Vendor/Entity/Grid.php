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
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Group
     */
    protected $_group;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Status
     */
    protected $_status;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Group $group
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Status $status
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\System\Config\Source\Group $group,
        \Ced\CsMarketplace\Model\System\Config\Source\Status $status,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    )
    {
        $this->_vendorFactory = $vendorFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_group = $group;
        $this->_status = $status;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_vendorFactory->create()->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('reason');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addExportType('*/*/massCsvExport', __('CSV'));
        $this->addExportType('*/*/massXmlExport', __('XML'));
        $this->addColumn('created_at', [
                'header' => __('Created At'),
                'align' => 'right',
                'index' => 'created_at',
                'type' => 'date',
                'filter_condition_callback' => array($this, '_createdAtFilter'),
            ]
        );

        $this->addColumn('name', [
                'header' => __('Vendor Name'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn('email', [
                'header' => __('Vendor Email'),
                'align' => 'left',
                'index' => 'email',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );

        $this->addColumn('group', [
                'header' => __('Vendor Group'),
                'align' => 'left',
                'index' => 'group',
                'type' => 'options',
                'options' => $this->_group->toFilterOptionArray(),
                'header_css_class' => 'col-group',
                'column_css_class' => 'col-group'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id', [
                    'header' => __('Websites'),
                    'index' => 'website_id',
                    'type' => 'options',
                    'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites'
                ]
            );
        }

        $this->addColumn('status', [
                'header' => __('Vendor Status'),
                'align' => 'left',
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->toOptionArray(true),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumn('approve', [
                'header' => __('Approve'),
                'align' => 'left',
                'index' => 'entity_id',
                'filter' => false,
                'type' => 'text',
                'is_system' => true,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer\Approve'
            ]
        );

        $this->addColumn('reason', [
                'header' => __('Disapproval Reason'),
                'align' => 'left',
                'index' => 'reason',
                'filter' => false,
                'type' => 'text',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer\Reason'
            ]
        );

        $this->addColumn('shop_disable', [
                'header' => __('Vendor Shop Status'),
                'align' => 'left',
                'index' => 'shop_disable',
                'filter' => false,
                'is_system' => true,
                'type' => 'text',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer\Disableshop'
            ]
        );

        $this->addColumn('shop_status', [
                'header' => __('Vendor Shop Status'),
                'align' => 'left',
                'index' => 'shop_status',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'filter' => false,
                'type' => 'text',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer\ShopStatus'
            ]
        );

        $this->addColumn('edit', [
                'header' => __('Edit'),
                'type' => 'action',
                'is_system' => true,
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => ['base' => '*/*/edit', 'params' => ['store' => $this->getRequest()->getParam('store')]],
                        'field' => 'vendor_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('vendor_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete Vendor(s)'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->toOptionArray();

        $this->getMassactionBlock()->addItem('status',
            [
                'label' => __('Change Vendor(s) Status'),
                'url' => $this->getUrl('*/*/massStatus/', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => ('Status'),
                        'default' => '-1',
                        'values' => $statuses,
                    ]
                ]
            ]
        );

        $this->getMassactionBlock()->addItem('shop_disable',
            [
                'label' => __('Change Vendor Shops'),
                'url' => $this->getUrl('*/*/massDisable/', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'shop_disable',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Vendor Shop Status'),
                        'default' => '-1',
                        'values' => [
                            ['value' => \Ced\CsMarketplace\Model\Vshop::ENABLED, 'label' => __('Enabled')],
                            ['value' => \Ced\CsMarketplace\Model\Vshop::DISABLED, 'label' => __('Disabled')]
                        ],
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit',
            ['vendor_id' => $row->getId()]
        );
    }

    /**
     * @param $collection
     * @param \Magento\Framework\DataObject $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _createdAtFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $creationData = $column->getFilter()->getValue();
        if ((isset($creationData['from']) && !empty($creationData['from'])) || (isset($creationData['to']) && !empty($creationData['to']))) {
            if ((isset($creationData['from']) && !empty($creationData['from'])) && (isset($creationData['to']) && !empty($creationData['to']))) {
                $fromDate = date('Y-m-d H:i:s', strtotime($creationData['from']->format('Y-m-d H:i:s')));

                $toDate = date('Y-m-d H:i:s', strtotime($creationData['to']->format('Y-m-d H:i:s')) + 86400);
                $this->getCollection()->addFieldToFilter('created_at', ['from' => $fromDate, 'to' => $toDate]);
            } elseif (isset($creationData['from']) && !empty($creationData['from'])) {
                $fromDate = date('Y-m-d H:i:s', strtotime($creationData['from']->format('Y-m-d H:i:s')));
                $toDate = date('Y-m-d H:i:s');

                $this->getCollection()->addFieldToFilter('created_at', ['from' => $fromDate, 'to' => $toDate]);
            } else {
                $toDate = date('Y-m-d H:i:s', strtotime($creationData['to']->format('Y-m-d H:i:s')) + 86400);

                $this->getCollection()->addFieldToFilter('created_at', ['lteq' => $toDate]);
            }
        }

    }
}