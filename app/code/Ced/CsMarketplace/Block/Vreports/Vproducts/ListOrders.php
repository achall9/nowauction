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
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vreports\Vproducts;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListOrders
 * @package Ced\CsMarketplace\Block\Vreports\Vproducts
 */
class ListOrders extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    protected $_filterCollection;

    /**
     * @var \Ced\CsMarketplace\Helper\Report
     */
	protected $reportHelper;

    public $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * ListOrders constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Block\Vproducts\Store\Switcher|null $_storeSwitcher
     * @param \Ced\CsMarketplace\Helper\Report $reportHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Block\Vproducts\Store\Switcher $_storeSwitcher = null,
        \Ced\CsMarketplace\Helper\Report $reportHelper,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->reportHelper = $reportHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

		$ordersCollection = [];
	    $reportHelper =	$this->reportHelper;
		$params = $this->session->getData('vproducts_reports_filter');
		if(isset($params) && $params != null){

			$productsCollection = $reportHelper->getVproductsReportModel($this->getVendor()->getId(), $params['from'], $params['to']);

				$this->_filtercollection = $productsCollection;
				$this->setVproductsReports($this->_filtercollection);
		}else{
			$this->setVproductsReports([]);
		}
	}

    /**
     * @param $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param null $currency
     * @return float
     */
    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ){
        return $this->priceCurrency->format(
            $amount,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }
		
}
		