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

namespace Ced\CsMultiSeller\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class SearchVendorProductsProvider
 * @package Ced\CsMultiSeller\Model\ResourceModel
 */
class SearchVendorProductsProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsMultiSeller\Model\System\Config\Source\Type
     */
    protected $type;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * SearchVendorProductsProvider constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsMultiSeller\Model\System\Config\Source\Type $type
     * @param \Ced\CsMarketplace\Model\Session $session
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMultiSeller\Model\System\Config\Source\Type $type,
        \Ced\CsMarketplace\Model\Session $session,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'catalog_product_entity',
        $resourceModel = '\Magento\Catalog\Model\ResourceModel\Product'
    )
    {
        $this->storeManager = $storeManager;
        $this->type = $type;
        $this->session = $session;
        $this->resourceConnection = $resourceConnection;
        $this->storeFactory = $storeFactory;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }


    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = 0;
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $store = $this->_getStore();
        $types = $this->type->toFilterOptionArray(true, false, $store->getId());
        $types = array_keys($types);
        $this->_count = count($types);

        $vendorId = $this->session->getVendorId();
        $tablename = $this->resourceConnection->getTableName('ced_csmarketplace_vendor_products');
        $storeId = 0;
        $websiteId = $this->storeFactory->create()->load(0)->getWebsiteId();
        if ($websiteId) {
            if (in_array($websiteId, $this->vproductsFactory->create()->getAllowedWebsiteIds())) {
                $storeId = 0;
            }
        }

        $this->addAttributeToFilter('type_id', array('in' => $types))
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', array('neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE));
        $this->getSelect()
            ->joinleft(array('vproducts' => $tablename), 'vproducts.product_id=e.entity_id', array('vproducts.check_status', 'vproducts.is_multiseller', 'vproducts.vendor_id'));
        $this->getSelect()
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`check_status` WHEN 1 THEN 1 WHEN 0 THEN 0 ELSE 1 END') . "='1'")
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`is_multiseller` WHEN 1 THEN 1 WHEN 0 THEN 0 ELSE 0 END') . " ='0'")
            ->where(new \Zend_Db_Expr ('CASE `vproducts`.`vendor_id` WHEN ' . $vendorId . ' THEN ' . $vendorId . ' WHEN 0 THEN 0 ELSE 0 END') . " <> '" . $vendorId . "'");
        if ($storeId) {
            $this->addStoreFilter($storeId);
        }

        return $this;
    }
}
