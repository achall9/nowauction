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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block;

use Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;

/**
 * Class Vproducts
 * @package Ced\CsMarketplace\Block
 */
class Vproducts extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    protected $_filtercollection;

    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    protected $_productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public $_vproductsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    public $_vproducts;

    protected $_storeSwitcherHtml;

    /**
     * @var Type
     */
    protected $_configVproductType;

    /**
     * @var \Magento\Store\Model\ResourceModel\Group\Collection
     */
    protected $_groupCollection;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_categoryModel;

    /**
     * @var Vproducts\Store\Switcher|null
     */
    protected $_storeSwitcher;

    /**
     * Vproducts constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Type $type
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param Session $customerSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Registry $registry
     * @param UrlFactory $urlFactory
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param \Magento\Store\Model\ResourceModel\Group\Collection $groupCollection
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param Vproducts\Store\Switcher|null $_storeSwitcher
     * @param Type $_configVproductType
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Registry $registry,
        UrlFactory $urlFactory,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Store\Model\ResourceModel\Group\Collection $groupCollection,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Ced\CsMarketplace\Block\Vproducts\Store\Switcher $_storeSwitcher,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $_configVproductType,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    )
    {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->_registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->_categoryModel = $categoryModel;
        $this->_groupCollection = $groupCollection;
        $this->_vproducts = $vproducts;
        $this->_priceCurrency = $priceCurrency;
        $this->_configVproductType = $_configVproductType;
        $this->_storeManager = $context->getStoreManager();
        $this->_vproductsFactory = $vproductsFactory->create();
        $this->_productCollectionFactory = $productCollection->create();
        $this->_type = $type;
        $this->_storeSwitcher = $_storeSwitcher;
        $vendorId = $this->getVendorId();

        $currentStore = $this->_storeManager->getStore(null)->getId();
        $this->_storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $productcollection = $this->_productCollectionFactory;
        $storeId = 0;
        if ($this->getRequest()->getParam('store')) {
            $websiteId = $this->_storeManager->getStore($this->getRequest()->getParam('store'))->getWebsiteId();
            if ($websiteId) {
                if (in_array($websiteId, $this->_vproductsFactory->getAllowedWebsiteIds())) {
                    $storeId = $this->getRequest()->getParam('store');
                }
            }
        }

        $productcollection->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSort('entity_id', 'DESC');

        $productcollection->addStoreFilter($storeId);
        $productcollection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $storeId);
        $productcollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $storeId);
        $productcollection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $storeId);
        $productcollection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $storeId);

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $productcollection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        $productcollection->joinField('check_status', 'ced_csmarketplace_vendor_products', 'check_status', 'product_id=entity_id','{{table}}.vendor_id='.$vendorId, 'right');
        $params = $this->session->getData('product_filter');

        if (isset($params) && is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if ($field == 'store' || $field == 'store_switcher' || $field == "__SID" || $field == 'reset_product_filter')
                    continue;
                if (is_array($value)) {
                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        $productcollection->addAttributeToFilter($field, array('gteq' => $from));
                    }
                    if (isset($value['to']) && urldecode($value['to']) != "") {
                        $to = urldecode($value['to']);
                        $productcollection->addAttributeToFilter($field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    $productcollection->addAttributeToFilter($field, array("like" => '%' . urldecode($value) . '%'));
                }
            }
        }

        $this->_storeManager->setCurrentStore($currentStore);
        $productcollection->setStoreId($storeId);

        if ($productcollection->getSize() > 0) {
            $this->_filtercollection = $productcollection;
            $this->setVproducts($this->_filtercollection);
        }
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_filtercollection) {
            if ($this->_filtercollection->getSize() > 0) {
                if ($this->getRequest()->getActionName() == 'index') {
                    $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
                    $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
                    $pager->setCollection($this->_filtercollection);
                    $this->setChild('pager', $pager);
                }
            }
        }
        return $this;
    }

    /**
     * Get pager HTML
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get Edit product url
     * @param $product
     * @return string
     */
    public function getEditUrl($product)
    {
        return $this->getUrl('*/*/edit', array('_nosid' => true, 'id' => $product->getId(), 'type' => $product->getTypeId(), 'store' => $this->getRequest()->getParam('store', 0)));
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->_type->toOptionArray(false, true);
    }

    /**
     * get Product Type url
     *
     */
    public function getProductTypeUrl()
    {
        return $this->getUrl('*/*/new/', array('_nosid' => true));
    }

    /**
     * get Delete url
     * @param $product
     * @return string
     */
    public function getDeleteUrl($product)
    {
        return $this->getUrl('*/*/delete', array('_nosid' => true, 'id' => $product->getId()));
    }

    /**
     * back Link url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * get Product
     *
     */

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get Category IDs
     *
     */
    public function getCategoryIds()
    {
        $_product = $this->getProduct();
        $category_ids = [];
        if ($_product) {
            $category_ids = $_product->getCategoryIds();
        }
        if (is_array($category_ids) && empty($category_ids)) {
            $category_ids = [];
        }
        return $category_ids;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryModel(){
        return $this->_categoryModel;
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroups(){
        return $this->_groupCollection->addFieldToFilter('group_id', array('neq'=>0))->setOrder('website_id', 'ASC');
    }

    /**
     * @return \Ced\CsMarketplace\Model\Vproducts
     */
    public function getVproductsObject(){
        return $this->_vproducts;
    }

    /**
     * @return mixed
     */
    public function getStoreSwitcherHtml(){
        if($this->_storeSwitcherHtml == null){
            $this->_storeSwitcherHtml = $this->_storeSwitcher
                ->setSwitchUrl(
                    $this->getUrl('*/*/*',
                        array('_current'=>false, '_query'=>false,'_nosid'=>true)
                    )
                )->toHtml();
        }
        return $this->_storeSwitcherHtml;
    }

    /**
     * @return array
     */
    public function getVproductConfigArray(){
        if(empty($this->_configVproductToOptionArray)){
            $this->_configVproductToOptionArray = $this->_configVproductType->toOptionArray(
                false,
                true
            );
        }
        return $this->_configVproductToOptionArray;
    }

    /**
     * @param $price
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param $currency
     * @return float
     */
    public function formatCurrency(
        $price,
        $includeContainer = false,
        $precision = 2,
        $scope = null,
        $currency
    ){
        return $this->_priceCurrency->format(
            $price,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }

}
