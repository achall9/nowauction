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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Tablerate extends \Magento\OfflineShipping\Model\Carrier\Tablerate
{
    
    /**
     * @var string
     */
    protected $_code = 'tablerate';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected $_defaultConditionName = 'package_weight';

    /**
     * @var array
     */
    protected $_conditionNames = [];

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_resultMethodFactory;

    public function __construct(
        \Ced\CsMultiShipping\Helper\Data $multishippingHelper,
        \Ced\CsTableRateShipping\Helper\Data $tablerateHelper,
        \Ced\CsTableRateShipping\Model\Resource\Carrier\TablerateFactory $tablerate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->multishippingHelper = $multishippingHelper;
        $this->tablerateHelper = $tablerateHelper;
        $this->tablerate = $tablerate;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $resultMethodFactory, $tablerateFactory, $data);
        foreach ($this->getCode('condition_name') as $k => $v) {
            $this->_conditionNames[] = $k;
        }
    }
    
    public function collectRates(RateRequest $request)
    {
        if(!$this->_scopeConfig->getValue('carriers/tablerate/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return; 
        }
        
        if(!$this->multishippingHelper->isEnabled()) {
            return parent::collectRates($request);   
        }

        if(!$this->tablerateHelper->isEnabled()) {
            return parent::collectRates($request); 
        }

        $vendorId = $request->getVendorId();
        if(!$vendorId) {
            return; 
        }
        if($vendorId!="admin") {
            $freeSpecificConfig = $request->getVendorShippingSpecifics(); 
        }
        if($vendorId!="admin") {

            if (!$this->getConfigFlag('include_virtual_price') && $request->getVendorItems()) {
                foreach ($request->getVendorItems() as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }
                    if ($item->getHasChildren() && $item->isShipSeparately()) {
                        foreach ($item->getChildren() as $child) {
                            if ($child->getProduct()->isVirtual()) {
                                $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                            }
                        }
                    } elseif ($item->getProduct()->isVirtual()) {
                        $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                    }
                }
            }
            // Free shipping by qty
            $freeQty = 0;
            if ($request->getVendorItems()) {
                $freePackageValue = 0;
                foreach ($request->getAllItems() as $item) {
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }
                    if ($item->getHasChildren() && $item->isShipSeparately()) {
                        foreach ($item->getChildren() as $child) {
                            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                                $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                                $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                            }
                        }
                    } elseif ($item->getFreeShipping()) {
                        $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                        $freeQty += $item->getQty() - $freeShipping;
                        $freePackageValue += $item->getBaseRowTotal();
                    }
                }
                $oldValue = $request->getPackageValue();
                $request->setPackageValue($oldValue - $freePackageValue);
            }
            if ($freePackageValue) {
                $request->setPackageValue($request->getPackageValue() - $freePackageValue);
            }

            $setting = $freeSpecificConfig['condition'];
            if (!$request->getConditionName()) {
                $conditionName = $setting;
                $request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);
            }
            // Package weight and qty free shipping
            $oldWeight = $request->getPackageWeight();
            $oldQty = $request->getPackageQty();
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
            $result =  $this->_rateResultFactory->create();
            $rate = $this->getRate($request);

            //var_dump($rate);die;
            $error_msg = $this->_scopeConfig->getValue('carriers/tablerate/specificerrmsg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $method_title = $this->_scopeConfig->getValue('carriers/tablerate/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            $request->setPackageWeight($oldWeight);
            $request->setPackageQty($oldQty);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method =  $this->_rateMethodFactory->create();
                $vendorId = $request->getVendorId();
                if($vendorId) {
                    $method->setVendorId($vendorId); 
                }
                else {
                    $method->setVendorId("admin"); 
                }
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_code);
                if($vendorId) {
                    $custom_method = $this->_code.\Ced\CsMultiShipping\Model\Shipping::SEPARATOR.$vendorId; 
                }
                else {
                    $custom_method = $this->_code; 
                }
            
                $method->setMethod($custom_method);
                $method->setMethodTitle($method_title);
            
                if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                    $shippingPrice = 0;
                } else {
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                }
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                $result->append($method);
            } elseif (empty($rate) && $request->getFreeShipping() === true) {
                $request->setPackageValue($freePackageValue);
                $request->setPackageQty($freeQty);
                $rate = $this->getRate($request);
                
                $request->setPackageWeight($oldWeight);
                $request->setPackageQty($oldQty);
                if (!empty($rate) && $rate['price'] >= 0) {
                    $method =  $this->_rateMethodFactory->create();
                    if($vendorId) {
                        $method->setVendorId($vendorId); 
                    }
                    else {
                        $method->setVendorId("admin"); 
                    }
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->_code);
                    if($vendorId) {
                        $custom_method = $this->_code.\Ced\CsMultiShipping\Model\Shipping::SEPARATOR.$vendorId; 
                    }
                    else {
                        $custom_method = $this->_code; 
                    }
            
                    $method->setMethod($custom_method);
                    $method->setMethodTitle($method_title);
            
                    if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                        $shippingPrice = 0;
                    } else {
                        $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                    }
            
                    $method->setPrice($shippingPrice);
                    $method->setCost($shippingPrice);
            
                    $result->append($method);
                }
            } else {
     
                $error = $this->_rateErrorFactory->create(
                    [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->_code,
                        'error_message' => $error_msg,
                    ],
                    ]
                );
                $result->append($error);
            }     
            return $result;
        }
        else{
            return parent::collectRates($request);
        }
        return $result;
    }
    
    public function getCode($type, $code = '')
    {
        $codes = array(
        'condition_name' => array(
                        'package_weight' => __('Weight vs. Destination'),
                        'package_value' => __('Price vs. Destination'),
                        'package_qty' => __('# of Items vs. Destination'),
        ),
        'condition_name_short' => array(
                        'package_weight' => __('Weight (and above)'),
                        'package_value' => __('Order Subtotal (and above)'),
                        'package_qty' => __('# of Items (and above)'),
        ),
        );
        if (!isset($codes[$type])) {
            throw new LocalizedException(__('Please correct Table Rate code type: %1.', $type));
        }
        if ('' === $code) {
            return $codes[$type];
        }
        if(is_array($code)){
           $code = 'package_weight';
        }
        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(__('Please correct Table Rate code for type %1: %2.', $type, $code));
        }
        return $codes[$type][$code];
    }

    
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        return $this->tablerate->create()->getRate($request);
    }

}
