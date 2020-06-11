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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiSeller\Controller\Product;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Duplicate
 * @package Ced\CsMultiSeller\Controller\Product
 */
class Duplicate extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsMultiSeller\Helper\Data
     */
    protected $csmultisellerHelper;

    /**
     * @var \Ced\CsMultiSeller\Model\MultisellFactory
     */
    protected $multisellFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * Duplicate constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper
     * @param \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper,
        \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->productCopier = $productCopier;
        $this->storeManager = $storeManager;
        $this->csmultisellerHelper = $csmultisellerHelper;
        $this->multisellFactory = $multisellFactory;
        $this->productFactory = $productFactory;
        $this->storeFactory = $storeFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->stockRegistry = $stockRegistry;
        $this->productCollectionFactory = $productCollectionFactory;

        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     *  \Ced\CsMarketplace\Controller\Vendor::dispatch()
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {

        if ($this->registry->registry('ced_csmarketplace_current_store'))
            $this->registry->unRegister('ced_csmarketplace_current_store');

        if ($this->registry->registry('ced_csmarketplace_current_website'))
            $this->registry->unRegister('ced_csmarketplace_current_website');

        $this->registry->register('ced_csmarketplace_current_store', $this->storeManager->getStore()->getId());
        $this->registry->register('ced_csmarketplace_current_website', $this->storeManager->getStore()->getWebsiteId());
        return parent::dispatch($request);

    }

    /**
     * Create product duplicate
     */
    public function execute()
    {
        $vproducts = $this->multisellFactory->create()->getVendorProductIds();
        $productCount = count($this->vproductsFactory->create()->getVendorProductIds($this->_getSession()->getVendorId())) + count($vproducts);

        if ($productCount >= $this->csmarketplaceHelper->getVendorProductLimit()) {
            $this->messageManager->addErrorMessage($this->__('Product Creation limit has Exceeded'));
            $this->_redirect('*/*/index', array('store' => $this->getRequest()->getParam('store', 0)));
            return;
        }

        if (!$this->_getSession()->getVendorId())
            return;

        $this->mode = \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE;

        if (!$this->csmultisellerHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor/index');
            return;
        }

        if ($this->getRequest()->getParam('id', '') == '') {
            $this->_redirect('csmultiseller/product/new');
            return;
        }

        $customData = $this->getRequest()->getPost();
        $productErrors = array();
        $vproductModel = $this->multisellFactory->create();
        $vproductModel->addData(isset($customData['product']) ? $customData['product'] : '');
        $vproductModel->addData(isset($customData['product']['stock_data']) ? $customData['product']['stock_data'] : '');
        $productErrors = $vproductModel->validate();

        if (!empty($productErrors)) {
            foreach ($productErrors as $message) {
                $this->messageManager->addErrorMessage($message);
            }

            $this->_redirect('*/*/assign', array('id' => $this->getRequest()->getParam('id'), 'store' => $this->getRequest()->getParam('store', 0)));
            return;
        }

        $product = $this->_initProduct();

        $customData = $this->getRequest()->getPost();

        try {
            if (count($customData) > 0) {
                $currentStore = $this->storeManager->getStore()->getId();
                $this->storeManager->setCurrentStore($currentStore);
                $product->setUrlKey($customData['product']['sku']);
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                $newProduct = $this->productCopier->copy($product);
                $newProduct->setStoreId($product->getStoreId());

                $this->multisellFactory->create()->setStoreId($product->getStoreId())->saveProduct($this->mode, $newProduct, $product);
                $this->setCurrentStore();

                $productStock = $this->stockRegistry->getStockItem($newProduct->getId());

                $productStock->setData('is_in_stock', $customData['product']['stock_data']['is_in_stock']);
                $productStock->setData('qty', $customData['product']['stock_data']['qty']); //set updated quantity
                $productStock->setData('manage_stock', 1);
                $productStock->setData('use_config_notify_stock_qty', 1);
                $newProduct->save();
                $productStock->save();

                $this->messageManager->addSuccessMessage(__('The product has been saved.'));
            } else {
                $this->messageManager->addErrorMessage(__('Unable to save Product.'));
            }

            $this->_redirect('*/*/index', array('store' => $product->getStoreId()));
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            $this->_redirect('*/*/index', array('store' => $product->getStoreId()));
        }
    }

    /**
     * Initialize product from request parameters
     *
     * @return Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        $productId = (int)$this->getRequest()->getParam('id');
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

        $product = $this->productFactory->create();

        if (!$productId) {
            $product->setStoreId(0);
            if ($setId = (int)$this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            $storeId = 0;
            if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE && $this->getRequest()->getParam('store')) {
                $websiteId = $this->storeFactory->create()->load($this->getRequest()->getParam('store'))->getWebsiteId();

                if ($websiteId) {
                    if (in_array($websiteId, $this->vproductsFactory->create()->getAllowedWebsiteIds())) {
                        $storeId = $this->getRequest()->getParam('store');
                    }
                }
            }

            try {
                $product->setStoreId($storeId)->load($productId);
            } catch (Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
            }

        }

        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                explode(",", base64_decode(urldecode($attributes)))
            );
        }

        // Required attributes of simple product for configurable creation

        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
            && $this->getRequest()->getParam('product')
            && !is_array($this->getRequest()->getParam('product'))
            && $this->getRequest()->getParam('id', false) === false) {
            $configProduct = $this->productCollectionFactory->create()
                ->setStoreId(0)
                ->load($this->getRequest()->getParam('product'))
                ->setTypeId($this->getRequest()->getParam('type'));

            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes() as $attribute) {

                if (!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType() != 'gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $configProduct->getIdFieldName()) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }
            $product->addData($data)
                ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        $this->registry->register('product', $product);
        $this->registry->register('current_product', $product);
        return $product;
    }

    /**
     * Set current store
     */
    public function setCurrentStore()
    {
        if ($this->registry->registry('ced_csmarketplace_current_store')) {
            $currentStoreId = $this->registry->registry('ced_csmarketplace_current_store');
            $this->storeManager->setCurrentStore($currentStoreId);
        }
    }
}
    
