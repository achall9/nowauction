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

namespace Ced\CsMessaging\Controller\Adminhtml\Vcustomer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\CsMessaging\Helper\Data;

/**
 * Class Save
 * @package Ced\CsMessaging\Controller\Adminhtml\Admin
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $messageManager;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMessaging\Helper\Mail $mailHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_vcustomerFactory = $vcustomerFactory;
        $this->_vcustomerMessageFactory = $vcustomerMessageFactory;
        $this->_localeDate = $localeDate;
        $this->vendorFactory = $vendorFactory;
        $this->customerFactory = $customerFactory;
        $this->mailHelper = $mailHelper;
        $this->urlBuilder = $urlBuilder;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $vCustomer = $this->_vcustomerFactory->create();
        $vCustomerMessage = $this->_vcustomerMessageFactory->create();

        $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
        $postData = $this->getRequest()->getPostValue();
        $currentTime = $this->_localeDate->date()->format('Y-m-d H:i:s');

        if ($threadId = $this->getRequest()->getPost('thread_id'))
        {
            $vCustomer->load($threadId);
            $vCustomer->setUpdatedAt($currentTime);
        }

        $vCustomer->save();
        $vCustomerMessage->setMessage($postData['message'])
            ->setThreadId($threadId)
            ->setCustomerId($postData['customer_id'])
            ->setVendorId($postData['vendor_id'])
            ->setSender(Data::ADMIN_AS_SENDER)
            ->setCreatedAt($currentTime)
            ->setReceiverStatus(Data::STATUS_NEW)
            ->setSendMail(Data::MAIL_SEND);
        $vCustomerMessage->save();

        $notificationData = ['vendor_id'=>$postData['vendor_id'],'reference_id'=>$vCustomer->getId(),'title'=>'New Message from admin','action'=>$this->urlBuilder->getBaseUrl().'csmessaging/vendor/ainbox'];
        $this->csmarketplaceHelper->setNotification($notificationData);


        $vendor = $this->vendorFactory->create();
        $vendor->load($postData['vendor_id']);
        $customer = $this->customerFactory->create();
        $customer->load($postData['customer_id']);

        /** send mail to vendor and customer */
        if(isset($postData['send_mail']))
        {
            $vdata['subject'] = $vCustomer->getSubject();
            $vdata['message'] = $postData['message'];
            $vdata['receiver_name'] = $vendor->getPublicName();
            $vdata['receiver_email'] = $vendor->getEmail();
            $vdata['sender_name'] = $adminName;

            if (!empty($vdata['receiver_email'])) {
            $this->mailHelper->sendMailToVendor($vdata);
            } else {
                $this->messageManager->addErrorMessage(__('Vendor not available'));
            }

            $cdata['subject'] = $vCustomer->getSubject();
            $cdata['message'] = $postData['message'];
            $cdata['receiver_name'] = $customer->getName();
            $cdata['receiver_email'] = $customer->getEmail();
            $cdata['sender_name'] = $adminName;

            if (!empty($cdata['receiver_email'])) {
            $this->mailHelper->sendMailToCustomer($cdata);
            } else {
                $this->messageManager->addErrorMessage(__('Vendor not available'));
        }
        }

        return $this->_redirect('*/*/chat',['id'=>$this->getRequest()->getPost('thread_id')]);
    }
}
