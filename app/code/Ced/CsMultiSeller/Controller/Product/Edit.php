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
 * Class Edit
 * @package Ced\CsMultiSeller\Controller\Product
 */
class Edit extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBuilder;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsMultiSeller\Helper\Data
     */
    protected $csmultisellerHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMultiSeller\Model\MultisellFactory
     */
    protected $multisellFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Model\Url $urlBuilder
     * @param \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
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
        \Magento\Backend\Model\Url $urlBuilder,
        \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
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
        $this->resultPageFactory = $resultPageFactory;
        $this->urlBuilder = $urlBuilder;
        $this->csmultisellerHelper = $csmultisellerHelper;
        $this->productFactory = $productFactory;
        $this->multisellFactory = $multisellFactory;
        $this->storeManager = $storeManager;
        $this->storeFactory = $storeFactory;
        $this->vproductsFactory = $vproductsFactory;
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
     * Edit product Form
     */
    public function execute()
    {

        if (!$this->_getSession()->getVendorId())
            return;

        if (!$this->csmultisellerHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        $id = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        $collection = $this->productFactory->create()->setStoreId($storeId)->load($id);
        $this->registry->register('current_product_edit', $collection);
        $vendorId = $this->_getSession()->getVendorId();
        $vendorProduct = 0;
        if ($id && $vendorId) {
            $vendorProduct = $this->multisellFactory->create()->isAssociatedProduct($vendorId, $id);
        }

        if (!$vendorProduct) {
            $this->_redirect('csmultiseller/*');
            return;
        }

        $this->mode = \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE;
        $product = $this->_initProduct();
        $resultPage = $this->resultPageFactory->create();
        if (!$this->storeManager->isSingleStoreMode()
            &&
            ($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->urlBuilder->getUrl(
                        'csmultiseller/*/*',
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }
        $resultPage->getConfig()->getTitle()->set(__('Edit Product'));
        return $resultPage;
    }

    /**
     * Initialize product from request parameters
     * @return Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->productFactory->create();
        $currentStore = $this->storeManager->getStore()->getId();
        if (!$productId) {
            $product->setStoreId($currentStore);
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
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
            }
        }
        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())
        ) {
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
}
