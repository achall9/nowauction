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


use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use \Magento\Catalog\Api\CategoryRepositoryInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * General Attribute Group Name
     * @var string
     */
    private $_generalGroupName = 'General';
    private $eavSetupFactory;
    private $attributeSetFactory;
    private $attributeSet;
    private $categorySetupFactory;
    /**
     * Setup model
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * UpgradeData constructor.
     * @param ModuleDataSetupInterface $setup
     * @param \Magento\Framework\App\State $appState
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        \Magento\Framework\App\State $appState,
        EavSetupFactory $eavSetupFactory, AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->setup = $setup;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetId = $categorySetup->getAttributeSetId($entityTypeId, 'default');

        /*
         * Start : Adding and updating attributes
         * */

        $this->newGroup($setup, $categorySetup, $entityTypeId, $attributeSetId);
        $this->newAttributes($setup, $context, $categorySetup, $entityTypeId, $attributeSetId);

        /**   End : Adding and updating attributes
         * */

    }

    public function newGroup($setup, $categorySetup, $entityTypeId, $attributeSetId)
    {
        /**** Attribute-group Names ,sort-order****/
        $autosettingsTabName = [
            ['Gift Card Details', 10]
        ];

        foreach ($autosettingsTabName as $key => $value) {
            $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, $value[0], $value[1]);
        }

    }

    public function newAttributes($setup, $context, $categorySetup, $entityTypeId, $attributeSetId)
    {
        /**** Attribute names and there data*****/
        /* [atr-Code, atr-group, atr-type, label, input, required,default] */

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributes = [
            ['name' => 'gift_expiry_days',
                'type' => 'text',
                'label' => 'Gift Expiry Days',
                'input' => 'text',
                'required' => true,
                'default' => 30,
                'frontend_class' => 'validate-number validate-length minimum-length-1',
                'sort_order' => 10],
            ['name' => 'gift_min_amount',
                'type' => 'decimal',
                'label' => 'Gift Minimum Price',
                'input' => 'price',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => true,
                'default' => 100,
                'sort_order' => 20],
            ['name' => 'gift_max_amount',
                'type' => 'decimal',
                'label' => 'Gift Maximum Price',
                'input' => 'price',
                'required' => true,
                'default' => 300,
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'sort_order' => 30],
            ['name' => 'gift_price_slabs',
                'type' => 'text',
                'label' => 'Gift Price Slabs',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'source' => 'Ced\GiftCard\Model\Product\Price\Slabs',
                'input' => 'multiselect',
                'required' => true,
                'sort_order' => 40],
            ['name' => 'gift_template',
                'type' => 'int',
                'label' => 'Gift Template',
                'input' => 'select',
                'source' => 'Ced\GiftCard\Model\GiftTemplate',
                'user_defined' => true,
                'required' => true,
                'sort_order' => 50],
            ['name' => 'is_gift_card_allowed',
                'type' => 'int',
                'label' => 'Gift Card Checkout Allowed',
                'input' => 'boolean',
                'user_defined' => true,
                'required' => false,
                'group' => 'Product Details',
                'default' => 1,
                'apply_to' => 'simple,virtual,downloadable,bundle,configurable',
                'sort_order' => 50]
        ];

        foreach ($attributes as $key => $value) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $value['name'],   /* Custom Attribute Code */
                [
                    'group' => (isset($value['group'])) ? $value['group'] : 'Gift Card Details',/* Group name in which you want to display your custom attribute */
                    'type' => $value['type'],/* Data type in which formate your value save in database*/
                    'backend' => (isset($value['backend'])) ? $value['backend'] : '',
                    'frontend' => '',
                    'label' => $value['label'], /* lablel of your attribute*/
                    'input' => $value['input'],
                    'frontend_class' => ((isset($value['frontend_class'])) ? $value['frontend_class'] : ''),
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => (isset($value['visible'])) ? $value['visible'] : true,
                    'required' => $value['required'],
                    'source' => (isset($value['source'])) ? $value['source'] : '',
                    'option' => (isset($value['option'])) ? $value['option'] : '',
                    'user_defined' => (isset($value['user_defined'])) ? $value['user_defined'] : false,
                    'sort_order' => 50,
                    'default' => (isset($value['default'])) ? $value['default'] : '',
                    'note' => (isset($value['note'])) ? $value['note'] : '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => (isset($value['apply_to'])) ? $value['apply_to'] : 'giftcard',
                ]
            );
        }

        if (!$context->getVersion()) {
            /*Add gift product category*/

            $data = [
                'data' => [
                    "parent_ids" => array(array()),
                    "parent" => 2,
                    'name' => 'Gift',
                    "is_active" => true,
                    "position" => 1,
                    "include_in_menu" => true,
                ],
                'custom_attributes' => [
                    "parent_ids" => array(array()),
                    "parent" => 2,
                    "display_mode" => "PRODUCTS",
                    "is_anchor" => "1"
                ]
            ];
            try {

                $category = $this->categoryFactory->create($data)
                    ->setCustomAttributes($data['custom_attributes']);

                $result = $this->categoryRepository->save($category);
            } catch (\Exception $e) {

            }
        }
    }

}
