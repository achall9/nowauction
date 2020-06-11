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
 * Class CadminChat
 * @package Ced\CsMessaging\Block\Adminhtml
 */
class CadminChat extends \Magento\Backend\Block\Template
{
    /**
     * @var \Ced\CsMessaging\Model\CadminFactory
     */
    protected $cadminFactory;

    /**
     * @var \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * CadminChat constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\CsMessaging\Model\CadminFactory $cadminFactory
     * @param \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
        \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    )
    {
        $this->cadminFactory = $cadminFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getThreadData($id)
    {
        $thread = $this->cadminFactory->create();
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
    public function getCustomerById($vId)
    {
        $customer = $this->customerFactory->create();
        return $customer->load($vId);
    }
}
