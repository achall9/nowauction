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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class NewAction
 * @package Ced\CsProduct\Controller\Vproducts
 */
class NewAction extends \Ced\CsMarketplace\Controller\Vproducts\NewAction
{
    /**
     * @var \Ced\CsProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $type;

    /**
     * NewAction constructor.
     * @param \Ced\CsProduct\Helper\Data $helper
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $httpRequest
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
     */
    public function __construct(
        \Ced\CsProduct\Helper\Data $helper,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $httpRequest,
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
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
    )
    {
        $this->helper = $helper;
        $this->productBuilder = $productBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->httpRequest = $httpRequest;
        $this->type = $type;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor,
            $storeManager,
            $productFactory,
            $vproductsFactory,
            $type
        );
        $this->clean();
    }

    /**
     * Create new product page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
	public function execute()
	{
		$storeId = $this->storeManager->getStore()->getId();
		$module_name = $this->httpRequest->getModuleName();
		if($this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
				&& $module_name=='csmarketplace'){
			return $this->_redirect('csmarketplace/vendor/index');
		}
		 if(!$this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) && $module_name == 'csproduct'){
				return $this->_redirect('csmarketplace/vendor/index');

		}
		 if(!$this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) && $module_name =="csmarketplace")
		 {
		 	return parent::execute();
		 }
		
		if (!$this->getRequest()->getParam('set')) {
			 $this->_redirect('csmarketplace/vendor/index');
		}
	
		$attributesetIds = $this->scopeConfig->getValue('ced_vproducts/general/set', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if(!$attributesetIds)
		{
			$this->messageManager->addErrorMessage(__('No Attribute Set Is Allowed For Product Creation.'));
			return $this->_redirect('csmarketplace/vendor/index');

		}

		$allowedType = $this->type->getAllowedType($this->storeManager->getStore()->getId());
	    if(!in_array($this->getRequest()->getParam('type'),$allowedType))
	    {
	    	$this->messageManager->addErrorMessage(__('You are not allowed to create '.ucfirst(($this->getRequest()->getParam('type')).' Product')));
	    	return $this->_redirect('csmarketplace/vendor/index');

	    }	
	
		$product = $this->productBuilder->build($this->getRequest());
		$this->_eventManager->dispatch('catalog_product_new_action', ['product' => $product]);
		$this->_eventManager->dispatch('csproduct_vproducts_new_action', ['product' => $product]);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		if ($this->getRequest()->getParam('popup')) {
			$resultPage->addHandle(['popup', 'csproduct_vproducts_' . $product->getTypeId()]);
		} else {
			$resultPage->addHandle(['csproduct_vproducts_' .$product->getTypeId()]);
			$resultPage->getConfig()->getTitle()->prepend(__('Products'));
			$resultPage->getConfig()->getTitle()->prepend(__('New Product'));
		}
	
		$block = $resultPage->getLayout()->getBlock('catalog.wysiwyg.js');
		if ($block) {
			$block->setStoreId($product->getStoreId());
		}
	
		return $resultPage;
	}

    /**
     * @return $this
     */
    public function clean(){
       $this->helper->cleanCache();
       return $this;
    }
}


