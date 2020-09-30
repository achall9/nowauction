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
 * @package     Ced_Auction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Controller\Adminhtml\Winner;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory,
                                \Ced\Auction\Helper\Data $helper)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Auction');
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(__('All'), __('Winners'));
        $resultPage->getConfig()->getTitle()->prepend(__('Winners'));
        $this->helper->closeAuction();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/winner3.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('controllerWinner');


        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}