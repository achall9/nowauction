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

namespace Ced\CsMessaging\Block\Customer;

/**
 * Class Vchat
 * @package Ced\CsMessaging\Block\Customer
 */
class Vchat extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->vcustomerFactory = $vcustomerFactory;
        $this->collectionFactory = $collectionFactory;
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * @return int|null
     */
    public function getCurrentCustomerId()
    {
        return $this->customerSession->getId();
    }

    /**
     * @return \Ced\CsMessaging\Model\Vcustomer
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
        $collection = $collection->addFieldToFilter('thread_id',$threadId);
        return $collection;
    }

    /**
     * @param $vId
     * @return mixed
     */
    public function getVendorById($vId)
    {
        $vendor= $this->vendorFactory->create();
        return $vendor->load($vId);
    }
}
