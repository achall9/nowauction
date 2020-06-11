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
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Customer\Ainbox;

/**
 * Class Grid
 * @package Ced\CsMessaging\Block\Customer\Ainbox
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsMessaging\Model\ResourceModel\Cadmin\CollectionFactory
     */
    protected $cadminCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMessaging\Model\ResourceModel\Cadmin\CollectionFactory $cadminCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMessaging\Model\ResourceModel\Cadmin\CollectionFactory $cadminCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->cadminCollectionFactory = $cadminCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customeradmingrid');
        $this->setDefaultSort('Asc');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $cadminCollection = $this->cadminCollectionFactory->create();
        $currentCustomerId = $this->customerSession->getCustomer()->getId();
        $collection = $cadminCollection->addFieldToFilter(['sender_id', 'receiver_id'], [$currentCustomerId, $currentCustomerId]);
        $collection->setOrder('updated_at', 'desc');
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
            'sender_name',
            [
                'header' => __('Sender'),
                'index' => 'sender_name',
            ]
        );
        $this->addColumn(
            'receiver_name',
            [
                'header' => __('Receiver'),
                'index' => 'receiver_name',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'type' => 'datetime',
                'header' => __('Created At'),
                'index' => 'created_at',
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'type' => 'datetime',
                'header' => __('Updated At'),
                'index' => 'updated_at',
            ]
        );

        $this->addColumn(
            'subject',
            [
                'header' => __('Subject'),
                'index' => 'subject',
            ]
        );

        $this->addColumn(
            'receiver_status',
            [
                'header' => __('New Message'),
                'index' => 'receiver_status',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMessaging\Block\Customer\Ainbox\Renderer\Status'
            ]
        );


        $this->addColumn('action',
            [
                'header' => __('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('View'),
                        'url' => array(
                            'base' => '*/*/achat',
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
            ]);

        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {

        return $this->getUrl('*/*/agrid', array('_secure' => true, '_current' => true));
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

}