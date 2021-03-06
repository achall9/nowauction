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
  * @category  Ced
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsOrder\Controller\Vorders;

/**
 * Class Index
 * @package Ced\CsOrder\Controller\Vorders
 */
class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;
 
    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {    
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Orders List'));
        return $resultPage;
    }
}
 
