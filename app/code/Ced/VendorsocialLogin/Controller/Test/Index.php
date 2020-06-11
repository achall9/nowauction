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
  * @package     VendorsocialLogin
  * @author      CedCommerce Core Team <connect@cedcommerce.com>
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\VendorsocialLogin\Controller\Test;
use \Magento\Customer\Model\ResourceModel\Customer;

class Index extends \Magento\Framework\App\Action\Action
{
	
	protected $_modelNewsFactory;
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
								\Magento\Customer\Model\ResourceModel\Customer $modelNewsFactory
								)
    {
        $this->resultPageFactory = $resultPageFactory;
		$this->_modelNewsFactory = $modelNewsFactory;
        parent::__construct($context);
    }
    public function execute()
    {
		$Collection = $this->_modelNewsFactory->create();
		print_r($Collection->getCollection());
		echo "Test Index controller";die;
    }
}
