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
 * @package     Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Ccompose
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Ccompose extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * Ccompose constructor.
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
        $this->csmarketplaceHelper = $csmarketplaceHelper;
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
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        if (!$this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/general/messaging_active'))
            return $this->_redirect('customer/account');

        $resultRedirect = $this->resultPageFactory->create();

        if ($this->getRequest()->getParam('admin'))
            $resultRedirect->getConfig()->getTitle()->set(__('Admin Compose'));
        else
            $resultRedirect->getConfig()->getTitle()->set(__('Customer Compose'));

        return $resultRedirect;

    }
}
