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
 * @package   Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Ced\CsMessaging\Helper\Data;

class Vcompose extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        UrlInterface $urlBuilder,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\Session $vendorSession
    )
    {
        $this->session = $customerSession;
        $this->vendorSession = $vendorSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlBuilder = $urlBuilder;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if ($customer = $this->session->isLoggedIn()) {

            if (isset($params['vendor_id']) && $params['vendor_id'] == $this->vendorSession->getVendorId()) {
                $vendor = $this->vendorFactory->create();
                $vendor->loadByField('entity_id',$params['vendor_id']);
                return $this->_redirect($vendor->getVendorShopUrl());
            }


            if (!$this->csmarketplaceHelper->getStoreConfig(Data::IS_MESSAGING_ENABLED))
                return $this->_redirect('customer/account');


            $resultRedirect = $this->resultPageFactory->create();
            if ($this->getRequest()->getParam('admin'))
                $resultRedirect->getConfig()->getTitle()->set(__('Admin Compose'));
            else
                $resultRedirect->getConfig()->getTitle()->set(__('Vendor Compose'));

            return $resultRedirect;
        } else {
            if (isset($params['admin']))
                $parameters['admin'] = true;
            else
                $parameters['vendor_id'] = $params['vendor_id'];

            if (isset($params['product_id']))
            $parameters['product_id'] = $params['product_id'];
            $value = $this->session->setBeforeAuthUrl($this->urlBuilder->getUrl('csmessaging/customer/vcompose',$parameters));
            $this->_redirect('customer/account/login');
        }

    }
}
