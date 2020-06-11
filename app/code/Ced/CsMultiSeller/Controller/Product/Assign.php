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
 * Class Assign
 * @package Ced\CsMultiSeller\Controller\Product
 */
class Assign extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMultiSeller\Model\MultisellFactory
     */
    protected $multisellFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMultiSeller\Helper\Data
     */
    protected $csmultisellerHelper;

    /**
     * Assign constructor.
     * @param \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsMultiSeller\Model\MultisellFactory $multisellFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMultiSeller\Helper\Data $csmultisellerHelper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->multisellFactory = $multisellFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->csmultisellerHelper = $csmultisellerHelper;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Create product Form
     */
    public function execute()
    {
        $vproducts = $this->multisellFactory->create()->getVendorProductIds();
        $productCount = count($this->vproductsFactory->create()->getVendorProductIds($this->_getSession()->getVendorId())) + count($vproducts);
        if ($productCount >= $this->csmarketplaceHelper->getVendorProductLimit()) {
            $this->messageManager->addErrorMessage(__('Product Creation limit has Exceeded'));
            $this->_redirect('*/*/index', array('store' => $this->getRequest()->getParam('store', 0)));
            return;
        }
        if (!$this->_getSession()->getVendorId())
            return;


        if (!$this->csmultisellerHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        if ($this->getRequest()->getParam('id', '') == '') {
            $this->_redirect('csmultiseller/product/new');
            return;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Add Product'));
        return $resultPage;

    }
}
