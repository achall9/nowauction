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
 * Class Achat
 * @package Ced\CsMessaging\Block\Customer
 */
class Achat extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
        \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->cadminFactory = $cadminFactory;
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
        $collection = $collection->addFieldToFilter('thread_id',$threadId);
        return $collection;
    }
}
