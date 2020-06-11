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

namespace Ced\CsMultiSeller\Block;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Edit
 * @package Ced\CsMultiSeller\Block
 */
class Edit extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    public $storeFactory;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    public $currency;

    /**
     * Edit constructor.
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Locale\Currency $currency
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Locale\Currency $currency,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        $this->registry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->storeFactory = $storeFactory;
        $this->currency = $currency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }


    /**
     * Set Check Status
     */
    public function _construct()
    {
        parent::_construct();

        $this->setTitle(__('Edit Product'));
        $vendorId = $this->getVendorId();
        $id = $this->getRequest()->getParam('id');
        $status = 0;
        if ($id) {
            $vproductsCollection = $this->vproductsFactory->create()->getVendorProducts('', $vendorId, $id, 1);
            $status = $vproductsCollection->getFirstItem()->getCheckStatus();
        }
        $this->setCheckStatus($status);

    }

    /**
     * Return Delete Url
     */
    public function getDeleteUrl($product)
    {
        return $this->getUrl('*/*/delete', array('id' => $product->getId()));
    }

    /**
     * Return Back Url
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Return Save Url
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }

}
