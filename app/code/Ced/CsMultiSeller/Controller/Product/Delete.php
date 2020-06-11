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
 * Class Delete
 * @package Ced\CsMultiSeller\Controller\Product
 */
class Delete extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMultiSeller\Model\MultisellFactory
     */
    protected $multisellFactory;

    /**
     * @var \Ced\CsMultiSeller\Helper\Data
     */
    protected $csmultisellerHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Delete constructor.
     * @param \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper
     * @param \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory
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
        \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper,
        \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory,
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
        $this->csmultisellerHelper = $csmultisellerHelper;
        $this->multisellFactory = $multisellFactory;
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
    }

    /**
     * Delete product action
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        if (!$this->csmultisellerHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        $vendorId = $this->_getSession()->getVendorId();

        if (!$vendorId)
            return;

        $id = $this->getRequest()->getParam('id');

        $vendorProduct = false;

        if ($id && $vendorId) {
            $vendorProduct = $this->multisellFactory->create()->isAssociatedProduct($vendorId, $id);
        }

        if (!$vendorProduct) {
            $redirectBack = true;
        } else if ($id) {
            $this->registry->register("isSecureArea", 1);
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $product = $this->productFactory->create()->load($id);
            $sku = $product->getSku();
            try {

                if ($product && $product->getId()) {
                    $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
                    $product->delete();
                    $this->vproductsFactory->create()->changeVproductStatus(array($id), \Ced\CsMarketplace\Model\Vproducts::DELETED_STATUS);

                    $this->messageManager->addSuccessMessage(__('Your Product Has Been Sucessfully Deleted'));

                }

            } catch (\Exception $e) {

                $this->messageManager->addErrorMessage($e->getMessage());

            }

            $this->setCurrentStore();

        }
        $this->_redirect('*/*/index', array('store' => $this->getRequest()->getParam('store')));
    }

    /**
     * Set current store
     */
    public function setCurrentStore()
    {
        if ($this->registry->registry('ced_csmarketplace_current_store')) {
            $currentStoreId = $this->registry->registry('ced_csmarketplace_current_store');
            $this->storeManager->setCurrentStore($currentStoreId);
        }
    }
}
