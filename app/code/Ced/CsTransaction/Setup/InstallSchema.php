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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_cstransaction_vorder_items')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Parent ID'
        )->addColumn(
            'order_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Order Item Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            ['nullable' => false],
            'Order Id'
        )->addColumn(
            'order_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            ['nullable' => false],
            'Order Increment Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn(
            'qty_ready_to_pay',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Quantity Ready To Pay'
        )->addColumn(
            'qty_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Quantity Paid'
        )->addColumn(
            'total_invoiced_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Total Invoiced Amount'
        )->addColumn(
            'total_creditmemo_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Total CreditMemo Amount'
        )->addColumn(
            'amount_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Amount Paid'
        )->addColumn(
            'qty_ready_to_refund',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quantity Ready To Refund'
        )->addColumn(
            'qty_pending_to_refund',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quantity Pending To Refund'
        )->addColumn(
            'qty_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Quantity Refunded'
        )->addColumn(
            'amount_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Amount Refunded'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '10',
            ['nullable' => false],
            'Currency'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'SKU'
        )->addColumn(
            'base_row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Base Row Total'
        )->addColumn(
            'row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Row Total'
        )->addColumn(
            'item_fee',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Item Fee'
        )->addColumn(
            'item_commission',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Item Commission'
        )->addColumn(
            'shop_commission_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'Shop Commission Type'
        )->addColumn(
            'shop_commission_rate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Shop Commission Rate '
        )->addColumn(
            'shop_commission_base_fee',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Shop Commission Base Fee'
        )->addColumn(
            'shop_commission_fee',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['nullable' => false],
            'Shop Commission Fee'
        )->addColumn(
            'product_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Qty'
        )->addColumn(
            'item_payment_state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '11',
            ['nullable' => false],
            'Item Payment State'
        )->addColumn(
            'billing_country_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Billing Country Code'
        )->addColumn(
            'shipping_country_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Shipping Country Code'
        );
        $installer->getConnection()->createTable($table);


        if ($installer->getConnection()->isTableExists('ced_csmarketplace_vendor_payments')== true) {
            
            $columns = [
                            'item_wise_amount_desc' => [
                                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                'size' => '1000',
                                'nullable' => true,
                                'comment' => 'Item Wise Amount Desc'
                            ],
                            'total_shipping_amount' => [
                                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                                'nullable' => true,
                                'size' => '10,4',
                                'comment' => 'Total Shipping Amount'
                            ]
                        
                        ];
                        
                        foreach ($columns as $name => $value) {
                            $installer->getConnection()->addColumn('ced_csmarketplace_vendor_payments', $name, $value);
                        }


            /*$installer->getConnection()->addColumn(
                'item_wise_amount_desc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '500',
                ['nullable' => true],
                'Item Wise Amount Desc'
            )->addColumn(
                'total_shipping_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,4',
                ['nullable' => true, 'default' => 0],
                'Total Shipping Amount'
            );*/
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_payments_requested')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            ['nullable' => false],
            'Order Id'
        )->addColumn(
            'amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false,  'default' => 0],
            'Amount'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
