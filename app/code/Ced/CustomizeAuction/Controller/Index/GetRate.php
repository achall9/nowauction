<?php

namespace Ced\CustomizeAuction\Controller\Index;

use \Magento\Tax\Model\Calculation\Rate;

class GetRate extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $_productRepositoryFactory;

    protected $_storeManager;

    protected $_priceCurrency;

    protected $resultPageFactory;

    protected $_currency;

    protected $scopeConfig;

    protected $shipconfig;

    protected $customerSession;

    protected $_paymentConfig;

    protected $api;

    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CustomizeAuction\Helper\Api $api,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->resultPageFactory  = $resultPageFactory;
        $this->_currency = $currency;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->api = $api;
        $this->vproduct = $vproducts;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        $productSku = $this->getRequest()->getParam('productSku');
        $addressId = $this->getRequest()->getParam('addressId');
        $productId = $this->getRequest()->getParam('productId');

        if($this->vproduct->getVendorIdByProduct($productId)){
            $vendorId = $this->vproduct->getVendorIdByProduct($productId);
        }
       // $vendorId = 6;
        $resource = $this->resourceConnection;
            $connection = $resource->getConnection();
        $winTable = $resource->getTableName('vendor_shippingmethods');

        $getData = "SELECT * FROM " . $winTable." WHERE vendor_id = ".$vendorId;
        $results = $connection->fetchAll($getData);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cartObject = $objectManager->create('Magento\Checkout\Model\Cart')->truncate();
        $cartObject->saveQuote();

        $cartId = $this->api->createCart();
        
        $order_filled = $this->api->addToCart($cartId, $productSku, 1);

        $cusData = array(
            'addressId' => $addressId
        );
        $getShippingMethods = $this->api->request('/carts/mine/estimate-shipping-methods-by-address-id', 'POST', json_encode($cusData));

        $allShippingMethods = json_decode($getShippingMethods, true);

        $shippingOpt = '';
        $shippingOpt .= 'Choose: <ul>';
        if($allShippingMethods)
        {
            //echo "<pre>"; print_r($allShippingMethods); die();
            $allowedMethods = array();
            foreach ($allShippingMethods as $method){

                
                foreach ($results as $key => $data){
                    if(isset($data['shipping_method'])){
                        $shippingMethod = json_decode($data['shipping_method'], true);
                        if($method['carrier_code'] != 'vendor_rates') {
                            $activeSelectedVal = $shippingMethod[$method['carrier_code'].'_active'];
                            if($activeSelectedVal == 1){
                                if(isset($shippingMethod[$method['carrier_code'].'_allowed_method']))
                                {
                                    foreach($shippingMethod[$method['carrier_code'].'_allowed_method'] as $val){
                                        $allowedMethods[] = $val; 
                                    }
                                } else {
                                    $allowedMethods[] = $method['carrier_code'];
                                }
                            }
                        }
                    }                    
                }             

                if(isset($method['method_title']) && $method['carrier_code'] != 'vendor_rates' && in_array($method["method_code"], $allowedMethods))
                {
                    $shippingMethodCode = array("method_code"=>$method["method_code"], "carrier_code"=>$method["carrier_code"]);
                    $shippingOpt .= '<li><input class="popup-radio" data-validate="{required:true}" type="radio" value='.json_encode($shippingMethodCode).' name="shipping-opt"/>'.$method['method_title'];

                    if($method['base_amount'] != '')
                    {   
                        $shippingOpt .= ' <span>'.$this->getCurrentCurrencySymbol().'</span><span class="carrier-opt-price">'.number_format($method['base_amount'], 2);
                    }      
                    $shippingOpt .= '</li>';
                }
            }
        }
        $shippingOpt .= '</ul>
        <script>
            require(["jquery","Magento_Catalog/js/price-utils"],function($, priceUtils) {
                $(".popup-radio").change(function(){
                    var carOptPrice = $(this).parent().find(".carrier-opt-price").text();
                    if(carOptPrice) {
                        var carrierTotal = parseFloat(parseFloat(carOptPrice) + parseFloat($("#total-auction-bid").val())).toFixed(2);
                        var carrierTotalFormat = formatNumber(carrierTotal);
                        $(".total-cost>.totalcostprice").text(carrierTotalFormat);
                        $("#shipping-amount").val(parseFloat(carOptPrice));
                        
                    } else {
                        var carrierTotalFormat = formatNumber($("#total-auction-bid").val());
                        $(".total-cost>.totalcostprice").text(carrierTotalFormat);
                        $("#shipping-amount").val(0);
                    }
                });
                function formatNumber(num) {
                  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
                }
            });
        </script>';

        $resultJson = $this->resultJsonFactory->create();
        //$htmlContent = 'Pass Html Content';
        $success = true;
        return $resultJson->setData([
            'html' => $shippingOpt,
            'success' => $success
        ]);
    }
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    } 

 }