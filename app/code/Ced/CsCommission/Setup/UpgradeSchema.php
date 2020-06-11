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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Ced\CsCommission\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * UpgradeSchema constructor.
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    public function __construct(\Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            /**
             * Create table 'cscommission_commission'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('cscommission_commission')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'cscommission_commission'
            )
            ->addColumn(
                'category',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Category ID'
            )
            ->addColumn(
                'vendor',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Vendor ID'
            )
            ->addColumn(
                'method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                ['nullable' => false],
                'method'
            )
            ->addColumn(
                'fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                [],
                'fee'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 20,
                [],
                'fee'
            )
            ->addColumn(
                'type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Or Website ID'
            )
            ->addColumn(
                'fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                [],
                'fee'
            )
            ->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'priority'
            )
            ->addColumn('created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn('updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->setComment(
                'Ced CsCommission'
            );
            
            $installer->getConnection()->createTable($table);
           
        }   

        /*to append v before vendor id in core config table*/
        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $path = '/ced_vpayments/general/';
            $config = $this->collectionFactory->create()->addFieldToFilter('path', ['like' => '%' . $path . '%']);
            foreach ($config as $key => $value) {
                $data = $value->getData();
                $pathParts = explode('/', $data['path']);
                
                if(strpos($pathParts[0], 'v') === false){
                    $pathParts[0] = 'v'.$pathParts[0];
                    $pathParts = implode('/', $pathParts);
                    $value->setPath($pathParts)->save();       
                } 
            }
        }
        
        $installer->endSetup();
    }
}
