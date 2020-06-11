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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class VendorSaveBefore
 * @package Ced\CsMultiShipping\Observer
 */
Class VendorSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $csmultishippingHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * VendorSaveBefore constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Magento\Directory\Helper\Data $directoryHelper
    )
    {
        $this->_quoteFactory = $quoteFactory;
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->csmultishippingHelper->isEnabled()) {
            $vorder = $observer->getEvent()->getvorder();
            if (!$vorder->getId()) {
                $order = $vorder->getOrder();
                $quoteId = $order->getQuoteId();
                if ($quoteId) {
                    $quote = $this->_quoteFactory->create()->load($quoteId);
                    if ($quote && $quote->getId()) {
                        $addresses = $quote->getAllShippingAddresses();
                        foreach ($addresses as $address) {
                            if ($address) {
                                $shippingMethod = $address->getShippingMethod();
                                if (substr($shippingMethod, 0, 12) == 'vendor_rates') {
                                    $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
                                }
                                $shippingMethods = explode(\Ced\CsMultiShipping\Model\Shipping::METHOD_SEPARATOR, $shippingMethod);
                                $vendorId = 0;
                                foreach ($shippingMethods as $method) {
                                    $rate = $address->getShippingRateByCode($method);
                                    $methodInfo = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $method);
                                    if (sizeof($methodInfo) != 2) {
                                        continue;
                                    }
                                    $vendorId = isset($methodInfo [1]) ? $methodInfo[1] : "admin";

                                    if ($vendorId == $vorder->getVendorId()) {
                                        $vorder->setShippingAmount($this->directoryHelper->currencyConvert($rate->getPrice(), $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()));
                                        $vorder->setBaseShippingAmount($rate->getPrice());
                                        $vorder->setCarrier($rate->getCarrier());
                                        $vorder->setCarrierTitle($rate->getCarrierTitle());
                                        $vorder->setMethod($rate->getMethod());
                                        $vorder->setMethodTitle($rate->getMethodTitle());
                                        $vorder->setCode($method);
                                        $vorder->setShippingDescription($rate->getCarrierTitle() . "-" . $rate->getMethodTitle());
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }


}
