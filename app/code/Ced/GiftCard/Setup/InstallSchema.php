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
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
/*
 * Creating required Tables
 * */
class InstallSchema implements InstallSchemaInterface
{
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
             
        $installer->getConnection()->addColumn(
            $installer->getTable('quote_item'),
            'ced_giftcarddata',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Gift card data in serialized form',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('quote_item'),
            'ced_giftcoupon_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'save gift coupon id of table ced_giftcoupon',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_item'),
            'ced_giftcarddata',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Gift card data in serialized form',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_item'),
            'ced_giftcoupon_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'save gift coupon id of table ced_giftcoupon',
            ]
        ); 
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_item'),
            'ced_gift_to_mail',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'ced_gift card recipient mail id',
            ]
        ); 

        /*start : save coupon code and amount is order and quote table*/
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'cs_giftcoupon_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'save gift coupon code',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'cs_giftcoupon_amount',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'save gift coupon amount',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'cs_giftcoupon_applied',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'is gift coupon code applied',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'cs_giftcoupon_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'save gift coupon code',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'cs_giftcoupon_applied',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'is gift coupon code applied',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'cs_giftcoupon_amount',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => true,
                'comment' => 'save gift coupon amount',
            ]
        );
        /*end : save coupon code and amount is order and quote table*/


        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_giftcoupon')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift ID'
        )->addColumn(
            'template_id',
            Table::TYPE_INTEGER,
            25,
            ['nullable' => false],
            'Template id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Product ID'
        )->addColumn(
            'increment_id',
            Table::TYPE_TEXT,
            32,
            ['nullable' => true],
            'Order Increment ID'
        )->addColumn(
            'coupon_price',
            Table::TYPE_FLOAT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'initial price of coupon'
        )->addColumn(
            'current_amount',
            Table::TYPE_FLOAT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'current avaliable amount of coupon code'
        )->addColumn(
            'time_used',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'total number of time gift card is used'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'coupon code'
        )->addColumn(
            'expiration_date',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => true, 'nullable' => true],
            'coupon expiration_date'
        )->addColumn(
            'delivery_date',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => true, 'nullable' => true],
            'coupon delivery_date'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => true, 'nullable' => true],
            'coupon creation date'
        )->addForeignKey(
            $installer->getFkName(
                $installer->getTable('sales_order'),
                'increment_id',
                $installer->getTable('ced_giftcoupon'),
                'increment_id'
            ),
            'increment_id',
            $installer->getTable('sales_order'),
            'increment_id',
            Table::ACTION_SET_DEFAULT
        )->setComment(
            'used to store coupon codes created by per order'
        );
        $installer->getConnection()->createTable($table);


        $installer2 = $setup;
        $installer2->startSetup();
        $table2 = $installer2->getConnection()->newTable(
            $installer->getTable('ced_gifttemplate')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            25,
            ['nullable' => false],
            'Template Name'
        )->addColumn(
            'code_template',
            Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            "code template example prefix_{N}{S}{N}{S}"
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'unsigned' => true],
            "description"
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            2,
            ['nullable' => true, 'unsigned' => true, 'default'=>1],
            "is_active or not"
        )->addColumn(
            'images',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'unsigned' => true],
            "images"
        )->setComment(
            'to store gift card templates'
        );

        $installer2->getConnection()->createTable($table2);

        $installer2->endSetup();
 
        $installer3 = $setup;
        $installer3->startSetup();
        $table3 = $installer3->getConnection()->newTable(
            $installer->getTable('ced_gift_message')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift ID'
        )->addColumn(
            'sales_order_item_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'sales order item id'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            "email id of sender"
        )->addColumn(
            'recipient_email',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            "email id of recipient"
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            500,
            ['nullable' => true],
            "gift message for recipient"
        )->setComment(
            'to store gift card message details of customers'
        );


        $installer3->getConnection()->createTable($table3);

        $installer3->endSetup();

        $installer->endSetup();

    }
}
