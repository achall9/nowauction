<?php

namespace Ced\CustomizeAuction\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
    * {@inheritdoc}
    */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'shipping_detail',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Shipping Details',
                'after' => 'vendor_id'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'payment_detail',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Payment Details',
                'after' => 'shipping_detail'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'total_bid_price',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'total bid price',
                'after' => 'payment_detail'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'is_preauction',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Is Preauction',
                'after' => 'total_bid_price'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'shipping_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Shipping Amount',
                'after' => 'is_preauction'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable( 'ced_auction_biddetails' ),
            'payment_token',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Payment Token',
                'after' => 'shipping_amount'
            ]
        );

        $conn = $installer->getConnection();
        $tableName = $installer->getTable('vendor_shippingmethods');
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                'id',
                                Table::TYPE_INTEGER,
                                null,
                     ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                'vendor_id',
                                Table::TYPE_TEXT,
                                255,
                                ['nullable'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'shipping_method',
                                Table::TYPE_TEXT,
                                '2555',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }

        $installer->endSetup();
    }
}
