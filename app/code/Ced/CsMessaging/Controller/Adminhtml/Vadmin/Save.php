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

namespace Ced\CsMessaging\Controller\Adminhtml\Vadmin;

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
        \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
        \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMessaging\Helper\Mail $mailHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->vadminFactory = $vadminFactory;
        $this->vadminMessageFactory = $vadminMessageFactory;
        $this->vendorFactory = $vendorFactory;
        $this->_localeDate = $localeDate;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->mailHelper = $mailHelper;
        $this->urlBuilder = $urlBuilder;
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
            $vadmin = $this->vadminFactory->create();
            $vadmin->load($postData['thread_id']);
            $vAdminMessage = $this->vadminMessageFactory->create();
            $vendor = $this->vendorFactory->create();
            $vendor->load($postData['receiver_id']);
            $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
            $vadmin->setUpdatedAt($currentTime);
            $vadmin->save();
            $vAdminMessage->setMessage($postData['message'])
                ->setThreadId($vadmin->getId())
                ->setSenderId(Data::ADMIN_ID)
                ->setReceiverId($postData['receiver_id'])
                ->setCreatedAt($currentTime)
                ->setStatus(Data::STATUS_NEW)
                ->setSendMail(Data::MAIL_SEND);
            $vAdminMessage->save();



            $notificationData = ['vendor_id'=>$postData['receiver_id'],'reference_id'=>$vadmin->getId(),'title'=>'New Message from admin','action'=>$this->urlBuilder->getBaseUrl().'csmessaging/vendor/ainbox'];
            $this->csmarketplaceHelper->setNotification($notificationData);

            /** send mail to vendor */
            if(isset($postData['send_mail']))
            {
                $data['subject'] = $vadmin->getSubject();
                $data['message'] = $postData['message'];
                $data['receiver_name'] = $vendor->getPublicName();
                $data['receiver_email'] = $vendor->getEmail();
                $data['sender_name'] = $adminName;
                $this->mailHelper->sendMailToVendor($data);
            }


            return $this->_redirect('*/*/chat',['id'=>$postData['thread_id']]);

        } else {
            $receiverId = $this->getRequest()->getPost('receiver_id');
            $receiverId = explode(',',$receiverId);
            if (!empty($receiverId)) {
                foreach ($receiverId as $id) {

                    $vadmin = $this->vadminFactory->create();
                    $vAdminMessage = $this->vadminMessageFactory->create();
                    $vendor = $this->vendorFactory->create();
                    $vendor->load($id);
                    $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');

                    $notificationData = ['vendor_id'=>$id,'reference_id'=>$vadmin->getId(),'title'=>'New Message from admin','action'=>$this->urlBuilder->getBaseUrl().'csmessaging/vendor/ainbox'];
                    $this->csmarketplaceHelper->setNotification($notificationData);



                    $vadmin->setSubject($postData['subject'])
                        ->setSenderId(Data::ADMIN_ID)
                        ->setReceiverId($id)
                        ->setSenderName($adminName)
                        ->setReceiverName($vendor->getPublicName())
                        ->setCreatedAt($currentTime)
                        ->setUpdatedAt($currentTime);
                    $vadmin->save();
                    $vAdminMessage->setMessage($postData['message'])
                        ->setThreadId($vadmin->getId())
                        ->setSenderId(Data::ADMIN_ID)
                        ->setReceiverId($id)
                        ->setCreatedAt($currentTime)
                        ->setStatus(Data::STATUS_NEW);
                    $vAdminMessage->save();

                    /** send mail to vendor */
                    if(isset($postData['send_mail']))
                    {
                        $data['subject'] = $vadmin->getSubject();
                        $data['message'] = $postData['message'];
                        $data['receiver_name'] = $vendor->getPublicName();
                        $data['receiver_email'] = $vendor->getEmail();
                        $data['sender_name'] = $adminName;
                        $this->mailHelper->sendMailToVendor($data);
                    }
                }
                return $this->_redirect('*/*/vadmin');
            }
        }
    }


}
