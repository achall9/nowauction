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
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProAssign\Controller\Adminhtml\Assign;

use Magento\Backend\App\Action\Context;

/**
 * Class Addproducts
 * @package Ced\CsProAssign\Controller\Adminhtml\Assign
 */
class Addproducts extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_registry = null;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $item;

    /**
     * Addproducts constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Model\Stock\Item $item
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\Stock\Item $item
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_registry = $registry;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->productFactory = $productFactory;
        $this->item = $item;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {

        $enable = $this->_scopeConfig->getValue(
            'ced_csmarketplace/general/csproassignactivation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($enable) {
            $vendor_id = $this->getRequest()->getParam('vendor_id');
            $product_ids = $this->getRequest()->getParam('product_ids');
            try {
                $vproductsModel = $this->vproductsFactory->create();
                $product_ids = explode(',', $product_ids);

                if (count($product_ids)) {
                    /**
                     * Product limit validation
                     **/
                    $vendorGroup = $this->vendorFactory->create()->load($vendor_id)->getGroup();
                    $curProductCount = count($vproductsModel->getVendorProductIds($vendor_id));
                    $afterAdd = $curProductCount + count($product_ids);
                    $vendorLimit = $this->_scopeConfig->getValue($vendorGroup . '/ced_vproducts/general/limit');
                    if (!$vendorLimit) {
                        $vendorLimit = $this->_scopeConfig->getValue('ced_vproducts/general/limit');
                    }
                    $availLimit = $vendorLimit - $curProductCount;
                    $result = '';
                    if ($afterAdd > $vendorLimit) {
                        if ($availLimit) {
                            $result = __('You can assign only') . $availLimit . __('products to vendor');
                        } else {
                            $result = __('Vendors Product limit has Exceeded');
                        }
                        $this->messageManager->addErrorMessage($result);
                        $this->getResponse()->setBody($result);
                        return;
                    }
                    foreach ($product_ids as $product_id) {
                        $allreadyvendorsproduct = $vproductsModel->getVendorProductIds($vendor_id);

                        if (count($allreadyvendorsproduct) != 0) {
                            if (in_array(trim($product_id), $allreadyvendorsproduct)) {
                                continue;
                            }
                        }

                        $product = $this->productFactory->create()->load($product_id);

                        // SIMPLE PRODUTC ASSIGNMENT ----------------------------------------------------------------

                        $websiteIds = array();
                        $websiteIds = '';
                        if ($this->_registry->registry('ced_csmarketplace_current_website') != '') {
                            $websiteIds = $this->_registry->registry('ced_csmarketplace_current_website');
                        } else {
                            $websiteIds = implode(",", $product->getWebsiteIds());
                        }

                        $productId = $product->getId();
                        if ($product->getSpecialPrice()) {
                            $specialPrice = $product->getSpecialPrice();
                        } else {
                            $specialPrice = '0';
                        }
                        $is_in_stock = $this->item->getIsInStock();
                        $quantity = $product->getQty();
                        $type_id = $product->getTypeId();
                        $vproductModel2 = $this->vproductsFactory->create()->addData($product->getData());
                        $vproductModel2->setQty($quantity)
                            ->setIsInStock($is_in_stock)
                            ->setPrice($product->getPrice())
                            ->setSpecialPrice($specialPrice)
                            ->setCheckStatus('1')
                            ->setProductId($product->getId())
                            ->setVendorId($vendor_id)
                            ->setType($type_id)
                            ->setWebsiteId($websiteIds)
                            ->setStatus('1')
                            ->save();
                        //SIMPLE PRODUCT ASSIGNMENT END-------------------------------------------------------------

                        //CONFIGURABLE PRODUCT ASSIGNMENT ---------------------------------------------------
                        if (($product->getTypeId() == 'configurable')) {

                            $config = $product->getTypeInstance(true);
                            $childproduct_config = $config->getUsedProducts($product);

                            foreach ($childproduct_config as $value) {
                                $config_product = $this->productFactory->create()->load(
                                    $value->getId());
                                $websiteIds = array();
                                $websiteIds = '';
                                if ($this->_registry->registry('ced_csmarketplace_current_website') != '') {
                                    $websiteIds = $this->_registry->registry('ced_csmarketplace_current_website');
                                } else {
                                    $websiteIds = implode(",", $config_product->getWebsiteIds());
                                }

                                $productId = $config_product->getId();
                                if ($config_product->getSpecialPrice()) {
                                    $specialPrice = $config_product->getSpecialPrice();
                                } else {
                                    $specialPrice = '0';
                                }
                                $is_in_stock = $this->item->getIsInStock();
                                $quantity = $config_product->getQty();
                                $type_id = $config_product->getTypeId();
                                $vproductModel2 = $this->vproductsFactory->create()->addData($config_product->getData());
                                $vproductModel2->setQty($quantity)
                                    ->setIsInStock($is_in_stock)
                                    ->setPrice($config_product->getPrice())
                                    ->setSpecialPrice($specialPrice)
                                    ->setCheckStatus('1')
                                    ->setProductId($config_product->getId())
                                    ->setVendorId($vendor_id)
                                    ->setType($type_id)
                                    ->setWebsiteId($websiteIds)
                                    ->setStatus('1')
                                    ->save();
                            }

                        }
                        // CONFIGURABLE ASSIGNMENT DONE ------------------------------------------

                        // GROUPED ASSIGNMENT DONE -----------------------------------------------
                        if (($product->getTypeId() == 'grouped')) {

                            $config = $product->getTypeInstance(true);
                            $childproduct_grouped = $config->getUsedProducts($product);
                            foreach ($childproduct_grouped as $value) {
                                $group_product = $this->productFactory->create()->load(
                                    $value->getId());
                                $websiteIds = array();
                                $websiteIds = '';
                                if ($this->_registry->registry('ced_csmarketplace_current_website') != '') {
                                    $websiteIds = $this->_registry->registry('ced_csmarketplace_current_website');
                                } else {
                                    $websiteIds = implode(",", $group_product->getWebsiteIds());
                                }

                                $productId = $group_product->getId();
                                if ($group_product->getSpecialPrice()) {
                                    $specialPrice = $group_product->getSpecialPrice();
                                } else {
                                    $specialPrice = '0';
                                }
                                $is_in_stock = $this->item->getIsInStock();
                                $quantity = $group_product->getQty();
                                $type_id = $group_product->getTypeId();
                                $vproductModel2 = $this->vproductsFactory->create()->addData($group_product->getData());
                                $vproductModel2->setQty($quantity)
                                    ->setIsInStock($is_in_stock)
                                    ->setPrice($group_product->getPrice())
                                    ->setSpecialPrice($specialPrice)
                                    ->setCheckStatus('1')
                                    ->setProductId($group_product->getId())
                                    ->setVendorId($vendor_id)
                                    ->setType($type_id)
                                    ->setWebsiteId($websiteIds)
                                    ->setStatus('1')
                                    ->save();
                            }

                        }
                        // GROUPED ASSIGNMENT END--------------------------------
                        $result = 'success';
                    }
                    $this->messageManager->addSuccessMessage(__('Product(s) Assigned Successfully.'));
                    $this->getResponse()->setBody($result);
                } else {
                    die("fv");
                    $result = 'noproduct';
                    $this->messageManager->addErrorMessage(__('Please select product to assign.'));
                    $this->getResponse()->setBody($result);
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__($result));
                $this->getResponse()->setBody($result);
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage(__($result));
                $this->getResponse()->setBody($result);
            }
        } else {
            $this->messageManager->addErrorMessage(__('You can not assign products.'));
            $this->getResponse()->setBody($result);
        }

    }
}