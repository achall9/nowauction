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

namespace Ced\CsMessaging\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Ced\CsMessaging\Helper\Data;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function __construct(\Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
                                \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory,
                                \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
                                \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory,
                                \Ced\CsMessaging\Model\CadminFactory $cadminFactory,
                                \Ced\CsMessaging\Model\CadminMessageFactory $cadminMessageFactory,
                                \Ced\CsMessaging\Helper\Data $csmessagingHelper,
                                \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
                                \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->vcustomerFactory = $vcustomerFactory;
        $this->vcustomerMessageFactory = $vcustomerMessageFactory;
        $this->vadminFactory = $vadminFactory;
        $this->vadminMessageFactory = $vadminMessageFactory;
        $this->cadminFactory = $cadminFactory;
        $this->cadminMessageFactory = $cadminMessageFactory;
        $this->csmessagingHelper = $csmessagingHelper;
        $this->vendorFactory = $vendorFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.3', '<'))
        {
            /**
             * Create table 'ced_vendor_customer_chat'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_vendor_customer_thread')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subject'
            )->addColumn(
                'vendor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Vendor Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Id'
            )->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender Name'
            )->addColumn(
                'receiver_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Receiver Name'
            )->addColumn(
                'sender',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Updated At'
            )->setComment(
                'Vendor-Customer-Admin Thread Table'
            );
            $installer->getConnection()->createTable($table);



            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_vendor_customer_thread_message')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Message'
            )->addColumn('images',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Chat Images'
            )->addColumn(
                'thread_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'default'=>0],
                'Thread Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Id'
            )->addColumn(
                'vendor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Vendor Id'
            )->addColumn(
                'sender',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'admin_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Admin Status'
            )->addColumn(
                'receiver_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Receiver Status'
            )->addColumn(
                'send_mail',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'default'=>0],
                'Send Mail'
            )->setComment(
                'Vendor-Customer-Admin Message Table'
            );
            $installer->getConnection()->createTable($table);


            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_customer_admin_thread')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'subject'
            )->addColumn(
                'receiver_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Receiver Id'
            )->addColumn(
                'sender_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Sender Id'
            )->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender Name'
            )->addColumn(
                'receiver_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Receiver Name'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Updated At'
            )->setComment(
                'Customer-Admin Thread Table'
            );
            $installer->getConnection()->createTable($table);


            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_customer_admin_thread_message')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Message'
            )->addColumn('images',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Chat Images'
            )->addColumn(
                'thread_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Thread Id'
            )->addColumn(
                'sender_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Sender Id'
            )->addColumn(
                'receiver_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Receiver Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'send_mail',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'default'=>0],
                'Send Mail'
            )->setComment(
                'Customer-Admin Message Table'
            );
            $installer->getConnection()->createTable($table);




            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_vendor_admin_thread')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'subject'
            )->addColumn(
                'receiver_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Receiver Id'
            )->addColumn(
                'sender_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Sender Id'
            )->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender Name'
            )->addColumn(
                'receiver_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Receiver Name'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Updated At'
            )->setComment(
                'Vendor-Admin Thread Table'
            );
            $installer->getConnection()->createTable($table);


            $table = $installer->getConnection()->newTable(
                $installer->getTable('ced_vendor_admin_thread_message')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Message'
            )->addColumn('images',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Chat Images'
            )->addColumn(
                'thread_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Thread Id'
            )->addColumn(
                'sender_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Sender Id'
            )->addColumn(
                'receiver_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Receiver Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'send_mail',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'default'=>0],
                'Send Mail'
            )->setComment(
                'Vendor-Admin Message Table'
            );
            $installer->getConnection()->createTable($table);



            /** migrate old messaging data to new tables */
            if ($installer->getConnection()->isTableExists('ced_csmessaging'))
            {
                $messagingTable = $installer->getConnection()->getTableName('ced_csmessaging');
                $sql = 'Select * from '.$messagingTable;
                $data = $installer->getConnection()->fetchAll($sql);
                $adminEmail = $this->csmessagingHelper->getStoreConfig('trans_email/ident_general/email');
                $adminName = $this->csmessagingHelper->getStoreConfig('trans_email/ident_general/name');
                if (!empty($data))
                {
                    foreach ($data as $message) {
                        if ($message['role'] == 'customer' && $message['receiver_email'] != $adminEmail) {

                            $vendor = $this->vendorFactory->create();
                            $receiver = $vendor->loadByEmail($message['receiver_email']);

                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId(1);
                            $sender = $customer->loadByEmail($message['sender_email']);

                            if ($sender && $receiver)
                                $this->saveVcustomer($message['vendor_id'],$customer->getId(),$sender->getName(),$receiver->getPublicName(),Data::CUSTOMER_AS_SENDER,$message);

                        } elseif ($message['role'] == 'vendor' && ($message['send_to'] == 'customer' || $message['receiver_email']!=$adminEmail))
                        {
                            $vendor = $this->vendorFactory->create();
                            $sender = $vendor->loadByEmail($message['sender_email']);

                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId(1);
                            $receiver = $customer->loadByEmail($message['receiver_email']);

                            if ($sender && $receiver)
                                $this->saveVcustomer($message['vendor_id'],$customer->getId(),$sender->getPublicName(),$receiver->getName(),Data::VENDOR_AS_SENDER,$message);

                        } elseif ($message['role'] == 'customer' && $message['receiver_email'] == $adminEmail)
                        {
                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId(1);
                            $sender = $customer->loadByEmail($message['sender_email']);

                            if ($sender)
                                $this->saveCadmin(Data::ADMIN_ID,$customer->getId(),$sender->getName(),$adminName,Data::CUSTOMER_AS_SENDER,$message);

                        }  elseif ($message['role'] == 'admin' && $message['send_to'] == 'customer')
                        {
                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId(1);
                            $receiver = $customer->loadByEmail($message['receiver_email']);

                            if ($receiver)
                                $this->saveCadmin($customer->getId(),Data::ADMIN_ID,$adminName,$receiver->getName(),Data::ADMIN_AS_SENDER,$message);

                        }  elseif ($message['role'] == 'vendor' && $message['receiver_email'] == $adminEmail)
                        {
                            $vendor = $this->vendorFactory->create();
                            $sender = $vendor->loadByEmail($message['sender_email']);

                            if ($sender)
                                $this->saveVadmin(Data::ADMIN_ID,$message['vendor_id'],$sender->getPublicName(),$adminName,Data::VENDOR_AS_SENDER,$message);

                        } elseif ($message['role'] == 'admin' && $message['send_to'] == 'vendor')
                        {
                            $vendor = $this->vendorFactory->create();
                            $receiver = $vendor->loadByEmail($message['receiver_email']);
                            if ($receiver)
                            {
                                $this->saveVadmin($message['vendor_id'],Data::ADMIN_ID,$adminName,$receiver->getPublicName(),Data::ADMIN_AS_SENDER,$message);
                            }
                        }
                    }
                }
                $installer->getConnection()->dropTable('ced_csmessaging');
            }
        }

        $installer->endSetup();
    }

    public function saveVcustomer($vendorId,$customerId,$senderName,$receiverName,$sender,$message)
    {
        $createdAt =$message['date'].' '.$message['time'];
        $vCustomer = $this->vcustomerFactory->create();
        $vCustomerMessage = $this->vcustomerMessageFactory->create();

        $vCustomer->setSubject($message['subject'])
            ->setVendorId($vendorId)
            ->setCustomerId($customerId)
            ->setSenderName($senderName)
            ->setReceiverName($receiverName)
            ->setSender($sender)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt);
        $vCustomer->save();
        $vCustomerMessage->setMessage($message['message'])
            ->setThreadId($vCustomer->getId())
            ->setCustomerId($customerId)
            ->setVendorId($vendorId)
            ->setSender($sender)
            ->setCreatedAt($createdAt)
            ->setAdminStatus(Data::STATUS_NEW)
            ->setReceiverStatus($message['postread'])
            ->setSendMail(Data::MAIL_SEND);
        $vCustomerMessage->save();
        return true;
    }

    public function saveCadmin($receiverId,$senderId,$senderName,$receiverName,$sender,$message)
    {
        $createdAt =$message['date'].' '.$message['time'];
        $cadmin = $this->cadminFactory->create();
        $cadmin->setSubject($message['subject'])
            ->setReceiverId($receiverId)
            ->setSenderId($senderId)
            ->setSenderName($senderName)
            ->setReceiverName($receiverName)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt);
        $cadmin->save();

        $cadminMessage = $this->cadminMessageFactory->create();
        $cadminMessage->setMessage($message['message'])
            ->setThreadId($cadmin->getId())
            ->setReceiverId($receiverId)
            ->setSenderId($senderId)
            ->setCreatedAt($createdAt)
            ->setStatus($message['postread'])
            ->setSendMail(Data::MAIL_SEND);
        $cadminMessage->save();
        return true;
    }


    public function saveVadmin($receiverId,$senderId,$senderName,$receiverName,$sender,$message)
    {
        $createdAt =$message['date'].' '.$message['time'];
        $vadmin = $this->vadminFactory->create();
        $vadmin->setSubject($message['subject'])
            ->setReceiverId($receiverId)
            ->setSenderId($senderId)
            ->setSenderName($senderName)
            ->setReceiverName($receiverName)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt);
        $vadmin->save();

        $vadminMessage = $this->vadminMessageFactory->create();
        $vadminMessage->setMessage($message['message'])
            ->setThreadId($vadmin->getId())
            ->setReceiverId($receiverId)
            ->setSenderId($senderId)
            ->setCreatedAt($createdAt)
            ->setStatus($message['postread'])
            ->setSendMail(Data::MAIL_SEND);
        $vadminMessage->save();
        return true;
    }
}
