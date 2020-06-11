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

namespace Ced\CsMarketplace\Block\Vorders;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    public $_vendorUrl;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * View constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->paymentHelper = $paymentHelper;
        $this->registry = $registry;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }
	
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('vorders/view.phtml');
    }

    protected function _prepareLayout(){
		$this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $this->setChild(
            'payment_info',
            $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->session->isLoggedIn()) {
            return $this->getUrl('*/*/index', array('_secure'=>true, '_nosid'=>true));
        }
        return $this->getUrl('*/*/form', array('_secure'=>true, '_nosid'=>true));
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if ($this->session->isLoggedIn()) {
            return __('Orders');
        }
        return __('View Another Order');
    }

    public function getInvoiceUrl($order)
    {
        return $this->getUrl('*/*/invoice', array('order_id' => $order->getId(), '_secure'=>true, '_nosid'=>true));
    }

    public function getShipmentUrl($order)
    {
        return $this->getUrl('*/*/shipment', array('order_id' => $order->getId(), '_secure'=>true, '_nosid'=>true));
    }

    public function getCreditmemoUrl($order)
    {
        return $this->getUrl('*/*/creditmemo', array('order_id' => $order->getId(), '_secure'=>true, '_nosid'=>true));
    }

}
