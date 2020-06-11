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

namespace Ced\CsMarketplace\Block\Vreports\Vorders;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class ListOrders
 * @package Ced\CsMarketplace\Block\Vreports\Vorders
 */
class ListOrders extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var array|bool
     */
    protected $_filtercollection;

    /**
     * @var \Ced\CsMarketplace\Helper\Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public $customerSession;

    /**
     * Set the Vendor object and Vendor Id in customer session
     * ListOrders constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Report $reportHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Report $reportHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->reportHelper = $reportHelper;
        $this->priceCurrency = $priceCurrency;
        $this->customerSession = $customerSession;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $reportHelper =	$this->reportHelper;
        $params = $this->session->getData('vorders_reports_filter');

        if (isset($params) && $params != null) {
            $ordersCollection = $reportHelper->getVordersReportModel($this->getVendor(), $params['period'], $params['from'], $params['to'], $params['payment_state']);

            if(count($ordersCollection) > 0){
                $this->_filtercollection = $ordersCollection;
                $this->setVordersReports($this->_filtercollection);
            }
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