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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Vshops;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $aclHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Helper\Data $dataHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Helper\Data $dataHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->aclHelper = $aclHelper;
        $this->dataHelper = $dataHelper;
        $this->vendorFactory = $vendorFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }
    
    protected function _initVendor()
    {    
        $this->_eventManager->dispatch('csmarketplace_controller_vshops_init_before', array('controller_action' => $this));
        
        if(!$this->aclHelper->isEnabled()) {
            return false; 
        }
        
        $shopUrl = $this->getRequest()->getParam('shop_url');
        if (!strlen($shopUrl)) {
            return false;
        }
        $storeId = $this->dataHelper->getStore()->getId();
        
        $vendor = $this->vendorFactory->create()
                    ->setStoreId($storeId)->loadByAttribute('shop_url', $shopUrl);
        
        if (!$this->dataHelper->canShow($vendor)) {
            return false;
        } else if(!$this->dataHelper->isShopEnabled($vendor)) {
            return false;
        }
        $this->_coreRegistry->register('current_vendor', $vendor);
     
        try {
            $this->_eventManager->dispatch(
                'csmarketplace_controller_vshops_init_after',
                array(
                'vendor' => $vendor,
                'controller_action' => $this
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Invalid login or password.'));
        } 
        return $vendor;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if (isset($data['product_list_dir'])) { 
            $this->_coreRegistry->register('name_filter', $data['product_list_dir']);
        }
        if ($vendor = $this->_initVendor()) {
            $helper = $this->dataHelper;
            if ($this->_coreRegistry->registry('current_category') == null) {
                $category = $this->categoryFactory->create()
                            ->setStoreId($helper->getStore()->getId())
                            ->load($helper->getRootId());
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($vendor->getPublicName()." ".('Shop')));
            $resultPage->getConfig()->addBodyClass('page-products');
            return $resultPage;
        }
        $this->messageManager->addErrorMessage(__('The Vendor\'s Shop you are trying to access is not available at this moment.'));
        return $this->_redirect('*/*');
   }
}
