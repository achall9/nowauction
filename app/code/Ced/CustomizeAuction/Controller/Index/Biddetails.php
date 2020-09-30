<?php
namespace Ced\CustomizeAuction\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Ced\CustomizeAuction\Controller\Index
 */
class Biddetails extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var
     */
    public $_resultForwardFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\Auction\Helper\Data $helper,
        \Magento\Customer\Model\Session $session
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        if(!$this->session->isLoggedIn()) {
            $this->session->setAfterAuthUrl($this->_url->getCurrentUrl());
            $this->session->authenticate();
        }

       /* $this->helper->closeAuction();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/winner1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('controllerindex'); */

        $resultPage->getConfig()->getTitle()->prepend(__('Auction'));
        $resultPage->getConfig()->getTitle()->prepend(__('My Auctions'));
        return $resultPage;
    }

}
