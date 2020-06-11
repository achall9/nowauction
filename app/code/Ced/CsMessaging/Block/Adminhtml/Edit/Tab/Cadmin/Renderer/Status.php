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

namespace Ced\CsMessaging\Block\Adminhtml\Edit\Tab\Cadmin\Renderer;

/**
 * Class Status
 * @package Ced\CsMessaging\Block\Adminhtml\Edit\Tab\Cadmin\Renderer
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Status constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
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
        $messageCollection = $this->collectionFactory->create();
        $messageCollection->addFieldToFilter('thread_id',$threadId)
            ->addFieldToFilter('receiver_id',['eq'=>\Ced\CsMessaging\Helper\Data::ADMIN_ID])
            ->addFieldToFilter('status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);

        return __('new').' ('.$messageCollection->getSize().')';
    }
}

