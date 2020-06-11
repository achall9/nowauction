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
 * @package     Ced_Auction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;


class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * table for add auction details
         */

        $setup->startSetup();
        $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_auction_auctionlist')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'primary' => true, 'nullable' => false],
            'Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Product Id'
        )->addColumn(
            'product_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Product Name'
        )->addColumn(
            'starting_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Starting Price'
        )->addColumn(
            'max_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Max Price'
        )->addColumn(
            'min_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Min Qty'
        )->addColumn(
            'max_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Max Qty'
        )->addColumn(
            'product_sold',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            [],
            'Product Sold'
        )->addColumn(
            'start_datetime',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            ['nullable' => true],
            'Start Date'
        )->addColumn(
            'end_datetime',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            [],
            'End Date'
        )->addColumn(
            'visibility',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Extended Time'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Status'
        )->addColumn(
            'sellproduct',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Sell Product'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Vendor Id'
        )->addColumn(
            'temp_startdate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            ['nullable' => true],
            'Temp Start Date'
        )->addColumn(
            'temp_enddate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            [],
            'Temp End Date'
        );
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_auction_biddetails')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'primary' => true, 'nullable' => false],
            'Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Product Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Customer Id'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true],
            'Customer Name'
        )->addColumn(
            'bid_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Bid Price'
        )->addColumn(
            'bid_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            [],
            'Bid Date'
        )->addColumn(
            'bid_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Bid Time'
        )->addColumn(
            'winner',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '10',
            [],
            'Winner'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Status'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Vendor Id'
        );
        $setup->getConnection()->createTable($table);


        $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_auction_winnerlist')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'primary' => true, 'nullable' => false],
            'Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Product Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Customer Id'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true],
            'Customer Name'
        )->addColumn(
            'auction_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Auction Price'
        )->addColumn(
            'bid_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            20,
            [],
            'Bid Date'
        )->addColumn(
            'winning_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Winning Price'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Status'
        )->addColumn(
            'add_to_cart',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Add To Cart'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Vendor Id'
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}