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

namespace Ced\CsMarketplace\Helper;

use Ced\CsMarketplace\Model\Vendor;

/**
 * Class Data
 * @package Ced\CsMarketplace\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LOGINACTION = 'login';
    const REGISTERACTION = 'register';
    const VORDER_CREATE = "VORDER_CREATE";
    const VORDER_CANCELED = "VORDER_CANCELED";
    const VORDER_PAYMENT_STATE_CHANGED = "VORDER_PAYMENT_STATE_CHANGED";

    const SALES_ORDER_CREATE = "SALES_ORDER_CREATE";
    const SALES_ORDER_CANCELED = "SALES_ORDER_CANCELED";
    const SALES_ORDER_ITEM = "SALES_ORDER_ITEM";
    const SALES_ORDER_PAYMENT_STATE_CHANGED = "SALES_ORDER_PAYMENT_STATE_CHANGED";

    const VPAYMENT_CREATE = "VPAYMENT_CREATE";
    const VPAYMENT_TOTAL_AMOUNT = "VPAYMENT_TOTAL_AMOUNT";

    const LOGIN_DEFAULT_DESIGN = 'default';
    const LOGIN_ADVANCE_DESIGN = 'new_design';

    protected $_allowedFeedType = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    protected $_configValueManager;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    protected $request;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Catalog\Model\Product\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Indexer\Model\ProcessorFactory
     */
    protected $processorFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Website
     */
    protected $website;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @var \Ced\CsMarketplace\Model\Vshop
     */
    protected $vshop;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_stringUtils;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $moduleRegistry;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueInterface $value
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Catalog\Model\Product\ActionFactory $actionFactory
     * @param \Magento\Indexer\Model\ProcessorFactory $processorFactory
     * @param \Magento\Catalog\Model\Product\Website $website
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueInterface $value,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Indexer\Model\ProcessorFactory $processorFactory,
        \Magento\Catalog\Model\Product\Website $website,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Helper\Context $context

    ){
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_request = $request;
        $this->_productMetadata = $productMetadata;
        $this->_storeManager = $storeManager;
        $this->_scopeConfigManager = $context->getScopeConfig();
        $this->_configValueManager = $value;
        $this->_transaction = $transaction;
        $this->requestInterface = $requestInterface;
        $this->state = $state;
        $this->websiteFactory = $websiteFactory;
        $this->actionFactory = $actionFactory;
        $this->processorFactory = $processorFactory;
        $this->website = $website;
        $this->vshop = $vshop;
        $this->resourceConnection = $resourceConnection;
        $this->deploymentConfig = $deploymentConfig;
        $this->notificationFactory = $notificationFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->moduleRegistry = $moduleRegistry;
        $this->_stringUtils = $stringUtils;
        $this->_vpaymentFactory = $vpaymentFactory;
        $this->filterManager = $filterManager;
        $this->_moduleManager = $context->getModuleManager();
        $this->_store = $store;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Filter\FilterManager
     */
    public function getFilterManager(){
        return $this->filterManager;
    }

    /**
     * @return \Magento\Framework\Stdlib\StringUtils
     */
    public function getStringUtils() {
        return $this->_stringUtils;
    }

    /**
     * @param $trans_id
     * @return mixed
     */
    public function getTransaction($trans_id) {
        $transaction = $this->_vpaymentFactory->create()->load( $trans_id);
        return $transaction;
    }

    /**
     * Set a specified store ID value
     *
     * @param  int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $params = $this->_request->getParams();
        if ($this->_storeId) {
            $storeId = (int)$this->_storeId;
        } else {
            $storeId = isset($params['store']) ? (int)$params['store'] : null;
        }
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Set a specified store ID value
     *
     * @param  int $store
     * @return $this
     */
    public function setWebsiteId($website)
    {
        $this->_websiteId = $website;
        return $this;
    }

    /**
     * Get current website
     *
     * @return \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Api\Data\WebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsite()
    {
        $params = $this->_request->getParams();
        if ($this->_websiteId) {
            $_websiteId = (int)$this->_websiteId;
        } else {
            $_websiteId = isset($params['website']) ? (int)$params['website'] : null;
        }
        return $this->_storeManager->getWebsite($_websiteId);
    }

    /**
     * @return mixed
     */
    public function getCustomCSS()
    {
        return $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/vendor/theme_css',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsDashboard()
    {
        return $this->getVendorUrl() == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . '/index' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . '/index/' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . 'index' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . 'index/' == $this->_getUrl('*/*/*');
    }

    /**
     * @param $logo_src
     * @param $logo_alt
     * @return $this
     */
    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogoSrc()
    {
        $logo_path = $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/vendor/vendor_logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
        return $logo_path;
    }

    /**
     * @return mixed
     */
    public function getLogoAlt()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vendor/vendor_logo_alt', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * @return mixed
     */
    public function getVendorFooterText()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vendor/vendor_footer_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getMarketplaceVersion()
    {
        return trim((string)$this->getReleaseVersion('Ced_CsMarketplace'));
    }

    /**
     * @param $module
     * @return bool|string
     */
    public function getReleaseVersion($module)
    {
        $modulePath = $this->moduleRegistry->getPath(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES, $module);
        $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, "$modulePath/etc/module.xml");
        $source = new \Magento\Framework\Simplexml\Config($filePath);
        if ($source->getNode(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version) {
            return $source->getNode(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version->__toString();
        }
        return false;
    }

    /**
     * Url encode the parameters
     *
     * @param  string | array
     * @return string | array | boolean
     */
    public function prepareParams($data)
    {
        if (!is_array($data) && strlen($data)) {
            return urlencode($data);
        }
        if ($data && is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $data[$key] = urlencode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Url decode the parameters
     *
     * @param  string | array
     * @return string | array | boolean
     */
    public function extractParams($data)
    {
        if (!is_array($data) && strlen($data)) {
            return urldecode($data);
        }
        if ($data && is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $data[$key] = urldecode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Add params into url string
     *
     * @param  string $url (default '')
     * @param  array $params (default array())
     * @param  boolean $urlencode (default true)
     * @return string | array
     */
    public function addParams($url = '', $params = array(), $urlencode = true)
    {
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (parse_url($url, PHP_URL_QUERY)) {
                    if ($urlencode) {
                        $url .= '&' . $key . '=' . $this->prepareParams($value);
                    } else {
                        $url .= '&' . $key . '=' . $value;
                    }
                } else {
                    if ($urlencode) {
                        $url .= '?' . $key . '=' . $this->prepareParams($value);
                    } else {
                        $url .= '?' . $key . '=' . $value;
                    }
                }
            }
        }
        return $url;
    }

    /**
     * Retrieve all the extensions name and version developed by CedCommerce
     *
     * @param  boolean $asString (default false)
     * @return array|string
     */
    public function getCedCommerceExtensions($asString = false)
    {
        if ($asString) {
            $cedCommerceModules = '';
        } else {
            $cedCommerceModules = [];
        }
        $allModules = $this->_scopeConfigManager->getValue(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES);
        $allModules = json_decode(json_encode($allModules), true);
        foreach ($allModules as $name => $module) {
            $name = trim($name);
            if (preg_match('/ced_/i', $name) && isset($module['release_version'])) {
                if ($asString) {
                    $cedCommerceModules .= $name . ':' . trim($module['release_version']) . '~';
                } else {
                    $cedCommerceModules[$name] = trim($module['release_version']);
                }
            }
        }
        if ($asString) {
            trim($cedCommerceModules, '~');
        }
        return $cedCommerceModules;
    }

    /**
     * Retrieve environment information of magento
     * And installed extensions provided by CedCommerce
     *
     * @return array
     */
    public function getEnvironmentInformation()
    {
        $info = array();
        $info['domain_name'] = $this->_productMetadata->getBaseUrl();
        $info['magento_edition'] = 'default';
        if (method_exists('Mage', 'getEdition')) {
            $info['magento_edition'] = $this->_productMetadata->getEdition();
        }
        $info['magento_version'] = $this->_productMetadata->getVersion();
        $info['php_version'] = phpversion();
        $info['feed_types'] = $this->getStoreConfig(\Ced\CsMarketplace\Model\Feed::XML_FEED_TYPES);
        $info['installed_extensions_by_cedcommerce'] = $this->getCedCommerceExtensions(true);

        return $info;
    }

    /**
     * Retrieve admin interest in current feed type
     *
     * @param  SimpleXMLElement $item
     * @return boolean $isAllowed
     */
    public function isAllowedFeedType(SimpleXMLElement $item)
    {
        $isAllowed = false;
        if (is_array($this->_allowedFeedType) && count($this->_allowedFeedType) > 0) {
            $cedModules = $this->getCedCommerceExtensions();
            switch (trim((string)$item->update_type)) {
                case \Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_NEW_RELEASE :
                case \Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_INSTALLED_UPDATE :
                    if (in_array(\Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_INSTALLED_UPDATE, $this->_allowedFeedType) && strlen(trim($item->module)) > 0 && isset($cedModules[trim($item->module)]) && version_compare($cedModules[trim($item->module)], trim($item->release_version), '<') === true) {
                        $isAllowed = true;
                    }
                    break;
                case \Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_UPDATE_RELEASE :
                    if (in_array(\Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_UPDATE_RELEASE, $this->_allowedFeedType) && strlen(trim($item->module)) > 0) {
                        $isAllowed = true;
                        break;
                    }
                    if (in_array(\Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_NEW_RELEASE, $this->_allowedFeedType)) {
                        $isAllowed = true;
                    }
                    break;
                case \Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_PROMO :
                    if (in_array(\Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_PROMO, $this->_allowedFeedType)) {
                        $isAllowed = true;
                    }
                    break;
                case \Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_INFO :
                    if (in_array(\Ced\CsMarketplace\Model\Source\Updates\Type::TYPE_INFO, $this->_allowedFeedType)) {
                        $isAllowed = true;
                    }
                    break;
            }
        }
        return $isAllowed;
    }

    /**
     * Retrieve vendor account page url
     *
     * @return string
     */
    public function getCsMarketplaceUrl()
    {
        return $this->_getUrl('csmarketplace/vshops');
    }


    /**
     * Retrieve CsMarketplace title
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCsMarketplaceTitle()
    {
        return $this->getStoreConfig('ced_vshops/general/vshoppage_top_title', $this->_storeManager->getStore(null)->getId());
    }

    /**
     * Retrieve I am a Vendor title
     *
     * @return string
     */
    public function getIAmAVendorTitle()
    {
        return $this->getStoreConfig('ced_vshops/general/vshoppage_title');
    }

    /**
     * Check customer account sharing is enabled
     *
     * @return boolean
     */
    public function isSharingEnabled()
    {
        if ($this->scopeConfig->getValue(\Magento\Customer\Model\Config\Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE) == \Magento\Customer\Model\Config\Share::SHARE_GLOBAL) {
            return true;
        }
        return false;
    }

    /**
     * get Product limit
     *
     * @return integer
     */
    public function getVendorProductLimit()
    {
        if ($this->requestInterface->getParam('store_switcher', 0))
            return $this->scopeConfig->getValue('ced_vproducts/general/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->requestInterface->getParam('store_switcher', 0));
        return $this->scopeConfig->getValue('ced_vproducts/general/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve vendor account page url
     *
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->_getUrl('csmarketplace/vendor');
    }

    /**
     * Authenticate vendor
     *
     * @param  int $customerId
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($customerId = 0)
    {
        if ($customerId) {
            $vendor = $this->vendorFactory->create()->loadByCustomerId($customerId);
            if ($vendor && $vendor->getId()) {
                return $this->canShow($vendor);
            }
        }
        return false;
    }

    /**
     * Check if a vendor can be shown
     *
     * @param  Vendor|int $vendor
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canShow($vendor)
    {
        if (is_numeric($vendor)) {
            $vendor = $this->vendorFactory->create()->load($vendor);
        }

        if (!is_object($vendor)) {
            $vendor = $this->vendorFactory->create()->loadByAttribute('shop_url', $vendor);
        }

        if (!$vendor || !$vendor->getId()) {
            return false;
        }

        if (!$vendor->getIsActive()) {
            return false;
        }
        if ($this->state->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            if (!$this->isSharingEnabled() && ($vendor->getWebsiteId() != $this->_storeManager->getStore()->getWebsiteId())) {
                return false;
            }
        }
        return true;
    }

    /**
     *Rebuild Website Ids
     *
     * @return Data $websiteIds
     */
    public function rebuildWebsites()
    {
        $productIds = [];
        $collection = $this->vproductsFactory->create()->getVendorProducts('', 0)->setOrder('vendor_id', 'ASC');
        foreach ($collection as $row) {
            $productIds[] = $row->getProductId();
        }
        $previousVendorId = 0;
        $vendorWebsiteIds = [];
        $removeWebsiteIds = array_keys($this->websiteFactory->create()->getCollection()->toOptionHash());
        $actionModel = $this->actionFactory->create();
        $this->updateWebsites($productIds, $removeWebsiteIds, 'remove');

        foreach ($collection as $row) {
            if (!$this->canShow($row->getVendorId())) {
                continue;
            }
            $productWebsiteIds = explode(',', $row->getWebsiteIds());
            if (!$previousVendorId || $previousVendorId != $row->getVendorId()) {
                $vendorWebsiteIds = $this->vendorFactory->create()->getWebsiteIds($row->getVendorId());
            }
            $previousVendorId = $row->getVendorId();
            $websiteIds = array_intersect($productWebsiteIds, $vendorWebsiteIds);
            if ($websiteIds) {
                $this->updateWebsites([$row->getProductId()], $websiteIds, 'add');
            }
        }

        $indexCollection = $this->processorFactory->create()->getCollection();
        foreach ($indexCollection as $index) {
            /* @var $index \Magento\Indexer\Model\Processor */
            $index->reindexAll();
        }
        $this->cleanCache();

        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $obj->get('Magento\Framework\App\Config\Element')->saveConfig(Vendor::XML_PATH_VENDOR_WEBSITE_SHARE, 0);
        return $this;
    }

    /**
     * Clear cache related with product id
     *
     * @return bool
     */
    public function cleanCache()
    {
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        return true;
    }

    /**
     *update Websites
     *
     * @param $productIds
     * @param $websiteIds
     * @param $type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateWebsites($productIds, $websiteIds, $type)
    {
        $this->_eventManager->dispatch(
            'catalog_product_website_update_before',
            [
                'website_ids' => $websiteIds,
                'product_ids' => $productIds,
                'action' => $type
            ]
        );

        if ($type == 'add') {
            $this->website->addProducts($websiteIds, $productIds);
        } else if ($type == 'remove') {
            $this->website->removeProducts($websiteIds, $productIds);
        }

        $actionModel = $this->actionFactory->create();
        $actionModel->setData([
                'product_ids' => array_unique($productIds),
                'website_ids' => $websiteIds,
                'action_type' => $type
            ]
        );

        $this->_eventManager->dispatch(
            'catalog_product_website_update',
            [
                'website_ids' => $websiteIds,
                'product_ids' => $productIds,
                'action' => $type
            ]
        );
    }

    /**
     * Get new vendor collection
     *
     * @return \Ced\CsMarketplace\Model\ResourceModel\Vendor\Collection
     */
    public function getNewVendors()
    {
        return $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('status', array('eq' => Vendor::VENDOR_NEW_STATUS));
    }

    /**
     * @return array
     */
    public function getFilterParams()
    {
        return array(
            '_secure' => true,
            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid::VAR_NAME_FILTER => base64_encode('status=' . Vendor::VENDOR_NEW_STATUS),
        );
    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorLogEnabled()
    {
        return $this->getStoreConfig('ced_csmarketplace/vlogs/active', $this->getStore()->getId());
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRootId()
    {
        return $this->_storeManager->getStore()->getRootCategoryId();
    }

    /**
     * Log Process Data
     */
    public function logProcessedData($data, $tag = false)
    {
        if (!$this->isVendorLogEnabled()) {
            return;
        }

        $file = $this->getStoreConfig('ced_vlogs/general/process_file');

        $controller = $this->requestInterface->getControllerName();
        $action = $this->requestInterface->getActionName();
        $router = $this->requestInterface->getRouteName();
        $module = $this->requestInterface->getModuleName();

        $out = '';
        @$out .= "<pre>";
        @$out .= "Controller: $controller\n";
        @$out .= "Action: $action\n";
        @$out .= "Router: $router\n";
        @$out .= "Module: $module\n";
        foreach (debug_backtrace() as $key => $info) {
            @$out .= "#" . $key . " Called " . $info['function'] . " in " . $info['file'] . " on line " . $info['line'] . "\n";
            break;
        }
        if ($tag) {
            @$out .= "#Tag " . $tag . "\n";
        }
        @$out .= "</pre>";
    }

    /**
     * Log Exception
     */
    public function logException(\Exception $e)
    {
        if (!$this->isVendorLogEnabled()) {
            return;
        }
        $file = $this->getStoreConfig('ced_vlogs/general/exception_file');
        $this->_logger->critical("\n" . $e->__toString(), null, $file, true);
    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorDebugEnabled()
    {
        $isDebugEnable = (int)$this->getStoreConfig('ced_csmarketplace/vlogs/debug_active');
        $clientIp = $this->_getRequest()->getClientIp();
        $allow = false;

        if ($isDebugEnable) {
            $allow = true;

            // Code copy-pasted from core/helper, isDevAllowed method
            // I cannot use that method because the client ip is not always correct (e.g varnish)
            $allowedIps = $this->getStoreConfig('dev/restrict/allow_ips');
            if ($isDebugEnable && !empty($allowedIps) && !empty($clientIp)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($clientIp, $allowedIps) === false
                    && array_search($this->_request->getHttpHost(), $allowedIps) === false
                ) {
                    $allow = false;
                }
            }
        }
        return $allow;
    }

    /**
     * Check Vendor Log is enabled
     *
     * @param $vendor
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isShopEnabled($vendor)
    {
        $model = $this->vshop->loadByField(array('vendor_id'), array($vendor->getId()));

        if ($model && $model->getId()) {
            if ($model->getShopDisable() == \Ced\CsMarketplace\Model\Vshop::DISABLED) {
                return false;
            }
        }
        return true;
    }

    /**
     * Function for setting Config value of current store
     *
     * @param string $path ,
     * @param string $value ,
     * @param null $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStoreConfig($path, $value, $storeId = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        $data = [
            'path' => $path,
            'scope' => 'stores',
            'scope_id' => $storeId,
            'scope_code' => $store->getCode(),
            'value' => $value,
        ];
        $this->_configValueManager->addData($data);
        $this->_transaction->addObject($this->_configValueManager);
        $this->_transaction->save();
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }

    /**
     * @param $key
     * @return string
     */
    public function getTableKey($key)
    {
        $tablePrefix = (string)$this->deploymentConfig->get(\Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX);

        $exists = $this->resourceConnection->getConnection('core_write')->showTableStatus($tablePrefix . 'permission_variable');
        if ($exists) {
            return $key;
        } else {
            return "{$key}";
        }
    }

    /**
     * @return bool|mixed
     */
    public function getCsMarketplaceLink()
    {
        if ($this->scopeConfig->getValue('ced_csmarketplace/general/activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig->getValue('ced_vshops/general/vshoppage_top_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getIamaVendorLink()
    {
        if ($this->scopeConfig->getValue('ced_csmarketplace/general/activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig->getValue('ced_vshops/general/vshoppage_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    /**
     * @param $data
     */
    public function setNotification($data){
        $notification = $this->notificationFactory->create();
        $notification->setData($data)->save();
    }

    /**
     * @param $reference
     */
    public function readNotification($reference){
        $notification = $this->notificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('reference_id',$reference)
            ->getFirstItem();

        if($notification && $notification->getId())
            $notification->setStatus(1)->save();
    }

    /**
     * @param $vendor_id
     */
    public function readAllNotifications($vendor_id){
        $notifications = $this->notificationFactory->create()
            ->getCollection()
            ->updateRecords(['status'=>1],'vendor_id='.$vendor_id)
        ;
        /* ->addFieldToFilter('vendor_id',$vendor_id);
foreach($notifications as $notification){
$notification->setStatus(1)->save();
}*/
    }

    /**
     *
     */
    public function deleteOldNotifications(){
        $notifications = $this->notificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',1)
            ->walk('delete');
    }

    /**
     * @param $action
     * @param $params
     * @return string
     */
    public function getUrl($action, $params){
        return $this->_getUrl($action,$params);
    }

    public function canShowLogin()
    {
        if (strtolower($this->_request->getActionName()) == self::LOGINACTION || strtolower($this->_request->getActionName()) == self::REGISTERACTION)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function canShowHeaderInfo()
    {
        if (strtolower($this->_request->getActionName()) == self::LOGINACTION)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function isSocialLoginEnabled()
    {
        if ($this->_moduleManager->isEnabled('Ced_VendorsocialLogin'))
        {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function newLoginPageEnabled()
    {
        $loginPageDesign = $this->getStoreConfig('ced_csmarketplace/login_page/design',
            $this->_storeManager->getStore(null)->getId());
        if ($loginPageDesign == self::LOGIN_ADVANCE_DESIGN)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function isFacebookLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_facebook_link', $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/facebook_id', $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isTwitterLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_twitter_link', $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/twitter_id', $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isLinkedinLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_linkedin_link', $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getLinkedinId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/linkedin_id', $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isInstagramLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_instagram_link', $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getInstagramId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/instagram_id', $this->_store->getStoreId());
    }
}
