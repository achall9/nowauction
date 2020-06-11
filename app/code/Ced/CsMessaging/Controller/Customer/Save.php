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
 * @category  Ced
 * @package   Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Ced\CsMessaging\Helper\Data;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        UrlInterface $urlBuilder,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
        \Ced\CsMessaging\Model\CadminMessageFactory $cadminMessageFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMessaging\Helper\Mail $mailHelper,
        \Ced\CsMessaging\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_moduleManager = $moduleManager;
        $this->urlBuilder = $urlBuilder;
        $this->vcustomerFactory = $vcustomerFactory;
        $this->vcustomerMessageFactory = $vcustomerMessageFactory;
        $this->_localeDate = $localeDate;
        $this->vendorFactory = $vendorFactory;
        $this->cadminFactory = $cadminFactory;
        $this->cadminMessageFactory = $cadminMessageFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->mailHelper = $mailHelper;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($customer = $this->session->isLoggedIn()) {

            $asubject = '';
            $csubject = '';
            $admin = false;

            $postData = $this->getRequest()->getPostValue();
            $image_files = $this->getRequest()->getFiles('chat_images');
            $currentTime = $this->_localeDate->date()->format('Y-m-d H:i:s');
            $customerId = $this->session->getCustomerId();
            $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
            $adminEmail = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/email');

            if (isset($postData['send_to_admin']))
            {
                $cadmin = $this->cadminFactory->create();
                if (isset($postData['thread_id']))
                {
                    $chatId = $postData['thread_id'];
                    $cadmin->load($postData['thread_id']);
                    $asubject = $cadmin->getSubject();
                } else {
                    $cadmin->setSubject($postData['subject'])
                        ->setReceiverId(Data::ADMIN_ID)
                        ->setSenderId($customerId)
                        ->setSenderName($this->session->getCustomer()->getName())
                        ->setReceiverName($adminName)
                        ->setCreatedAt($currentTime);
                    $asubject = $postData['subject'];

                }
                $cadmin->setUpdatedAt($currentTime);
                $cadmin->save();

                $cadminMessage = $this->cadminMessageFactory->create();
                $cadminMessage->setMessage($postData['message'])
                    ->setThreadId($cadmin->getId())
                    ->setReceiverId(Data::ADMIN_ID)
                    ->setSenderId($customerId)
                    ->setCreatedAt($currentTime)
                    ->setStatus(Data::STATUS_NEW)
                    ->setSendMail(Data::MAIL_SEND);

                /** save message images */
                if( !empty($image_files) ){
                    $result_image = $this->helper->saveImages($cadmin->getId(), $image_files);
                    if (isset($result_image['error']))
                    {
                        $this->messageManager->addErrorMessage(__('There has been an error while uploading documents.'));
                    }
                    if(isset($result_image['filename']) && !empty($result_image['filename']) ){
                        $encodedResult = $this->jsonHelper->jsonEncode($result_image);
                        $cadminMessage->setImages($encodedResult);
                    }
                }
                $cadminMessage->save();

                $amessage = $postData['message'];
                $admin = true;

            } else {

                $vCustomer = $this->vcustomerFactory->create();
                $vCustomerMessage = $this->vcustomerMessageFactory->create();

                $vendor = $this->vendorFactory->create();
                $vendor->load($postData['receiver_id']);

                if ($threadId = $this->getRequest()->getPost('thread_id')) {
                    $chatId = $threadId;
                    $vCustomer->load($threadId);
                    $csubject = $asubject = $vCustomer->getSubject();
                } else {
                    $vCustomer->setSubject($postData['subject'])
                        ->setVendorId($postData['receiver_id'])
                        ->setCustomerId($customerId)
                        ->setSenderName($this->session->getCustomer()->getName())
                        ->setReceiverName($vendor->getPublicName())
                        ->setSender(Data::CUSTOMER_AS_SENDER)
                        ->setCreatedAt($currentTime);
                    $csubject = $asubject = $postData['subject'];
                }
                $vCustomer->setUpdatedAt($currentTime);
                $vCustomer->save();
                $vCustomerMessage->setMessage($postData['message'])
                    ->setThreadId($vCustomer->getId())
                    ->setCustomerId($customerId)
                    ->setVendorId($postData['receiver_id'])
                    ->setSender(Data::CUSTOMER_AS_SENDER)
                    ->setCreatedAt($currentTime)
                    ->setAdminStatus(Data::STATUS_NEW)
                    ->setReceiverStatus(Data::STATUS_NEW)
                    ->setSendMail(Data::MAIL_SEND);


                /** save message images */
                if( !empty($image_files) ){
                    $result_image = $this->helper->saveImages($vCustomer->getId(), $image_files);

                    if (isset($result_image['error']))
                    {
                        $this->messageManager->addErrorMessage(__('There has been an error while uploading documents.'));
                    }

                    if(isset($result_image['filename']) && !empty($result_image['filename']) ){
                        $encodedResult = $this->jsonHelper->jsonEncode($result_image);
                        $vCustomerMessage->setImages($encodedResult);
                    }
                }


                $vCustomerMessage->save();

                $notificationData = ['vendor_id'=>$postData['receiver_id'],'reference_id'=>$vCustomer->getId(),'title'=>'New Message from customer/admin','action'=>$this->urlBuilder->getUrl('csmessaging/vendor/cinbox')];
                $this->csmarketplaceHelper->setNotification($notificationData);


                $amessage = $postData['message'];

                /** send mail to vendor */
                if(isset($postData['send_mail']))
                {
                    $data['subject'] = $csubject;
                    $data['message'] = $postData['message'];
                    $data['receiver_name'] = $vendor->getPublicName();
                    $data['receiver_email'] = $vendor->getEmail();
                    $data['sender_name'] =$this->session->getCustomer()->getName();
                    $this->mailHelper->sendMailToVendor($data);
                }

                $admin = false;
            }

            /** send mail to admin */
            if(isset($postData['send_mail']))
            {
                $data['subject'] = $asubject;
                $data['message'] = $amessage;
                $data['receiver_name'] = $adminName;
                $data['receiver_email'] = $adminEmail;
                $data['sender_name'] = $this->session->getCustomer()->getName();
                $this->mailHelper->sendMailToAdmin($data);
            }

            if ($admin){
                if (isset($chatId)){
                    return $this->_redirect('*/*/achat',['id'=>$chatId]);
                } else {
                    return $this->_redirect('*/*/ainbox');
                }
            }
            else{
                if (isset($chatId)){
                    return $this->_redirect('*/*/chat',['id'=>$chatId]);
                } else {
                    return $this->_redirect('*/*/vinbox');
                }
            }

        } else {
            $value = $this->session->setBeforeAuthUrl($this->urlBuilder->getUrl() . 'csmessaging/customer/vcompose?id=' . $this->getRequest()->getParam('id'));
            $this->_redirect('customer/account/login');
        }
    }
}
