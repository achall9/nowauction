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
 * @package     Ced_CsMessaging
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Adminhtml\Vcustomer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Chat
 * @package Ced\CsMessaging\Controller\Adminhtml\Admin
 */
class Chat extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_vcustomerFactory = $vcustomerFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultPageFactory->create();
        $id = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        $threadFactory = $this->_vcustomerFactory->create();
        $threadData = $threadFactory->load($id);
        $resultRedirect->getConfig()->getTitle()->set($threadData->getSubject());
        return $resultPage;
    }


}
