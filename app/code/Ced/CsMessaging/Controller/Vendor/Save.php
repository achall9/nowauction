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
use Ced\CsMessaging\Helper\Data;

/**
 * Class Save
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Save constructor.
     * @param \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory
     * @param \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMessaging\Model\VadminFactory $vadminFactory
     * @param \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory
     * @param \Ced\CsMessaging\Helper\Mail $mailHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Data $helper
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
        \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
        \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory,
        \Ced\CsMessaging\Helper\Mail $mailHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Ced\CsMessaging\Helper\Data $helper,
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
        $this->_vcustomerMessageFactory = $vcustomerMessageFactory;
        $this->_vcustomerFactory = $vcustomerFactory;
        $this->_localeDate = $localeDate;
        $this->_customerFactory = $customerFactory;
        $this->vadminFactory = $vadminFactory;
        $this->vadminMessageFactory = $vadminMessageFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->mailHelper = $mailHelper;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;

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
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $csubject = '';
        $asubject = '';
        $amessage = '';
        $admin = false;

        $postData = $this->getRequest()->getPostValue();
        $image_files = $this->getRequest()->getFiles('chat_images');
        $currentTime = $this->_localeDate->date()->format('Y-m-d H:i:s');
        $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
        $adminEmail = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/email');


        if (isset($postData['send_to_admin'])) {
            $vadmin = $this->vadminFactory->create();
            $vAdminMessage = $this->vadminMessageFactory->create();

            if ($threadId = $this->getRequest()->getPost('thread_id')) {
                $chatId = $threadId;
                $vadmin->load($threadId);
                $asubject = $vadmin->getSubject();
            } else {
                $vadmin->setSubject($postData['subject'])
                    ->setSenderId($this->_getSession()->getVendorId())
                    ->setReceiverId(Data::ADMIN_ID)
                    ->setSenderName($this->_getSession()->getVendor()['public_name'])
                    ->setReceiverName($adminName)
                    ->setCreatedAt($currentTime);
                $asubject = $postData['subject'];
            }

            $vadmin->setUpdatedAt($currentTime);
            $vadmin->save();
            $vAdminMessage->setMessage($postData['message'])
                ->setThreadId($vadmin->getId())
                ->setSenderId($this->_getSession()->getVendorId())
                ->setReceiverId(Data::ADMIN_ID)
                ->setCreatedAt($currentTime)
                ->setStatus(Data::STATUS_NEW)
                ->setSendMail(Data::MAIL_SEND);

            /** save message images */
            if (!empty($image_files)) {
                $result_image = $this->helper->saveImages($vadmin->getId(), $image_files);
                if (isset($result_image['error'])) {
                    $this->messageManager->addErrorMessage(__('There has been an error while uploading documents.'));
                }
                if (isset($result_image['filename']) && !empty($result_image['filename'])) {
                    $encodedResult = $this->jsonHelper->jsonEncode($result_image);
                    $vAdminMessage->setImages($encodedResult);
                }
            }


            $vAdminMessage->save();

            $amessage = $postData['message'];
            $admin = true;


        } else {


            $vCustomer = $this->_vcustomerFactory->create();
            $vCustomerMessage = $this->_vcustomerMessageFactory->create();
            $customer = $this->_customerFactory->create();
            $customer->load($postData['receiver_id']);

            if ($threadId = $this->getRequest()->getPost('thread_id')) {
                $chatId = $threadId;
                $vCustomer->load($threadId);
                $csubject = $asubject = $vCustomer->getSubject();
            } else {
                $vCustomer->setSubject($postData['subject'])
                    ->setVendorId($this->_getSession()->getVendorId())
                    ->setCustomerId($postData['receiver_id'])
                    ->setSenderName($this->_getSession()->getVendor()['public_name'])
                    ->setReceiverName($customer->getName())
                    ->setSender(Data::VENDOR_AS_SENDER)
                    ->setCreatedAt($currentTime);
                $csubject = $asubject = $postData['subject'];
            }
            $vCustomer->setUpdatedAt($currentTime);
            $vCustomer->save();
            $vCustomerMessage->setMessage($postData['message'])
                ->setThreadId($vCustomer->getId())
                ->setCustomerId($postData['receiver_id'])
                ->setVendorId($this->_getSession()->getVendorId())
                ->setSender(Data::VENDOR_AS_SENDER)
                ->setCreatedAt($currentTime)
                ->setAdminStatus(Data::STATUS_NEW)
                ->setReceiverStatus(Data::STATUS_NEW)
                ->setSendMail(Data::MAIL_SEND);

            /** save message images */
            if (!empty($image_files)) {
                $result_image = $this->helper->saveImages($vCustomer->getId(), $image_files);
                if (isset($result_image['error'])) {
                    $this->messageManager->addErrorMessage(__('There has been an error while uploading documents.'));
                }
                if (isset($result_image['filename']) && !empty($result_image['filename'])) {
                    $encodedResult = $this->jsonHelper->jsonEncode($result_image);
                    $vCustomerMessage->setImages($encodedResult);
                }
            }

            $vCustomerMessage->save();

            $amessage = $postData['message'];

            /** send mail to customer */
            if (isset($postData['send_mail'])) {
                $data['subject'] = $csubject;
                $data['message'] = $postData['message'];
                $data['receiver_name'] = $customer->getName();
                $data['receiver_email'] = $customer->getEmail();
                $data['sender_name'] = $this->_getSession()->getVendor()['public_name'];
                $this->mailHelper->sendMailToCustomer($data);
            }

            $admin = false;

        }

        /** send mail to admin */
        if (isset($postData['send_mail'])) {
            $data['subject'] = $asubject;
            $data['message'] = $amessage;
            $data['receiver_name'] = $adminName;
            $data['receiver_email'] = $adminEmail;
            $data['sender_name'] = $this->_getSession()->getVendor()['public_name'];
            $this->mailHelper->sendMailToAdmin($data);
        }

        if ($admin) {
            if (isset($chatId)) {
                return $this->_redirect('*/*/achat', ['id' => $chatId]);
            } else {
                return $this->_redirect('*/*/ainbox');
            }
        } else {
            if (isset($chatId)) {
                return $this->_redirect('*/*/chat', ['id' => $chatId]);
            } else {
                return $this->_redirect('*/*/cinbox');
            }
        }
    }
}
