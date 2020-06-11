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
 * @package     Ced_CsTransaction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $itemtableName = $installer->getTable('ced_cstransaction_vorder_items');

        if ($installer->getConnection()->isTableExists($itemtableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $itemtableName,
                    'is_requested',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '',
                        'nullable' => false,
                        'comment' => 'is requested'
                    ]
                );
        }
        if ($installer->getConnection()->isTableExists($itemtableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $itemtableName,
                    'amount_ready_to_refund',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => false,
                        'comment' => 'Amount Ready To Refund'
                    ]
                );
        }
        if ($installer->getConnection()->isTableExists($itemtableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $itemtableName,
                    'qty_ordered',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '',
                        'nullable' => false,
                        'comment' => 'Qty Ordered'
                    ]
                );
        }
        if ($installer->getConnection()->isTableExists($itemtableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $itemtableName,
                    'qty_for_pay_now',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '',
                        'nullable' => false,
                        'comment' => 'Qty For Pay Now'
                    ]
                );
        }
        $installer->endSetup();
    }
}
