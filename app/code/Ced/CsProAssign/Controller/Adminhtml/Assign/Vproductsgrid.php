<?php
namespace Ced\CsProAssign\Controller\Adminhtml\Assign;

class Vproductsgrid extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

    /**
     * Vproductsgrid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	
	public function execute()
	{
			$resultPage = $this->resultPageFactory->create();
			return $resultPage;
		
	}
}
