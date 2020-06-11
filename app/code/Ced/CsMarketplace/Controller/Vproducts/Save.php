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

namespace Ced\CsMarketplace\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlFactory;

class Save extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->type = $type;
        $this->registry = $registry;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor, $storeManager, $productFactory, $vproductsFactory, $type);
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->_getSession()->getVendorId()) {
            return;
        }

        if (!$this->getRequest()->getParam('id')) {
            if (count($this->vproductsFactory->create()->getVendorProductIds($this->_getSession()->getVendorId())) >= $this->csmarketplaceHelper->getVendorProductLimit()) {
                $this->messageManager->addErrorMessage(__('Product Creation limit has Exceeded'));
                return $this->_redirect('*/*/index/store/' . $this->getRequest()->getParam('store_switcher', 0));
            }
        }
        
        $product = [];
        $data = $this->getRequest()->getPost();

        if ($data) {
            $product = $this->_initProductSave();
            if ($product == \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE) {
                if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE) {
                    return $this->_redirect('*/*/edit/id/' . $this->getRequest()->getParam('id') . '/type/' . $this->getRequest()->getParam('type') . '/store/' . $this->getRequest()->getParam('store_switcher', 0));
                } else if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
                    $this->_getSession()->setFormError(true)->setProductFormData($data);
                    return $this->_redirect('*/*/new/type/' . $this->getRequest()->getParam('type'));
                } else {
                    return $this->_redirect('*/*/index/store/' . $product->getStoreId());
                }
            }
           
            try {
                $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                
                $product = $product->save();
                /**
                 * dispatch event when new product is created
                 */
                if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
                    $this->_eventManager->dispatch('csmarketplace_vendor_new_product_creation', [
                        'product' => $product,
                        'vendor_id' => $this->_getSession()->getVendorId(),
                    ]);
                }
                
                $this->messageManager->addSuccessMessage(__('The product has been saved.'));
                
                $this->vproductsFactory->create()->setStoreId($product->getStoreId())->setProductData($product)->saveProduct($this->mode);
                
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                $redirectBack = true;
            }
        }
         
        $storeId = 0;
        if ($product) {
            $storeId = $product->getStoreId();
        }
        
        if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE) {
            $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $this->getRequest()->getParam('id'), 'type' => $this->getRequest()->getParam('type'), 'store' => $this->getRequest()->getParam('store_switcher', 0)]
            );

        } else if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
            $this->_redirect('*/*/new/type/' . $this->getRequest()->getParam('type'));
            $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $product->getEntityId(), 'type' => $this->getRequest()->getParam('type')]
            );
        } else {
            $resultRedirect->setPath('*/*/index', ['store' => $storeId]);
        }
        return $resultRedirect;
    }

    /**
     * Initialize product from request parameters
     *
     * @return Magento\Catalog\Model\Product|const ERROR_IN_PRODUCT_SAVE
     */
    protected function _initProduct()
    {
        $productData = $this->getRequest()->getPost();
        $productId = $this->getRequest()->getParam('id');

        if ($productId) {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE;
        } else {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE;
        }

        $productData['entity_id'] = $productId;
        $errors = array();
        try {
            $this->storeManager->setCurrentStore(0);

            $product = $this->productFactory->create();
            if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE) {

                $product->setStoreId((int)$this->getRequest()->getParam('store_switcher', 0));
                $vendorId = $this->_getSession()->getVendorId();
                if ($productId && $vendorId) {
                    $vendorProduct = $this->vproductsFactory->create()->isAssociatedProduct($vendorId, $productId);
                    if (!$vendorProduct) {
                        return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
                    }
                }

                $product->load($productId);

            } else if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
                $product->setStoreId(0);
                $allowedType = $this->type->getAllowedType($this->storeManager->getStore()->getId());
                $type = $this->getRequest()->getParam('type');
                if (!(in_array($type, $allowedType))) {
                    return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
                }
            }
            $product->addData(isset($productData['product']) ? $productData['product'] : '');
            $product->validate();
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
        }

        $vproductModel = $this->vproductsFactory->create();
        $vproductModel->addData(isset($productData['product']) ? $productData['product'] : '');
        $vproductModel->addData(isset($productData['product']['stock_data']) ? $productData['product']['stock_data'] : '');
        $productErrors = $vproductModel->validate();

        if (is_array($productErrors)) {
            $errors = array_merge($errors, $productErrors);
        }

        if (!empty($errors)) {
            foreach ($errors as $message) {
                $this->messageManager->addErrorMessage($message);
            }
            return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
        }
        return $product;

    }

    /**
     * Initialize product saving
     *
     * @return Magento\Catalog\Model\Product|const ERROR_IN_PRODUCT_SAVE
     */
    protected function _initProductSave()
    {
        $product = $this->_initProduct();
        if ($product == \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE) {

            return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
        }
        $productData = $this->getRequest()->getPost('product');
        $productId = (int)$this->getRequest()->getParam('id');

        if ($productData) {
            $stock_data = isset($productData['stock_data']) ? $productData['stock_data'] : '';
            $this->_filterStockData($stock_data);
            $productData['stock_data'] = $stock_data;
            $productData['quantity_and_stock_status'] = $stock_data;
        }

        $product->addData($productData);
        /**
         * Initialize product categories
         */
        $categoryIds = $this->getRequest()->getPost('category_ids');
        $store = $this->storeManager->getStore(null);

        if (null !== $categoryIds) {
            if (empty($categoryIds)) {
                $categoryIds = '';
            }

            $cats = explode(',', $categoryIds);
            if (!in_array($store->getRootCategoryId(), $cats)) {
                $cats[] = $store->getRootCategoryId();

            }
            $cats = array_unique($cats);
            $cats = array_filter($cats);
            $category_array = array();

            foreach ($cats as $value) {
                if (strlen($value)) {
                    $category_array [] = trim($value);
                }
            }

            $product->setCategoryIds($category_array);
        } else {

            $category_array = array($store->getRootCategoryId());
            $product->setCategoryIds($category_array);
        }

        if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
            $setId = (int)$this->getRequest()->getParam('set') ? (int)$this->getRequest()->getParam('set') : $this->productFactory->create()->getDefaultAttributeSetId();;
            $product->setAttributeSetId($setId);

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
            $product->setStatus($this->vproductsFactory->create()->isProductApprovalRequired() ? \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED : \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            if ($this->csmarketplaceHelper->isSharingEnabled()) {
                $websiteIds = isset($productData['website_ids']) ? $productData['website_ids'] : array();
            } else {
                $websiteIds = array($this->registry->registry('ced_csmarketplace_current_website'));
            }

            $product->setWebsiteIds(array(1));

        }


        if ($this->storeManager->isSingleStoreMode()) {
            $product->setWebsiteIds(array($this->storeManager->getStore(null)->getWebsite()->getId()));
        }
        return $product;
    }

}
