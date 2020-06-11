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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Cron;

use Ced\CsMessaging\Helper\Data;

/**
 * Class SendMail
 * @package Ced\CsMessaging\Cron
 */
class SendMail {

    public function __construct(\Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $vadminMessageCollectionFactory,
                                \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $cadminMessageCollectionFactory,
                                \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
                                \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
                                \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
                                \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
                                \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->vadminMessageCollectionFactory = $vadminMessageCollectionFactory;
        $this->cadminMessageCollectionFactory = $cadminMessageCollectionFactory;
        $this->vadminFactory = $vadminFactory;
        $this->vendorFactory = $vendorFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->cadminFactory = $cadminFactory;
        $this->customerFactory = $customerFactory;
    }

    /*
     * running cron job to send mails to the customers
     */
    public function execute() {


        /** send mail to vendor */
        $vadminMessageCollection = $this->vadminMessageCollectionFactory->create();
        $vadminMessageCollection->addFieldToFilter('sender_id',Data::ADMIN_ID);
        $vadminMessageCollection->addFieldToFilter('send_mail',Data::SEND_MAIL_CHECKED);
        $vadminMessageCollection->getSelect()->limit(10);
        $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');

        if (!empty($vadminMessageCollection))
        {
            foreach ($vadminMessageCollection as $vadminMessage) {

                $vadmin = $this->vadminFactory->create();
                $vadmin->load($vadminMessage->getThreadId());

                $vendor = $this->vendorFactory->create();
                $vendor->load($vadminMessage->getReceiverId());

                $data['subject'] = $vadmin->getSubject();
                $data['message'] = $vadminMessage->getMessage();
                $data['receiver_name'] = $vendor->getPublicName();
                $data['receiver_email'] = $vendor->getEmail();
                $data['sender_name'] = $adminName;
                $this->mailHelper->sendMailToVendor($data);

                $vadminMessage->setSendMail(Data::MAIL_SEND);
                $vadminMessage->save();
            }
        }


        /** send mail to customer */
        $cadminMessageCollection = $this->cadminMessageCollectionFactory->create();
        $cadminMessageCollection->addFieldToFilter('sender_id',Data::ADMIN_ID);
        $cadminMessageCollection->addFieldToFilter('send_mail',Data::SEND_MAIL_CHECKED);
        $cadminMessageCollection->getSelect()->limit(10);
        $adminName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');

        if (!empty($cadminMessageCollection))
        {
            foreach ($cadminMessageCollection as $cadminMessage) {

                $cadmin = $this->cadminFactory->create();
                $cadmin->load($cadminMessage->getThreadId());

                $customer = $this->customerFactory->create();
                $customer->load($cadminMessage->getReceiverId());

                $data['subject'] = $cadmin->getSubject();
                $data['message'] = $cadminMessage->getMessage();
                $data['receiver_name'] = $customer->getName();
                $data['receiver_email'] = $customer->getEmail();
                $data['sender_name'] = $adminName;
                $this->mailHelper->sendMailToCustomer($data);

                $cadminMessage->setSendMail(Data::MAIL_SEND);
                $cadminMessage->save();
            }
        }
        return $this;
    }
}