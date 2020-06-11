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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Vendor\Ainbox\Renderer;

/**
 * Class Status
 * @package Ced\CsMessaging\Block\Vendor\Ainbox\Renderer
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Sender constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Ced\CsMarketplace\Model\Session $vendorSession,
        \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->vendorSession = $vendorSession;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $threadId = $row->getId();
        $vendorId = $this->vendorSession->getVendorId();
        $messageCollection = $this->collectionFactory->create();
        $messageCollection->addFieldToFilter('thread_id',$threadId)
            ->addFieldToFilter('receiver_id',$vendorId)
            ->addFieldToFilter('status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);

        return __('new').' ('.$messageCollection->getSize().')';
    }
}

