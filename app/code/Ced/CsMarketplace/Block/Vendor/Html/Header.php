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

namespace Ced\CsMarketplace\Block\Vendor\Html;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Ced\CsMarketplace\Model\Vproducts;

/**
 * Class Header
 * @package Ced\CsMarketplace\Block\Vendor\Html
 */
class Header extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var Vproducts
     */
    protected $_vproducts;

    /**
     * Header constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Vproducts $vproducts
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Vproducts $vproducts
    )
    {
        $this->_vproducts = $vproducts;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @return int
     */
    public function isPaymentDetailAvailable(){
		return count($this->getVendor()->getPaymentMethodsArray($this->getVendorId(),false));
	}
	
    /**
     * Get Product collection
     * @param $checkstatus string
     * @param $vendorId integer
     * @param $productId integer
     * @return \Ced\CsMarketplace\Model\ResourceModel\Vproducts\Collection
     */
    public function getVendorProducts($checkstatus = '', $vendorId = 0, $productId = 0){
        return $this->_vproducts->getVendorProducts($checkstatus, $vendorId, $productId);
	}
}
