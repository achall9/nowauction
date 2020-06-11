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

namespace Ced\CsMessaging\Block\Adminhtml;

/**
 * Class VadminChat
 * @package Ced\CsMessaging\Block\Adminhtml
 */
class VadminChat extends \Magento\Backend\Block\Template
{
    /**
     * @var \Ced\CsMessaging\Model\VadminFactory
     */
    protected $vadminFactory;

    /**
     * @var \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * VadminChat constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\CsMessaging\Model\VadminFactory $vadminFactory
     * @param \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $collectionFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
        \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    )
    {
        $this->vadminFactory = $vadminFactory;
        $this->collectionFactory = $collectionFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getThreadData($id)
    {
        $thread = $this->vadminFactory->create();
        $thread->load($id);
        return $thread;
    }


    /**
     * @param $threadId
     * @return mixed
     */
    public function getChatCollection($threadId)
    {
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('thread_id', $threadId);
        return $collection;
    }

    /**
     * @param $vId
     * @return mixed
     */
    public function getVendorById($vId)
    {
        $vendor = $this->vendorFactory->create();
        return $vendor->load($vId);
    }
}
