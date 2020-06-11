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

/**
 * Class Vinbox
 * @package Ced\CsMessaging\Controller\Customer
 */
class Vinbox extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    )
    {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        if ($customer = $this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultPageFactory->create();
            $resultRedirect->getConfig()->getTitle()->set(__('Vendor Inbox'));
            return $resultRedirect;
        } else {
            $this->_redirect('customer/account/login');
        }
    }
}
