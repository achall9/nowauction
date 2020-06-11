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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\ActionFactory;

/**
 * Class ChangeNewProductStatus
 * @package Ced\CsMarketplace\Observer
 */
Class ChangeNewProductStatus implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $_vproductsFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * ChangeNewProductStatus constructor.
     * @param ActionFactory $actionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_vproductsFactory = $vproductsFactory;
    }

    /**
     * Notify Customer Account status Change
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeManager = $this->_storeManager;
        $scopeConfig = $this->_scopeConfig;
        $confirmation = $scopeConfig->getValue('ced_vproducts/general/confirmation', 'store', $storeManager->getStore()->getCode());

        $marketplaceProduct = $this->_vproductsFactory->create()->load($product->getId(),'product_id');

        if($marketplaceProduct && $marketplaceProduct->getId()) {
            if ($marketplaceProduct->getCheckStatus() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS) {
                $status = $product->getStatus();
            } else {
                $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            }
        } else {
            if ($confirmation == true) {
                $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            } else {
                $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            }
        }

        $this->actionFactory->create()->updateAttributes([$product->getId()], ['status' => $status], $product->getStoreId());
    }
}
