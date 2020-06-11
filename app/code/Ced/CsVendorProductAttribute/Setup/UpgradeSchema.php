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
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('catalog_eav_attribute');
        //$tableName_vendor=$installer->getTable('ced_csmarketplace_vendor');
        if (version_compare($context->getVersion(), '0.0.1') < 0) {
            // Changes here.
        }
        if (version_compare($context->getVersion(), '0.0.2', '<')) {

            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection
                    ->addColumn(
                        $tableName,
                        'attribute_set_ids',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => false,
                            'comment' => 'Attribute Set Ids'
                        ]
                    );
            }
        }

        $cedtableName = $installer->getTable('ced_vendor_product_attributes');
        if ($installer->getConnection()->isTableExists($cedtableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $cedtableName,
                    'sort_order',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => false,
                        'comment' => 'Sort Order'
                    ]
                );
        }


        $installer->endSetup();
    }
}

