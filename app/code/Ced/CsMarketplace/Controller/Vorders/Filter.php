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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Vorders;

class Filter extends \Ced\CsMarketplace\Controller\Vorders
{
    /**
     * Filter constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\Session $mktSession
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\Session $mktSession,
        \Ced\CsMarketplace\Model\Vorders $vorders
    ) {
    
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor, $mktSession, $vorders);
    }
    
    public function execute()
    {   
        if(!$this->_getSession()->getVendorId()){ 
            return; 
        }
        $reset_filter = $this->getRequest()->getParam('reset_order_filter');
        $params = $this->getRequest()->getParams();
        if($reset_filter == 1)
            $this->_getSession()->uns('order_filter');
        else if(!isset($params['p']) && !isset($params['limit']) &&  is_array($params) ){
            $this->_getSession()->setData('order_filter',$params);
        }
        $block = $this->_view->getLayout()
                ->createBlock('Ced\CsMarketplace\Block\Vorders\ListOrders')
                ->setTemplate('Ced_CsMarketplace::vorders/list.phtml')
                ->toHtml();

        $this->getResponse()->setBody($block);
    }
}
