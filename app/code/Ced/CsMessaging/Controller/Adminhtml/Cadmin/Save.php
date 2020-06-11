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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Adminhtml\Cadmin;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\CsMessaging\Helper\Data;

/**
 * Class Save
 * @package Ced\CsMessaging\Controller\Adminhtml\Vadmin
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
        \Ced\CsMessaging\Model\CadminMessageFactory $cadminMessageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMessaging\Helper\Mail $mailHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cadminFactory = $cadminFactory;
        $this->cadminMessageFactory = $cadminMessageFactory;
        $this->customerFactory = $customerFactory;
        $this->_localeDate = $localeDate;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $currentTime = $this->_localeDate->date()->format('Y-m-d H:i:s');
        if (isset($postData['thread_id']))
        {
            $cadmin = $this->cadminFactory->create();
            $cadmin->load($postData['thread_id']);

            $customer = $this->customerFactory->create();
            $customer->load($postData['receiver_id']);


            $cAdminMessage = $this->cadminMessageFactory->create();
            $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
            $cadmin->setUpdatedAt($currentTime);
            $cadmin->save();
            $cAdminMessage->setMessage($postData['message'])
                ->setThreadId($cadmin->getId())
                ->setSenderId(Data::ADMIN_ID)
                ->setReceiverId($postData['receiver_id'])
                ->setCreatedAt($currentTime)
                ->setStatus(Data::STATUS_NEW)
                ->setSendMail(Data::MAIL_SEND);
            $cAdminMessage->save();

            /** send mail to customer */
            if(isset($postData['send_mail']))
            {
                $data['subject'] = $cadmin->getSubject();
                $data['message'] = $postData['message'];
                $data['receiver_name'] = $customer->getName();
                $data['receiver_email'] = $customer->getEmail();
                $data['sender_name'] = $adminName;
                $this->mailHelper->sendMailToCustomer($data);
            }

            return $this->_redirect('*/*/chat',['id'=>$postData['thread_id']]);

        } else {
            $receiverId = $this->getRequest()->getPost('receiver_id');
            $receiverId = explode(',',$receiverId);
            if (!empty($receiverId)) {
                foreach ($receiverId as $id) {
                    $cadmin = $this->cadminFactory->create();
                    $cAdminMessage = $this->cadminMessageFactory->create();
                    $customer = $this->customerFactory->create();
                    $customer->load($id);
                    $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
                    $cadmin->setSubject($postData['subject'])
                        ->setSenderId(Data::ADMIN_ID)
                        ->setReceiverId($id)
                        ->setSenderName($adminName)
                        ->setReceiverName($customer->getName())
                        ->setCreatedAt($currentTime)
                        ->setUpdatedAt($currentTime);
                    $cadmin->save();
                    $cAdminMessage->setMessage($postData['message'])
                        ->setThreadId($cadmin->getId())
                        ->setSenderId(Data::ADMIN_ID)
                        ->setReceiverId($id)
                        ->setCreatedAt($currentTime)
                        ->setStatus(Data::STATUS_NEW);
                    $cAdminMessage->save();

                    /** send mail to customer */
                    if(isset($postData['send_mail']))
                    {
                        $data['subject'] = $cadmin->getSubject();
                        $data['message'] = $postData['message'];
                        $data['receiver_name'] = $customer->getName();
                        $data['receiver_email'] = $customer->getEmail();
                        $data['sender_name'] = $adminName;
                        $this->mailHelper->sendMailToCustomer($data);
                    }
                }
            }
            return $this->_redirect('*/*/cadmin');
        }
        
    }
}
