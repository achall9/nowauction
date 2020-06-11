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
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData
 * @package Ced\CsMarketplace\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Ced\CsMarketplace\Model\Vendor\FormFactory
     */
    public $formFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * UpgradeData constructor.
     * @param \Ced\CsMarketplace\Model\Vendor\FormFactory $formFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vendor\FormFactory $formFactory,
        EavSetupFactory $eavSetupFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
        $this->formFactory = $formFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.0') < 0
        ) {
            $vendorAttribute = $this->formFactory->create()->getCollection()
                ->addFieldToFilter('attribute_code', 'zip_code')
                ->getFirstItem();
            $vendorAttribute->load($vendorAttribute->getAttributeId())->delete();

            $eavSetup->removeAttribute('csmarketplace_vendor', 'zip_code');

            $eavSetup->addAttribute(
                'csmarketplace_vendor',
                'zip_code',
                array(
                    'group' => 'Address Information',
                    'label' => 'Zip/Postal Code',
                    'type' => 'static',
                    'visible' => true,
                    'position' => 27,
                    'user_defined' => false,
                    'required' => true,

                )
            );

            $attribute = $eavSetup->getAttribute('csmarketplace_vendor',
                'zip_code');
            $vendorAttribute = $this->formFactory->create();

            $data = [
                'attribute_id' => $attribute['attribute_id'],
                'attribute_code' => 'zip_code',
                'is_visible' => 1,
                'sort_order' => 27,
            ];
            $vendorAttribute->setData($data)->save();
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $eavSetup->addAttribute(
                'csmarketplace_vendor', 'reason', array(
                    'group' => 'General Information',
                    'visible' => true,
                    'position' => 4,
                    'type' => 'varchar',
                    'label' => 'Disapproval Reason',
                    'input' => 'textarea',
                    'required' => false,
                    'user_defined' => false,

                )
            );
        }

        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.3') < 0
        ) {

            $ourStoryBlock = $this->blockFactory->create();
            $ourStoryBlock->load('ced-csmarketplace-out-story', 'identifier');
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            if (!$ourStoryBlock->getId()) {
                $ourStory = [
                    'title' => 'Our Story',
                    'identifier' => 'ced-csmarketplace-out-story',
                    'content' => '<div class="container">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <div class="story_image"><img class="img-fluid" src="{{media url=ced/csmarketplace/login_landing_page/story_sec.svg}}" alt="CoolBrand"></div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <div class="story_content">
                                                <h3 class="story_heading">Tell Your Story</h3>
                                                <div class="sub_heading"><strong> We are working to your business Goal </strong></div>
                                                <p class="str_para">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi ducimus expedita facilis architecto fugiat veniam natus suscipit amet beatae atque, enim recusandae quos, magnam, perferendis accusamus cumque nemo modi unde!</p>
                                                <p class="str_para">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi ducimus expedita facilis architecto fugiat veniam natus suscipit amet beatae atque, enim recusandae quos, magnam, perferendis accusamus cumque nemo modi unde!</p>
                                                <div class="button-set"><button class="btn btn-primary">Read more</button></div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>',
                    'stores' => 0,
                    'is_active' => 1,
                ];
                $this->blockFactory->create()->setData($ourStory)->save();
            }

            $stepsToRegisterBlock = $this->blockFactory->create();
            $stepsToRegisterBlock->load('ced-csmarketplace-steps-to-register', 'identifier');
            if (!$stepsToRegisterBlock->getId()) {
                $stepsToRegister = [
                    'title' => 'Steps to Register',
                    'identifier' => 'ced-csmarketplace-steps-to-register',
                    'content' => '<div class="container">
                                    <div class="how_get_row">
                                        <div class="how_get_main_wrapper">
                                            <div class="how_get">
                                                <h3 class="ger_ready_h">How to get ready for selling?</h3>
                                                <p class="content">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laboriosam consectetur excepturi consequuntur nemo dolor fuga commodi</p>
                                                <div class="steps_for_get_ready">
                                                    <div id="get_ready" class="carousel slide" data-ride="carousel">
                                                        <div class="carousel-inner">
                                                            <div class="item active">
                                                                <div class="get_ready_steps">
                                                                    <h4>Register</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>List your product</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Ship your product</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Earn money</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Register now</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <a class="btn btn-primary" href="{{store direct_url="csmarketplace/account/register"}}">Register now</a></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ol class="carousel-indicators align_carousel_items">
                                                            <li class="active" data-target="#get_ready" data-slide-to="0">Register</li>
                                                            <li data-target="#get_ready" data-slide-to="1">List Product</li>
                                                            <li data-target="#get_ready" data-slide-to="2">Ship product</li>
                                                            <li data-target="#get_ready" data-slide-to="3">Get earning</li>
                                                            <li data-target="#get_ready" data-slide-to="4">Let\'s go</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="how_get_image">
                                            <div class="table">
                                                <div class="table-cell"><img class="img-fluid" src="{{media url=ced/csmarketplace/login_landing_page/get_ready.png}}" alt="CoolBrand"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                </div>',
                    'stores' => 0,
                    'is_active' => 1,
                ];
                $this->blockFactory->create()->setData($stepsToRegister)->save();
            }


            $featuresBlock = $this->blockFactory->create();
            $featuresBlock->load('ced-csmarketplace-features', 'identifier');
            if (!$featuresBlock->getId()) {
                $features = [
                    'title' => 'Features',
                    'identifier' => 'ced-csmarketplace-features',
                    'content' => '<div class="container">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="section_title">
                                                <h3>Why you sell in our marketplace?</h3>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero optio fugiat dignissimos incidunt</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/dashboard_new.svg}}" alt="">Dashboard</h3>
                                                    <p class="para_all">It will have the block of vendor order history, account related statistics details and summary of sales</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/create_new.svg}}" alt=""> Create Product</h3>
                                                    <p class="para_all">Vendor can create Simple products, manage Qty, Price, create Configurable products with variations and images.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/order_new.svg}}" alt="">Order Management</h3>
                                                    <p class="para_all">Vendor can manage order gird, It can print packing slip and create shipment• It can also cancel order as well.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/report_new.svg}}" alt=""> Reports</h3>
                                                    <p class="para_all">Vendor can review different notifications for new orders, can review reports for product sell, commission, total orders.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/customerpanel_new.svg}}" alt=""> Customer Panel</h3>
                                                    <p class="para_all">Customers will be able to view all the products from all the vendors, can post reviews on all products.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{media url=ced/csmarketplace/login_landing_page/vendor_manage_new.svg}}" alt=""> Vendor Management</h3>
                                                    <p class="para_all">Admin manages all the vendor accounts and also able to review all the statistics, can edit and approve vendors.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>',
                    'stores' => 0,
                    'is_active' => 1,
                ];
                $this->blockFactory->create()->setData($features)->save();
            }
            //phpcs:enable

        }

        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.4') < 0
        ) {
            $salesOrderTable = $setup->getTable('sales_order');
            $vendorOrderTable = $setup->getTable('ced_csmarketplace_vendor_sales_order');
            if ($setup->getConnection()->isTableExists($vendorOrderTable) &&
                $setup->getConnection()->isTableExists($salesOrderTable)) {
                $query = "UPDATE {$vendorOrderTable} vo 
                            left join {$salesOrderTable} so on so.increment_id = vo.order_id
                            set vo.real_order_id = so.entity_id, vo.real_order_status = so.status";

                $setup->getConnection()->query($query);
            }
        }

        $setup->endSetup();
    }
}
