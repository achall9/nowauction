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
 * @package     Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData
 * @package Ced\CsMessaging\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    const ENTITY_TYPE = \Magento\Catalog\Model\Product::ENTITY;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** create product attribute enable_messaging */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.2') < 0
        ) {
            $eavSetup->addAttribute('catalog_product', 'enable_messaging', [
                'group'            => '',
                'note'             => '',
                'input'            => 'boolean',
                'type'             => 'int',
                'label'            => 'Enable Messaging',
                'backend'          => '',
                'visible'          => true,
                'required'         => false,
                'sort_order'       => 105,
                'user_defined'     => 1,
                'source'           => '',
                'comparable'       => 0,
                'visible_on_front' => 0,
                'default'          => 1,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]);
            $entityTypeId = $eavSetup->getEntityTypeId(self::ENTITY_TYPE);
            $defaultId = $eavSetup->getDefaultAttributeSetId(self::ENTITY_TYPE);
            $eavSetup->addAttributeToSet($entityTypeId, $defaultId, 'General', 'enable_messaging');
        }
        $setup->endSetup();

    }

}
