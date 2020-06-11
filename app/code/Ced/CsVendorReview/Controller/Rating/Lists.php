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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Controller\Rating;

use Magento\Framework\App\Action\Context;

/**
 * Class Lists
 * @package Ced\CsVendorReview\Controller\Rating
 */
class Lists extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsVendorReview\Helper\Data
     */
    protected $csVendorReviewHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultpageFactory;

    /**
     * Lists constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsVendorReview\Helper\Data $csVendorReviewHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultpageFactory
     * @param Context $context
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Framework\Registry $registry,
        \Ced\CsVendorReview\Helper\Data $csVendorReviewHelper,
        \Magento\Framework\View\Result\PageFactory $resultpageFactory,
        Context $context
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendor = $vendor;
        $this->registry = $registry;
        $this->csVendorReviewHelper = $csVendorReviewHelper;
        $this->resultpageFactory = $resultpageFactory;
        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->csmarketplaceHelper->getStore()->getId();
        $vendor = $this->vendor->setStoreId($storeId)->loadByAttribute('entity_id', $id);
        
        if (!$this->csmarketplaceHelper->canShow($vendor)) {
            return false;
        } elseif (!$this->csmarketplaceHelper->isShopEnabled($vendor)) {
            return false;
        }

        $this->registry->register('current_vendor', $vendor);
        
        $isAllowed = $this->csVendorReviewHelper->isCustomerAllowed();
        $checkVendorProducts = $this->csVendorReviewHelper->checkVendorProduct();
        if($isAllowed){
        	if(!$checkVendorProducts){
        		$this->messageManager->addErrorMessage(__('You cannot review this vendor as you have not purchsed any item from this vendor'));
        		$redirect =  $this->resultRedirectFactory->create();
        		return $redirect->setPath('csmarketplace/vshops');
        	}
        }

        $resultPage =  $this->resultpageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Customer Review'));

        return $resultPage;
    }
}
