<?php
namespace Ced\CustomizeAuction\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Ced\CustomizeAuction\Controller\Index
 */
class Delete extends \Magento\Framework\App\Action\Action
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

    private $logger;
    public $resultJsonFactory;
    protected $bidDetails;
    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\Auction\Helper\Data $helper,
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Auction\Model\BidDetailsFactory $bidDetails
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->session = $session;
        $this->logger = $logger;
        $this->resultFactory = $context->getResultFactory();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->bidDetails = $bidDetails;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultJson = $this->resultJsonFactory->create();
        try {
            $bidDetails = $this->bidDetails->create()->load($id)->delete();

            $this->messageManager->addSuccessMessage(__('You Successfully Deleted The Bid'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            $success = true;
            return $resultJson->setData([
                'success' => $success
            ]);

            //return $resultRedirect;


        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            $success = false;
            return $resultJson->setData([
                'success' => $success
            ]);
        }

    }

}
