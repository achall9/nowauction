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

namespace Ced\CsMarketplace\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Ced\CsMarketplace\Helper\Data;

class Approval extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     *
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     *
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public $vendor;

    public $helper;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Approval constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param Data $csmarketplaceHelper
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
    ) {
        $this->vendor = $vendor;
        $this->helper = $csmarketplaceHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_getSession()->isLoggedIn() && $this->helper->authenticate($this->session->getCustomerId())) {
            $resultRedirect->setPath('*/vendor/');
            return $resultRedirect;
        }
        
        if (!$this->authenticate($this)) { $this->_actionFlag->set('', 'no-dispatch', true); 
        }
        $this->session->unsVendorId();
        $this->session->unsVendor();
        if($this->session->isLoggedIn()) {
        
            $vendor = $this->vendor->create()->loadByCustomerId($this->session->getCustomerId());
            if($vendor && $vendor->getId()) {
                $this->session->setData('vendor_id', $vendor->getId());
                $this->session->setData('vendor', $vendor->getData());
            }
        }
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->set(__('Account Approval'));
        return $resultPage;
    }
}
