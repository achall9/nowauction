<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

/**
 * Class Changestatus
 * @package Ced\CsMessaging\Controller\Customer
 */
class Changestatus extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Changestatus constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->vcustomerMessageCollFactory = $vcustomerMessageCollFactory;
    }


    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $currentCustomerId = $this->customerSession->getCustomerId();

        $threadId = $this->getRequest()->getParam('thread_id');
        $vcustomerCollection = $this->vcustomerMessageCollFactory->create();
        $vcustomerCollection->addFieldToFilter('thread_id',$threadId)
                            ->addFieldToFilter('receiver_status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);
        if (!empty($vcustomerCollection))
        {
            foreach ($vcustomerCollection as $message)
            {
                if($message->getCustomerId()== $currentCustomerId && $message->getSender()!='customer')
                {
                    $message->setReceiverStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['success'=>true]);
        return $resultJson;
    }
}
