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
  * @category  Ced
  * @package   Ced_CsVendorAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsVendorAttribute\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
     
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection = $setup->getConnection();

            $columns = [
                        'use_in_invoice' => [
                                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                            'length' => 1,
                                            'nullable' => true,
                                            'comment' => 'Use Attribute in Invoice'
                                        ]
                        
                    ];
                    $connection = $setup->getConnection();
                    $table = $setup->getTable('ced_csmarketplace_vendor_form_attribute');
                    foreach ($columns as $key => $value) {
                        $connection->addColumn($table, $key, $value);
                    }
        }
   
        $installer->endSetup();
    }
}
