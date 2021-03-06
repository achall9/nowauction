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

namespace Ced\CsMessaging\Block\Vendor;

/**
 * Class Achat
 * @package Ced\CsMessaging\Block\Vendor
 */
class Achat extends \Magento\Framework\View\Element\Template
{
    /**
     * Cchat constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ced\CsMarketplace\Model\Session $vendorSession
     * @param \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory
     * @param \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsMarketplace\Model\Session $vendorSession,
        \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
        \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        parent::__construct($context);
        $this->vendorSession = $vendorSession;
        $this->vadminFactory = $vadminFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return int|null
     */
    public function getCurrentVendorId()
    {
        return $this->vendorSession->getVendorId();
    }

    /**
     * @return \Ced\CsMessaging\Model\Vcustomer
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
        $collection = $collection->addFieldToFilter('thread_id',$threadId);
        return $collection;
    }


}
