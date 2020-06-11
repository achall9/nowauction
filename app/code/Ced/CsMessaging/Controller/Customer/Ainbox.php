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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Ced\CsMessaging\Helper\Data;

/**
 * Class Ainbox
 * @package Ced\CsMessaging\Controller\Customer
 */
class Ainbox extends \Magento\Framework\App\Action\Action
{
    /**
     * Ainbox constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if($customer = $this->customerSession->isLoggedIn()) {

            if (!$this->csmarketplaceHelper->getStoreConfig(Data::IS_MESSAGING_ENABLED))
                return $this->_redirect('customer/account');

            $resultRedirect = $this->resultPageFactory->create();
            $resultRedirect->getConfig()->getTitle()->set(__('Admin Inbox'));
            return $resultRedirect;
        }
        else{
            $this->_redirect('customer/account/login');
        }
    }
}
