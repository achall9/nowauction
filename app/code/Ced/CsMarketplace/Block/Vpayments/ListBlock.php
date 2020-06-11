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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vpayments;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class ListBlock extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_acl;

    /**
     * ListBlock constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Acl $acl
    )
    {
        $this->_acl = $acl;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $payments = [];
        if ($vendorId = $this->getVendorId()) {
            $payments = $this->getVendor()->getVendorPayments()->setOrder('created_at', 'DESC');
            $payments = $this->filterPayment($payments);
        }
        $this->setVpayments($payments);
    }


    public function filterPayment($payment)
    {
        $params = $this->session->getData('payment_filter');
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if ($field == "__SID")
                    continue;
                if (is_array($value)) {
                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        if ($field == 'created_at') {
                            $from = date("Y-m-d 00:00:00", strtotime($from));
                        }

                        $payment->addFieldToFilter($field, array('gteq' => $from));
                    }
                    if (isset($value['to']) && urldecode($value['to']) != "") {
                        $to = urldecode($value['to']);
                        if ($field == 'created_at') {
                            $to = date("Y-m-d 59:59:59", strtotime($to));
                        }

                        $payment->addFieldToFilter($field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    if ($field == 'payment_method') {
                        $payment->addFieldToFilter($field, array("in" => $this->_acl->getDefaultPaymentTypeValue(urldecode($value))));
                    } else {
                        $payment->addFieldToFilter($field, array("like" => '%' . urldecode($value) . '%'));
                    }
                }

            }
        }
        return $payment;
    }

    /**
     * prepare list layout
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pagerblock = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Html\Pager', 'custom.pager');
        $pagerblock->setAvailableLimit([5 => 5, 10 => 10, 20 => 20, 'all' => 'all']);
        $pagerblock->setCollection($this->getVpayments());
        $this->setChild('pager', $pagerblock);
        $this->getVpayments()->load();
        return $this;
    }

    /**
     * return the pager
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * return Back Url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', ['_secure' => true, '_nosid' => true]);
    }

    /**
     * Return order view link
     *
     * @param string $order
     * @return String
     */
    public function getViewUrl($payment)
    {
        return $this->getUrl('*/*/view', array('payment_id' => $payment->getId(), '_secure' => true, '_nosid' => true));
    }

}
