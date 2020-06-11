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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;


/**
 * Class View
 * @package Ced\CsOrder\Controller\Vorders
 */
class View extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     *
     *
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $vordersCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession, UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
    )
    {
        $this->registry = $registry;
        $this->vorders = $vorders;
        $this->customerSession = $customerSession;
        $this->vordersCollection = $vordersCollection;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * @param null $orderId
     * @param bool $viewMode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _loadValidOrder($orderId = null, $viewMode = false)
    {

        $register = $this->registry;
        if (null === $orderId) {
            $orderId = (int)$this->getRequest()->getParam('order_id', 0);
        }
        $incrementId = 0;
        if ($orderId == 0) {
            $incrementId = (int)$this->getRequest()->getParam('increment_id', 0);
            if (!$incrementId) {
                $this->_forward('noRoute');
                return false;
            }
        }

        if ($orderId) {

            $vorder = $this->vorders->load($orderId);
        } else if ($incrementId) {

            $vendorId = $this->customerSession->getVendorId();
            $vorder = $this->vorders->loadByField(array('order_id', 'vendor_id'), array($incrementId, $vendorId));
        }

        //add view mode for shipping method
        $order = $vorder->getOrder(false, $viewMode);

        if ($this->_canViewOrder($vorder)) {
            $register->register('current_order', $order);
            $register->register('sales_order', $order);
            $register->register('current_vorder', $vorder);
            return true;
        } else {
            $this->_redirect('csorder/vorders');
        }
        return false;
    }

    /**
     * @param $vorder
     * @return bool
     */
    protected function _canViewOrder($vorder)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $vendorId = $this->customerSession->getVendorId();

        $incrementId = $vorder->getOrder()->getIncrementId();


        $collection = $this->vordersCollection->create();
        $collection->addFieldToFilter('id', $vorder->getId())
            ->addFieldToFilter('order_id', $incrementId)
            ->addFieldToFilter('vendor_id', $vendorId);

        if (count($collection) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return PageFactory
     */
    public function execute()
    {
        $register = $this->registry;

        if (!$this->_loadValidOrder(null, true)) {
            return false;
        }
        $helper = $this->csmarketplaceHelper;
        $helper->readNotification($this->getRequest()->getParam('order_id', 0));
        $order = $register->registry('current_order');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order') . ' # ' . $order->getRealOrderId());
        return $resultPage;

    }
}
 
