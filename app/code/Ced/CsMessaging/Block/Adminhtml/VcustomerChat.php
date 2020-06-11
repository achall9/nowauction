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
 * Class VcustomerChat
 * @package Ced\CsMessaging\Block\Adminhtml
 */
class VcustomerChat extends \Magento\Backend\Block\Template
{
    /**
     * @var \Ced\CsMessaging\Model\VcustomerFactory
     */
    protected $vcustomerFactory;

    /**
     * @var \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * VcustomerChat constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory
     * @param \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    )
    {
        $this->vcustomerFactory = $vcustomerFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getThreadData($id)
    {
        $vcustomerThread = $this->vcustomerFactory->create();
        $vcustomerThread->load($id);
        return $vcustomerThread;
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
