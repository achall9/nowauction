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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
     
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $ced_giftcoupon = $setup->getTable('ced_giftcoupon');
        $quote = $setup->getTable('quote');
        $sales_order = $setup->getTable('sales_order');

        $quoteAddressTable = $setup->getTable('quote_address');
        $invoiceTable = $setup->getTable('sales_invoice');
        $creditmemoTable = $setup->getTable('sales_creditmemo');

        if (true) {
            if ($setup->getConnection()->isTableExists($ced_giftcoupon) == true) {
                $connection = $setup->getConnection();
                $data = [
                    [
                        'table' => $ced_giftcoupon,
                        'oldColumnName' => 'coupon_price',
                        'newColumnName'=> 'coupon_price',
                        'defination'=> ['type' => Table::TYPE_FLOAT]
                    ],
                    [
                        'table' => $ced_giftcoupon,
                        'oldColumnName' => 'current_amount',
                        'newColumnName'=> 'current_amount',
                        'defination'=>[
                            'type' => Table::TYPE_DECIMAL,
                            'nullable' => true,
                            'length' => '12,4',
                            'default' => 0.00,
                        ]
                    ],
                    [
                        'table' => $quote,
                        'oldColumnName' => 'cs_giftcoupon_amount',
                        'newColumnName'=> 'cs_giftcoupon_amount',
                        'defination'=>['type' => Table::TYPE_FLOAT]
                    ],
                    [
                        'table' => $sales_order,
                        'oldColumnName' => 'cs_giftcoupon_amount',
                        'newColumnName'=> 'cs_giftcoupon_amount',
                        'defination'=>['type' => Table::TYPE_FLOAT]
                    ]
                ];
                foreach ($data as $key => $value) {
                    $connection->changeColumn(
                                $value['table'],
                                $value['oldColumnName'],
                                $value['newColumnName'],
                                $value['defination']
                            );

                }
            }
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteAddressTable),
                    'cs_giftcoupon_amount',
                    [
                        'type' => Table::TYPE_FLOAT,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Cs_giftcoupon_amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($invoiceTable),
                    'cs_giftcoupon_amount',
                    [
                        'type' => Table::TYPE_FLOAT,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Cs_giftcoupon_amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($creditmemoTable),
                    'cs_giftcoupon_amount',
                    [
                        'type' => Table::TYPE_FLOAT,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Cs_giftcoupon_amount'
                    ]
                );

        }
        $setup->endSetup(); 
    }
}
