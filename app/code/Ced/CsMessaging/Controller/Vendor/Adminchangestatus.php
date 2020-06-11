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

namespace Ced\CsMessaging\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Adminchangestatus
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Adminchangestatus extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory
     */
    protected $vadminMessageCollFactory;

    /**
     * Adminchangestatus constructor.
     * @param \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $vadminMessageCollFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $vadminMessageCollFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->vadminMessageCollFactory = $vadminMessageCollFactory;

        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterfac e|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        $threadId = $this->getRequest()->getParam('thread_id');

        $vadminCollection = $this->vadminMessageCollFactory->create();
        $vadminCollection->addFieldToFilter('thread_id', $threadId);

        if (!empty($vadminCollection)) {
            foreach ($vadminCollection as $message) {
                if ($message->getReceiverId() == $this->_getSession()->getVendorId()) {
                    $message->setStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }
        $this->csmarketplaceHelper->readNotification($threadId);

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['succes' => true]);
        return $resultJson;

    }
}
