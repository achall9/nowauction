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

namespace Ced\CsMessaging\Block\Adminhtml\Vendor\Entity\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Tabs
 * @package Ced\CsMessaging\Block\Adminhtml\Vendor\Entity\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $setCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Tabs constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    )
    {
        $this->vendorFactory = $vendorFactory;
        $this->setCollectionFactory = $setCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->_eventManager = $context->getEventManager();

        parent::__construct($context, $jsonEncoder, $authSession, $data);

        $this->setId('vendor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $vendor = $this->vendorFactory->create();
        $entityTypeId = $vendor->getEntityTypeId();
        $setIds = $this->setCollectionFactory->create()->setEntityTypeFilter($entityTypeId)->getAllIds();
        $groupCollection = $this->groupCollectionFactory->create();
        if (count($setIds) > 0) {
            $groupCollection->addFieldToFilter('attribute_set_id', array('in' => $setIds));
        }

        $groupCollection->setSortOrder()->load();

        foreach ($groupCollection as $group) {
            $attributes = $vendor->getAttributes($group->getId(), true);
            if (count($attributes) == 0) {
                continue;
            }
            $this->addTab('group_' . $group->getId(), array(
                'label' => __($group->getAttributeGroupName()),
                'content' => $this->getLayout()->createBlock($this->getAttributeTabBlock(),
                    'csmarketplace.adminhtml.vendor.entity.edit.tab.attributes.' . $group->getId())->setGroup($group)
                    ->setGroupAttributes($attributes)
                    ->toHtml(),
            ));
        }

        if ($vendor_id = $this->getRequest()->getParam('vendor_id', 0)) {
            $this->addTab('payment_details', array(
                'label' => __('Payment Details'),
                'content' => $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Payment\Methods')->toHtml(),
            ));
            $this->addTab('vproducts', array(
                'label' => __('Vendor Products'),
                'title' => __('Vendor Products'),
                'content' => $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vproducts')->toHtml(),
            ));
            $this->addTab('vorders', array(
                'label' => __('Vendor Orders'),
                'title' => __('Vendor Orders'),
                'content' => $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vorders')->toHtml(),
            ));
            $this->addTab('vpayments', array(
                'label' => __('Vendor Transactions'),
                'title' => __('Vendor Transactions'),
                'content' => $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vpayments')->toHtml(),
            ));

            $this->addTab('customer_inbox', array(
                'label' => __('Customer Inbox'),
                'title' => __('Customer Inbox'),
                'content' => $this->getLayout()->createBlock('Ced\CsMessaging\Block\Adminhtml\Vendor\Entity\Edit\Tab\Cinbox')->toHtml(),
            ));

            $this->addTab('admin_inbox', array(
                'label' => __('Admin Inbox'),
                'title' => __('Admin Inbox'),
                'content' => $this->getLayout()->createBlock('Ced\CsMessaging\Block\Adminhtml\Vendor\Entity\Edit\Tab\Ainbox')->toHtml(),
            ));
        }


        /**
         * Dispatch Event for CsAssign to Assign Product Tab
         **/
        $this->_eventManager->dispatch('csmarketplace_adminhtml_vendor_entity_edit_tabs', array('tabs' => $this));

        return parent::_beforeToHtml();
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return '\Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Information';
    }
}