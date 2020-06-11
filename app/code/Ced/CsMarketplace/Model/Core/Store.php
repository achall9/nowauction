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

namespace Ced\CsMarketplace\Model\Core;

/**
 * Class Store
 * @package Ced\CsMarketplace\Model\Core
 */
class Store extends \Magento\Store\Model\Store
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Store constructor.
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $resource
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Config\Model\ResourceModel\Config\Data $configDataResource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\Information $information
     * @param string $currencyInstalled
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param bool $isCustomEntryPoint
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\ResourceModel\Store $resource,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\Information $information,
        $currencyInstalled,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $isCustomEntryPoint = false,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $coreFileStorageDatabase,
            $configCacheType,
            $url,
            $request,
            $configDataResource,
            $filesystem,
            $config,
            $storeManager,
            $sidResolver,
            $httpContext,
            $session,
            $currencyFactory,
            $information,
            $currencyInstalled,
            $groupRepository,
            $websiteRepository,
            $resourceCollection,
            $isCustomEntryPoint,
            $data
        );

        $this->vendorFactory = $vendorFactory;
        $this->registry = $registry;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @param string $path
     * @return mixed|null|string
     */
    public function getConfig($path)
    {
        $path = $this->preparePath($path);
        $data = $this->_config->getValue($path, 'store', $this->getCode());
        if (!$data) {
            $data = $this->_config->getValue($path,  'default');
        }
        return $data === false ? null : $data;
    }

    /**
     * @param $path
     * @param null $group
     * @param int $case
     * @return string
     */
    public function preparePath($path, $group = null, $case = 1)
    {
        if (!preg_match('/ced_/i', $path) || preg_match('/'.preg_quote('ced_csgroup/general/activation', '/').'/i', $path)) {
            return $path;
        }

        if ($group == null) {
            switch ($case) {
                case 1:
                    if ($this->_moduleManager->isEnabled('Ced_CsCommission')) {
                        if ($this->registry->registry('ven_id')) {
                            $vendor = $this->registry->registry('ven_id');
                            if (is_numeric($this->registry->registry('ven_id'))) {
                                $vendor = $this->vendorFactory->create()->load($this->registry->registry('ven_id'));
                            }
                            if ($vendor && is_object($vendor) && $vendor->getId()) {
                                return 'v'.$vendor->getId().'/'.$path;
                            }
                        }
                    }
                    return $path;
                    break;
                default:
                    return $path;
                    break;
            }
        } else {
            return $path;
        }
    }
}
