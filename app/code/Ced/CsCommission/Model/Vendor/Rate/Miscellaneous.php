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
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Model\Vendor\Rate;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class Miscellaneous
 * @package Ced\CsCommission\Model\Vendor\Rate
 */
class Miscellaneous extends \Ced\CsMarketplace\Model\Vendor\Rate\Abstractrate
{
    /**
     * @var int
     */
    protected $base_fee = 0;
    /**
     * @var int
     */
    protected $fee = 0;
    /**
     * @var int|\Magento\Framework\Registry
     */
    protected $coreRegistry = 0;
    /**
     * @var int|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig = 0;

    /**
     * Miscellaneous constructor.
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Ced\CsCommission\Helper\Category $commissionCategoryHelper
     * @param \Ced\CsCommission\Helper\Product $commissionProductHelper
     * @param \Ced\CsCommission\Helper\Data $commissionHelper
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Helper\Data $directoryHelper,
        \Ced\CsCommission\Helper\Category $commissionCategoryHelper,
        \Ced\CsCommission\Helper\Product $commissionProductHelper,
        \Ced\CsCommission\Helper\Data $commissionHelper,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $storeManager,
            $request,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->directoryHelper = $directoryHelper;
        $this->categoryHelper = $commissionCategoryHelper;
        $this->producHelper = $commissionProductHelper;
        $this->helper = $commissionHelper;
        $this->quoteItem = $quoteItem;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->product = $product;
    }

    /**
     * @param $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getMiscellaneousConditions($vendorId)
    {
        if ($this->coreRegistry->registry('current_order_vendor')) {
            $this->coreRegistry->unregister('current_order_vendor');
        }
        $order = $this->getOrder();

        $this->coreRegistry->register('current_order_vendor', $vendorId);

        $categoryWise = $this->categoryHelper->getUnserializedOptions($vendorId, $order->getStoreId());
        $productTypes = $this->producHelper->getUnserializedOptions($vendorId, $order->getStoreId());
        //Customize code to get sales, ship, payments & service tax
        $salesCalMethod = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_mode_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $salesRate = $this->scopeConfig->getValue('ced_vpayments/general/commission_fee_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $shipCalMethod = $this->scopeConfig->getValue('ced_vpayments/general/commission_mode_ship',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $shipRate = $this->scopeConfig->getValue('ced_vpayments/general/commission_fee_ship',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $paymentCalMethod = $this->scopeConfig->getValue('ced_vpayments/general/commission_mode_payments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $paymentRate = $this->scopeConfig->getValue('ced_vpayments/general/commission_fee_paymnets',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $servicetaxCalMethod = $this->scopeConfig->getValue('ced_vpayments/general/commission_mode_servicetax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());
        $servicetaxRate = $this->scopeConfig->getValue('ced_vpayments/general/commission_fee_servicetax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId());

        return [
            $productTypes,
            $categoryWise,
            $salesCalMethod,
            $salesRate,
            $shipCalMethod,
            $shipRate,
            $paymentCalMethod,
            $paymentRate,
            $servicetaxCalMethod,
            $servicetaxRate
        ];
    }

    /**
     * Get the commission based on group
     *
     * @param float $grand_total
     * @param float $base_grand_total
     * @param string $base_to_global_rate
     * @param array $commissionSetting
     * @return array
     */
    public function calculateCommission(
        $grand_total = 0,
        $base_grand_total = 0,
        $base_to_global_rate = 1,
        $commissionSetting = []
    )
    {
        try {
            $result = [];
            $order = $this->getOrder();

            $vendorId = $this->getVendorId();
            $result['base_fee'] = 0;
            $result['fee'] = 0;
            $salesCost = 0;
            $shipCost = 0;
            $paymentCost = 0;
            $serviceTaxCost = 0;
            $productTypes = [];
            $categoryWise = [];

            list(
                $productTypes,
                $categoryWise,
                $salesCalMethod,
                $salesRate,
                $shipCalMethod,
                $shipRate,
                $paymentCalMethod,
                $paymentRate,
                $servicetaxCalMethod,
                $servicetaxRate
                ) = $this->_getMiscellaneousConditions($vendorId);

            $itemCommission = isset($commissionSetting['item_commission']) ?
                $commissionSetting['item_commission'] : [];

            $customTotalPrice = 0;
            foreach ($itemCommission as $key => $itemPrice) {
                $customTotalPrice = $customTotalPrice + $itemPrice;
            }

            $salesCost = $this->helper->calculateFee($customTotalPrice, $salesRate, $salesCalMethod);
            $custom_base_fee = $salesCost;
            $custom_fee = $this->directoryHelper->currencyConvert(
                $custom_base_fee,
                $order->getBaseCurrencyCode(),
                $order->getGlobalCurrencyCode()
            );

            if (!empty($productTypes) || !empty($categoryWise)) {
                $item_commission = [];
                foreach ($order->getAllItems() as $item) {
                    if (!(isset($itemCommission[$item->getQuoteItemId()]))) {
                        continue;
                    }

                    if ($item->getVendorId() && $item->getVendorId() == $vendorId) {
                        $temp_base_fee = 0;
                        $temp_fee = 0;
                        $product_temp_priority = [];
                        $category_temp_priority = [];

                        $product = $this->product->load($item->getProductId());
                        $productTypeId = (string)$product->getTypeId();


                        if (is_array($product->getCategoryIds())) {
                            $productCategoriesIds = (array)$product->getCategoryIds();
                        } else {
                            $productCategoriesIds = explode(',', trim((string)$product->getCategoryIds()));
                        }
                        $productCategoriesIds = (array)$productCategoriesIds;
                        if (isset($productTypes[$productTypeId])) {
                            $product_temp_priority = $productTypes[$productTypeId];
                        }
                        foreach ($categoryWise as $id => $condition) {
                            $categoryId = isset($condition['category']) &&
                            (int)$condition['category'] ? (int)$condition['category'] : 0;

                            if (!$categoryId) {
                                continue;
                            }

                            if (in_array($categoryId, $productCategoriesIds)) {

                                if (!isset($category_temp_priority['priority']) ||
                                    (isset($category_temp_priority['priority']) &&
                                        (int)$category_temp_priority['priority'] > (int)$condition['priority']
                                    )
                                ) {
                                    $category_temp_priority = $condition;
                                }
                            }
                        }

                        if (!isset($category_temp_priority['priority']) && isset($categoryWise['all'])) {
                            $category_temp_priority = $categoryWise['all'];
                        }

                        /* Calculation starts for fee calculation */
                        /* START */

                        $pt = isset($product_temp_priority['fee']) ? $product_temp_priority['fee'] : 0;
                        $cw = isset($category_temp_priority['fee']) ? $category_temp_priority['fee'] : 0;

                        if ($product->getTypeId() == 'bundle') {
                            if (!empty($category_temp_priority) || !empty($product_temp_priority)) {
                                $bundleSelections = $item->getProductOptions();
                                $bundle_qty = 0;
                                foreach ($bundleSelections['bundle_options'] as $bundle_item) {
                                    $bundle_qty += $bundle_item['value'][0]['qty'];
                                }
                                if (!empty($category_temp_priority)) {
                                    if ($category_temp_priority['method'] == 'fixed') {
                                        $cw *= $bundle_qty;
                                    }
                                }
                                if (!empty($product_temp_priority)) {
                                    if ($product_temp_priority['method'] == 'fixed') {
                                        $pt *= $bundle_qty;
                                    }
                                }
                            }
                        }

                        $pt = isset($product_temp_priority['method']) ?
                            $this->helper->calculateCommissionFee(
                                $itemCommission[$item->getQuoteItemId()],
                                $pt,
                                $item->getQtyOrdered(),
                                $product_temp_priority['method']
                            ) :
                            $this->helper->calculateCommissionFee(
                                $itemCommission[$item->getQuoteItemId()],
                                $pt,
                                $item->getQtyOrdered(),
                                $this->scopeConfig->getValue('ced_vpayments/general/commission_mode_default')
                            );
                        $cw = isset($category_temp_priority['method']) ?
                            $this->helper->calculateCommissionFee(
                                $itemCommission[$item->getQuoteItemId()],
                                $cw,
                                $item->getQtyOrdered(),
                                $category_temp_priority['method']
                            ) : $this->helper->calculateCommissionFee(
                                $itemCommission[$item->getQuoteItemId()],
                                $cw,
                                $item->getQtyOrdered(),
                                $this->scopeConfig->getValue('ced_vpayments/general/commission_mode_default')
                            );

                        $cf = $this->scopeConfig->getValue(
                            'v' . $vendorId . '/ced_vpayments/general/commission_fn',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        );
                        if (null === $cf && !isset($cf)) {
                            $cf = $this->scopeConfig->getValue(
                                'ced_vpayments/general/commission_fn',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $order->getStoreId()
                            );
                        }
                        switch ($cf) {
                            case \Ced\CsCommission\Model\Source\Vendor\Rate\Aggregrade::TYPE_MIN:
                                if ($pt && $cw) {
                                    $temp_base_fee = min($pt, $cw);
                                } else {
                                    if ($cw) {
                                        $temp_base_fee = $cw;
                                    } else {
                                        $temp_base_fee = $pt;
                                    }
                                }

                                if (!$temp_base_fee) {
                                    $temp_base_fee = $custom_base_fee;
                                }

                                $temp_fee = $this->directoryHelper->currencyConvert(
                                    $temp_base_fee,
                                    $order->getBaseCurrencyCode(),
                                    $order->getGlobalCurrencyCode()
                                );
                                break;
                            case \Ced\CsCommission\Model\Source\Vendor\Rate\Aggregrade::TYPE_MAX:
                            default:
                                if ($pt && $cw) {
                                    $temp_base_fee = max($pt, $cw);
                                } else {
                                    if ($cw) {
                                        $temp_base_fee = $cw;
                                    } else {
                                        $temp_base_fee = $pt;
                                    }
                                }
                                if (!$temp_base_fee) {
                                    $temp_base_fee = $custom_base_fee;
                                }
                                $temp_fee = $this->directoryHelper->currencyConvert(
                                    $temp_base_fee,
                                    $order->getBaseCurrencyCode(),
                                    $order->getGlobalCurrencyCode()
                                );
                                break;
                        }

                        /* END */
                        $quoteItem = $this->quoteItem->load($item->getQuoteItemId())->getData();

                        if (!isset($quoteItem['parent_item_id'])) {
                            $result['base_fee'] = ($result['base_fee'] + $temp_base_fee);
                            $result['fee'] = $result['fee'] + $temp_fee;
                            $item_commission[$item->getQuoteItemId()] = [
                                'base_fee' => $temp_base_fee,
                                'fee' => $temp_fee
                            ];
                        } else {
                            $parentItemId = $quoteItem['parent_item_id'];
                            $parentQuote = $this->quoteItem->load($parentItemId)->getData();
                            if ($parentQuote['product_type'] == 'bundle') {
                                $result['base_fee'] = ($result['base_fee'] + $temp_base_fee);
                                $result['fee'] = $result['fee'] + $temp_fee;
                                $item_commission[$item->getQuoteItemId()] = [
                                    'base_fee' => $temp_base_fee,
                                    'fee' => $temp_fee
                                ];
                            }
                        }

                    }
                }
                $totalBaseFeeCommisionExludeServiceTax = $result['base_fee'];
                $serviceTaxCost = $this->helper->calculateFee(
                    $totalBaseFeeCommisionExludeServiceTax,
                    $servicetaxRate,
                    $servicetaxCalMethod
                );
                $totalBaseFeeCommisionIncludeServiceTax = $totalBaseFeeCommisionExludeServiceTax + $serviceTaxCost;

                $finalCommision = min($totalBaseFeeCommisionIncludeServiceTax, $customTotalPrice);
                $result['base_fee'] = $finalCommision;
                $result['fee'] = $this->directoryHelper->currencyConvert(
                    $finalCommision,
                    $order->getBaseCurrencyCode(),
                    $order->getGlobalCurrencyCode()
                );
                $result['item_commission'] = json_encode($item_commission);
            } else {
                $result['base_fee'] = $custom_base_fee;
                $result['fee'] = $custom_fee;
            }

            $this->coreRegistry->unregister('current_order_vendor');
            return $result;
        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }
    }
}
