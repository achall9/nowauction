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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Navigation extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var UrlFactory
     */
    public $urlFactory;

    /**
     * @var \Ced\CsMarketplace\Model\NotificationHandler
     */
    public $notificationHandler;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    public $vendor;

    /**
     * @var \Ced\CsMarketplace\Helper\Tool\Image
     */
    public $imageHelper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    public $layout;

    /**
     * Navigation constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\NotificationHandler $notificationHandler
     * @param \Ced\CsMarketplace\Helper\Tool\Image $imageHelper
     * @param \Magento\Framework\View\LayoutInterface $layoutInterface
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\NotificationHandler $notificationHandler,
        \Ced\CsMarketplace\Helper\Tool\Image $imageHelper,
        \Magento\Framework\View\LayoutInterface $layoutInterface
    )
    {
        $this->urlFactory = $urlFactory;
        $this->resultJsonFactory = $jsonFactory;
        $this->notificationHandler = $notificationHandler;
        $this->vendor = $vendor;
        $this->imageHelper = $imageHelper;
        $this->layout = $layoutInterface;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }


    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $result = [];
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {
            
        }
        else
        {
            $result['notifications'] = $this->notificationHandler->getNotifications();
            $vendorId = $this->_getSession()->getVendorId();
            $vendor = $this->vendor->create()->load($vendorId);
            $helper = $this->imageHelper;
            $block = $this->layout->createBlock('Ced\CsMarketplace\Block\Vendor\Navigation\Statatics','csmarketplace_vendor_navigation_statatics_header');
            $block->getVendorAttributeInfo();

            $percent = round(($block->getSavedAttr() * 100)/$block->getTotalAttr());
            $href = '#';
            $urlFactory = $this->urlFactory->create();
            if($vendorId && $vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS ){
                $href = $urlFactory->getUrl('csmarketplace/vendor/profile',array('_secure' => true));
            } 
            $result['statistics']['percent'] = $percent ;
            $result['statistics']['href'] = $href;
            $result['vendor']['profile_pic'] = $helper->getResizeImage($vendor->getData('profile_picture'), 'logo', 50, 50);
            $result['vendor']['is_approved'] = 0;
            $result['vendor']['name'] = $vendor->getName();
            if($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
                $result['vendor']['is_approved'] = 1;
                $result['vendor']['status'] = __('Approved');
                $result['vendor']['status_itag'] = 'fa fa-circle text-success';

            } elseif($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_DISAPPROVED_STATUS) {
                $result['vendor']['status'] = __('Disapproved');
                $result['vendor']['status_itag'] = 'fa fa-circle text-danger';
            } else {
                $result['vendor']['status'] = __('New');;
                $result['vendor']['status_itag'] = 'fa fa-circle text-warning';
            }
            $result['vendor']['profile_url'] = $urlFactory->getUrl('csmarketplace/vendor/profileview/',array('_secure'=>true));
            $result['vendor']['settings_url'] = $urlFactory->getUrl('csmarketplace/vsettings/',array('_secure'=>true));
            $result['vendor']['logout_url'] = $urlFactory->getUrl('csmarketplace/account/logout/',array('_secure'=>true)); 

            $result['vendor']['shop_url'] = $vendor->getVendorShopUrl();
        }
        $resultJson->setData($result);
        return $resultJson;
     }
}
