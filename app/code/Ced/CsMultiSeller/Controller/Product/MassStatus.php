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
 * Class MassStatus
 * @package Ced\CsMultiSeller\Controller\Product
 */
class MassStatus extends \Ced\CsMarketplace\Controller\Vproducts
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
     * @var \Ced\CsMultiSeller\Helper\Data
     */
    protected $multisellerHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $action;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * MassStatus constructor.
     * @param \Ced\CsMultiSeller\Helper\Data $multisellerHelper
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory
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
        \Ced\CsMultiSeller\Helper\Data $multisellerHelper,
        \Magento\Catalog\Model\Product\Action $action,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollectionFactory,
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
        $this->multisellerHelper = $multisellerHelper;
        $this->action = $action;
        $this->productFactory = $productFactory;
        $this->vproductsCollectionFactory = $vproductsCollectionFactory;
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
     * Update product(s) status action
     *
     */
    public function execute()
    {

        if (!$this->_getSession()->getVendorId())
            return;

        if (!$this->multisellerHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        $ids = (array)$this->getRequest()->getParam('product');

        if (is_array($ids) && !empty($ids)) {
            $ids = explode(',', $ids[0]);
        }

        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $status = (int)$this->getRequest()->getParam('status');
        try {

            $productIds = $this->_validateMassStatus($ids, $status);

            if (count(array_diff($productIds, $ids)) > 0 || count($productIds) == 0)

                $this->messageManager->addErrorMessage(__('Some of the processed products have not approved. Only Approved Products status can be changed.'));

            $this->action->updateAttributes($productIds, array('status' => $status), $storeId);


            $this->messageManager->addSuccessMessage(
                __('Total of # %1 record(s) have been updated.', count($productIds))
            );
        } catch (\Exception $e) {
            $this->messageManager
                ->addExceptionMessage($e, __('An error occurred while updating the product(s) status.'));

        }
        $this->_redirect('*/*/', array('store' => $storeId));

    }

    /**
     *
     * @param array $productIds
     * @return $approvedIds
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->productFactory->create()->isProductsHasSku($productIds)) {
                throw new \Exception(
                    __('Some of the processed products have no SKU value defined. Please fill it prior to performing operations on these products.')

                );
            }
        }

        $approvedProducts = $this->vproductsCollectionFactory->create()
            ->addFieldToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS))
            ->addFieldToFilter('is_multiseller', array('eq' => 1))
            ->addFieldToFilter('product_id', array('in' => $productIds));

        $approvedIds = array();

        foreach ($approvedProducts as $row) {
            $approvedIds[] = $row->getProductId();
        }
        return $approvedIds;

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
