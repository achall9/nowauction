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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vproducts\Edit\Downloadable;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Link extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $pricingHelper;

    /**
     * Link constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Downloadable\Model\Product\Type $type
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Downloadable\Model\Product\Type $type,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_localeCurrency = $localeCurrency;
        $this->type = $type;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }


    /**
     * @param $_product
     * @return \Magento\Downloadable\Model\Link[]
     */
    public function getDownloadableProductLinks($_product){
        return $this->type->getLinks($_product);
    }

    /**
     * @param $_product
     * @return bool
     */
    public function getDownloadableHasLinks($_product){
        return $this->type->hasLinks($_product);
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve curency name by code
     *
     * @param string $code
     * @return string
     */
    public function getCurrencySymbol($code)
    {
        $currency = $this->_localeCurrency->getCurrency($code);
        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

}
