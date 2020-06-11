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
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vproducts;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Edit extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $classModelFactory;

    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Edit constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Downloadable\Model\Product\Type $type
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Model\ClassModelFactory $classModelFactory
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Downloadable\Model\Product\Type $type,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Model\ClassModelFactory $classModelFactory,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->vproducts = $vproducts;
        $this->storeFactory = $storeFactory;
        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->type = $type;
        $this->priceCurrency = $priceCurrency;
        $this->classModelFactory = $classModelFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_objectManager = $objectManager;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $vendorId=$this->getVendorId();
        $id=$this->getRequest()->getParam('id');
        $status=0;
        if($id){
            $vproductsCollection = $this->vproducts->getVendorProducts('',$vendorId,$id);
            $status=$vproductsCollection->getFirstItem()->getCheckStatus();
        }
        $storeId=0;
        if($this->getRequest()->getParam('store')){
            $websiteId = $this->storeFactory->create()->load($this->getRequest()->getParam('store'))->getWebsiteId();
            if($websiteId){
                if(in_array($websiteId,$this->vproducts->getAllowedWebsiteIds())){
                    $storeId=$this->getRequest()->getParam('store');
                }
            }
        }
        $product = $this->productFactory->create()->setStoreId($storeId);
        if($id){
            $product = $product->load($id);
        }
        $this->setVproduct($product);

        $registry = $this->registry;
        $registry->register('current_product',$product);

        $this->setCheckStatus($status);
    }

    public function getDeleteUrl($product)
    {
        return $this->getUrl('*/*/delete', array('id' => $product->getId(),'_secure'=>true,'_nosid'=>true));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/index',array('_secure'=>true,'_nosid'=>true));
    }

    public function getDownloadableProductLinks($_product){
        return $this->type->getLinks($_product);
    }

    public function getDownloadableHasLinks($_product){
        return $this->type->hasLinks($_product);
    }

    public function getDownloadableProductSamples($_product){
        return $this->type->getSamples($_product);
    }

    public function getPriceCurrencyInterface()
    {
        return $this->priceCurrency;
    }

    public function getDownloadableHasSamples($_product){
        return $this->type->hasSamples($_product);
    }

    public function getTaxModelCollection(){

        $collection = $this->classModelFactory->create()
            ->getCollection()
            ->addFieldToFilter('class_type', ['eq'=>'PRODUCT']);

        return $collection;
    }

    public function createBlock($class)
    {
        return $this->_objectManager->get($class);
    }
}
