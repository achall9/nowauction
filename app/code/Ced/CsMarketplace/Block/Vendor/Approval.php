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

namespace Ced\CsMarketplace\Block\Vendor;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Approval
 * @package Ced\CsMarketplace\Block\Vendor
 */
class Approval extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Model\Url
     */
    public $_vendorUrl;

    /**
     * @var UrlFactory
     */
    protected $urlModel;

    /**
     * Approval constructor.
     * @param \Ced\CsMarketplace\Model\Url $vendorUrl
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Url $vendorUrl,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    ){
        $this->_vendorUrl = $vendorUrl;
        $this->urlModel = $urlFactory;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_vendorUrl->getBaseUrl();
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_vendorUrl->getLogoutUrl();
    }

    /**
     * Approval message
     *
     * @return String
     */
    public function getApprovalMessage() {
        $message = '';
        if ($this->getVendorId()) {
            switch ($this->getVendor()->getStatus()) {
                case \Ced\CsMarketplace\Model\Vendor::VENDOR_DISAPPROVED_STATUS : $message .= __('Your vendor account has been Disapproved.'); break;
                default : $message .= __('You will recieve an email once your account is reviewed.'); break;
            }
        } else {
            $message .= __('Please fill this form to create your vendor account.');
        }
        return $message;
    }
}
