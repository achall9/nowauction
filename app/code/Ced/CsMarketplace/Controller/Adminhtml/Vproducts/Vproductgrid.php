<?php
namespace Ced\CsMarketplace\Controller\Adminhtml\Vproducts;

class Vproductgrid extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Vproductgrid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        parent::__construct($context);

    }
	
	public function execute()
	{
			$redirectUrl = $this->_redirect->getRedirectUrl();
        $actionName = $this->getRequest()->getParam('vProductFilter');
			if($actionName == 'pending'){
				$this->registry->register('usePendingProductFilter', true);
			}
			elseif($actionName == 'approved'){
				$this->registry->register('useApprovedProductFilter', true);
			}
			$resultPage = $this->resultPageFactory->create();
			
			return $resultPage;
		
	}
}
