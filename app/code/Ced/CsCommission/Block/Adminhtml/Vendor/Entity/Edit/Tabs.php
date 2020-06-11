<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category  Ced
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_vendor = $vendor;
        $this->_setColFactory = $setColFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->setId('vendor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        try {
            $entityTypeId = $this->_vendor->getEntityTypeId();
            $setIds = $this->_setColFactory->create()->setEntityTypeFilter($entityTypeId)
                ->getAllIds();
            $groupCollection = $this->groupCollectionFactory->create();
            if (!empty($setIds)) {
                $groupCollection->addFieldToFilter('attribute_set_id', ['in' => $setIds]);
            }

            $groupCollection->setSortOrder()->load();

            foreach ($groupCollection as $group) {
                $attributes = $this->_vendor->getAttributes($group->getId(), true);
                $attributesCount = count($attributes);
                if ($attributesCount == 0) {
                    continue;
                }

                $this->addTab(
                    'group_' . $group->getId(),
                    [
                        'label' => __($group->getAttributeGroupName()),
                        'content' => $this->getLayout()->createBlock(
                            $this->getAttributeTabBlock(),
                            'csmarketplace.adminhtml.vendor.entity.edit.tab.attributes.' . $group->getId()
                        )->setGroup($group
                            ->setGroupAttributes($attributes)
                            ->toHtml())
                    ]
                );
            }

            if ($vendor_id = $this->getRequest()->getParam('vendor_id', 0)) {
                $this->addTab(
                    'payment_details',
                    [
                        'label' => __('Payment Details'),
                        'content' => $this->getLayout()->createBlock(
                            'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Payment\Methods'
                        )->toHtml()
                    ]
                );

                $this->addTab(
                    'vproducts',
                    [
                        'label' => __('Vendor Products'),
                        'title' => __('Vendor Products'),
                        'content' => $this->getLayout()->createBlock(
                            'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vproducts'
                        )->toHtml(),
                    ]
                );
                $this->addTab(
                    'vorders',
                    [
                        'label' => __('Vendor Orders'),
                        'title' => __('Vendor Orders'),
                        'content' => $this->getLayout()->createBlock(
                            'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vorders'
                        )->toHtml()
                    ]
                );
                $this->addTab(
                    'vpayments',
                    [
                        'label' => __('Vendor Transactions'),
                        'title' => __('Vendor Transactions'),
                        'content' => $this->getLayout()->createBlock(
                            'Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vpayments'
                        )->toHtml()
                    ]
                );
                $this->addTab(
                    'commission',
                    [
                        'label' => __('Commission Configurations'),
                        'title' => __('Commission Configurations'),
                        'content' => $this->getLayout()->createBlock(
                            'Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit\Tab\Configurations'
                        )->toHtml()
                    ]
                );
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

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
