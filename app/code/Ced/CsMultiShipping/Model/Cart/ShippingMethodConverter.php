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
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Model\Cart;

/**
 * Class ShippingMethodConverter
 * @package Ced\CsMultiShipping\Model\Cart
 */
class ShippingMethodConverter extends \Magento\Quote\Model\Cart\ShippingMethodConverter
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * ShippingMethodConverter constructor.
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Helper\Data $taxHelper
     */
    public function __construct(
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendorFacory = $vendorFactory;
        $this->csmultishippingHelper = $csmultishippingHelper;
        parent::__construct($shippingMethodDataFactory, $storeManager, $taxHelper);
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param string $quoteCurrencyCode The quote currency code.
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel The rate model.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method data object.
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        /**
         * @var \Magento\Directory\Model\Currency $currency
         */
        if (!$this->csmultishippingHelper->isEnabled()) {
            return parent::modelToDataObject($rateModel, $quoteCurrencyCode);
        }
        $store = $this->storeManager->getStore();
        $currency = $store->getBaseCurrency();
        $errorMessage = $rateModel->getErrorMessage();
        $vendorId = 'admin';
        if ($rateModel->getCarrier() != 'vendor_rates') {
            $tmp = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $rateModel->getCode());
            $vendorId = isset($tmp[1]) ? $tmp[1] : "admin";
        }
        $vendor = $this->vendorFacory->create();
        if ($vendorId && $vendorId != "admin") {
            $vendor = $vendor->load($vendorId);
        }
        $carrier_title = $this->csmarketplaceHelper
            ->getStoreConfig('ced_csmultishipping/general/carrier_title', $store->getId());

        $title = $vendor->getId() ? $vendor->getPublicName() : $carrier_title;

        return $this->shippingMethodDataFactory->create()
            ->setCarrierCode($rateModel->getCarrier())
            ->setMethodCode($rateModel->getMethod())
            ->setCarrierTitle($title)
            ->setMethodTitle($rateModel->getMethodTitle())
            ->setAmount($currency->convert($rateModel->getPrice(), $quoteCurrencyCode))
            ->setBaseAmount($rateModel->getPrice())
            ->setAvailable(empty($errorMessage))
            ->setErrorMessage(empty($errorMessage) ? false : $errorMessage)
            ->setPriceExclTax($currency->convert($this->getShippingPriceWithFlag($rateModel, false), $quoteCurrencyCode))
            ->setPriceInclTax($currency->convert($this->getShippingPriceWithFlag($rateModel, true), $quoteCurrencyCode));

    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param bool $flag
     * @return float
     */
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }
}
