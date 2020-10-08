<?php

namespace Ced\CustomizeAuction\Controller\Index;

use \Magento\Tax\Model\Calculation\Rate;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $_productRepositoryFactory;

    protected $_storeManager;

    protected $_priceCurrency;

    protected $resultPageFactory;

    protected $_currency;

    protected $scopeConfig;

    protected $shipconfig;

    private $taxCalculation;

    protected $customerSession;

    protected $_taxModelConfig;

    protected $csShipping;

    protected $_orderPayment;

    protected $_paymentHelper;

    protected $_paymentConfig;

    protected $api;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\Session $customerSession,
        Rate $taxModelConfig,
        \Ced\CsMultiShipping\Model\Source\Shipping\Methods $csShipping,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPayment,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Config $paymentConfig,
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer,
        \Ced\CustomizeAuction\Helper\Api $api
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->resultPageFactory  = $resultPageFactory;
        $this->_currency = $currency;
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
        $this->customerSession = $customerSession;
        $this->_taxModelConfig = $taxModelConfig;
        $this->csShipping = $csShipping;
        $this->vproduct = $vproducts;
        $this->_orderPayment = $orderPayment;
        $this->_paymentHelper = $paymentHelper;
        $this->_paymentConfig = $paymentConfig;
        $this->stripeCustomer = $stripeCustomer;
        $this->api = $api;
    }

    public function execute()
    {
        //echo "<pre>"; print_r($this->csShipping->toOptionArray()); die();
        // echo "<pre>"; print_r($this->stripeCustomer->getStripeId()); die();

        $productId = $this->getRequest()->getParam('id');
        $currentPrice = $this->getRequest()->getParam('currentPrice');

        $product = $this->_productRepositoryFactory->create()->getById($productId);

        $customer = $this->customerSession->getCustomer();
        $shippingAddress = $customer->getDefaultShippingAddress();
        $billingAddress = $customer->getDefaultBillingAddress();        

        $taxRate = '00';
        $taxRates = $this->_taxModelConfig->getCollection()->addFieldToFilter('tax_country_id',$shippingAddress->getCountryId())->addFieldToFilter('tax_postcode',$shippingAddress->getPostcode())->getData();
     
        if(isset($taxRates[0]['rate']))
        {
            $taxRate = $taxRates[0]['rate'];
        } else {
            $taxRates = $this->_taxModelConfig->getCollection()->addFieldToFilter('tax_country_id',$shippingAddress->getCountryId())->addFieldToFilter('tax_postcode','*')->getData();
            if(isset($taxRates[0]['rate'])){
                $taxRate = $taxRates[0]['rate'];
            }            
        }
               
        $currentStore = $this->_storeManager->getStore();

        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $image = $mediaUrl.'catalog/product'.$product->getData('thumbnail');
        $name = $product->getName();

        $resultPage = $this->resultPageFactory->create();
        $getBlock = $resultPage->getLayout()->createBlock("StripeIntegration\Payments\Block\Customer\Cards");
        $cards = $getBlock->getCards();    

        //echo "<pre>"; print_r($cards); die();   

        $vendorId = '';
        if($this->vproduct->getVendorIdByProduct($product->getId())){
            $vendorId = $this->vproduct->getVendorIdByProduct($product->getId());
        }

        $paymentMethods = $this->getActivePaymentMethods();

        $paymentBlock = '';
        $paymentBlock .= '<ul>';
        foreach ($paymentMethods as $payment){
            $paymentBlock .= '<li><input class="popup-payment-radio" data-validate="{required:true}" id="'.$payment['value'].'" type="radio" value="'.$payment['value'].'" name="payment-opt"/>'.$payment['label'];
 
            $paymentBlock .= '</li>';
        }
        $paymentBlock .= '</ul>';
        $paymentBlock .= '<div class="stripe-options stripe-card-details stripe_payments" style="display:none;">
            <input type="checkbox" id="use-saved-card" name="use_saved_card" value="1">
            <label for="use-saved-card"> Use Saved Card</label><br>';

            $paymentBlock .= '<div class="bid-saved-payment">
                    <label for="bid-selected-payment">Select form of Payment</label>
                    <select name="bid-selected-payment" id="bid-selected-payment" class="save-card-options" data-validate="{required:true}" > 
                    <option value="0">--- Please select ---</option>';
            if($cards)
            {
                foreach($cards as $card)
                {
                    $paymentBlock .= '<option value="'.$card->id.':'.$card->brand.':'.$card->last4.'">'.$getBlock->cardType($card->brand) .' - ****&nbsp;'. $card->last4.'</option>';
                }
            }
            $paymentBlock .= '</select></div>';

            $paymentBlock .= '<div class="payment-credit-opt"><input class="input-card-numbers" autocomplete="cc-number" autocorrect="off" spellcheck="false" name="cardnumber" id="cardnumber" inputmode="numeric" aria-label="Credit or debit card number" placeholder="1234 1234 1234 1234" maxlength="16" aria-invalid="false" value="" data-validate="{required:true}"/>
            <input class="input-exp-date" autocomplete="cc-exp" autocorrect="off" spellcheck="false" name="exp-date" id="exp-date" inputmode="numeric" aria-label="Credit or debit card expiration date" placeholder="MM / YY" aria-invalid="false" value="" data-validate="{required:true}"/>
            <input class="input-cvc" autocomplete="cc-csc" autocorrect="off" spellcheck="false" name="cvc" id="cvc" inputmode="numeric" aria-label="Credit or debit card CVC/CVV" maxlength="3" placeholder="CVC" aria-invalid="false" value="" data-validate="{required:true}"/></div>
                <div class=add-new-card><a href="'.$currentStore->getUrl().'stripe/customer/cards/">Add New Card</a></div>
            </div>';

        $paymentBlock .= '<div class="stripe-options stripe-ach-details stripe_payments_ach" style="display:none;">
            <fieldset class="fieldset" id="payment_form_stripe_payments_ach">
                <div class="field required">
                    <label class="label" for="stripe_payments_ach_account_holder_name">
                        <span><span>Account Holder Name</span></span>
                    </label>
                    <div class="control">
                        <input type="text" name="payment[ach][account_holder_name]" class="input-text" value=""     id="stripe_payments_ach_account_holder_name" title="Account Holder Name" data-container="stripe_payments_ach-account_holder_name" data-validate="{required:true}">
                    </div>
                </div>
                <div class="field required">
                    <label class="label" for="stripe_payments_ach_account_holder_type">
                        <span><span>Account Type</span></span>
                    </label>
                    <div class="control">
                        <select name="payment[ach][account_holder_type]" class="input-select" value="" id="stripe_payments_ach_account_holder_type" title="Account Type" data-container="stripe_payments_ach-account_holder_type" data-validate="{required:true}"><option value="">-- Please choose an option --</option><option value="Individual">Individual</option><option value="Company">Company</option></select>
                    </div>
                </div>
                <div class="field required">
                    <label class="label" for="stripe_payments_ach_account_number">
                        <span><span>Account Number</span></span>
                    </label>
                    <div class="control">
                        <input type="text" name="payment[ach][account_number]" class="input-select" value="" id="stripe_payments_ach_account_number" title="Account Number" data-container="stripe_payments_ach-account_number" data-validate="{required:true}">
                    </div>
                </div>
                <div class="field required">
                    <label class="label" for="stripe_payments_ach_routing_number">
                        <span><span>Routing Number</span></span>
                    </label>
                    <div class="control">
                        <input type="text" name="payment[ach][routing_number]" class="input-select" value=""  id="stripe_payments_ach_routing_number" title="Routing Number" data-container="stripe_payments_ach-routing_number" data-validate="{required:true}">
                    </div>
                </div>
            </fieldset>
        </div>';

      //  $paymentBlock = $resultPage->getLayout()->createBlock('StripeIntegration\Payments\Block\StripeElements')->toHtml();
        

       /* if($currentPrice == '')
        {
            $currentPrice = $this->_priceCurrency->format($product->getPrice(),false,2);

            $block = ' <div class="prd-current-price">'.$currentPrice.'</div>';
        } else { */
            $block = $resultPage->getLayout()
                    ->createBlock('Ced\Auction\Block\Product\View\Type\SimpleProductAuction')
                    ->setTemplate('Ced_Auction::product/view/type/simpleauction.phtml')
                    ->setData('product_id', $productId)
                    ->toHtml();
        //}

        $isBuyNow = $this->getRequest()->getParam('isBuyNow'); 
        $disable = ''; 
        $value = '';
        if($isBuyNow){
            $disable = 'disabled'; 
            if($currentPrice == '')
            {
                $currentPrice = number_format($product->getPrice(),2);
            }
            $value = "value = ".$currentPrice;
        }
        $url = $currentStore->getBaseUrl()."customizeauction/index/getRate";
        $htmlContent = '<form class="form popupbid"
            novalidate
            action="#"
            method="post"
            data-mage-init={"validation": {"errorClass": "mage-error"}}
            id="popupbid-validate-detail">

                <div class="bid-popup-wrap">
                <p class="message" data-message></p>
                <div class="bid-popup-image-section">
                    <img src="'.$image.'"/>
                    <span>'.$name.'</span>
                </div>
                <div class="popup-current-price">Current Price: '.$block.'</div>
                <div class="bid-price-cur">Your Bid Price:<input id="auction-bid" data-validate="{required:true}" class="bid-current-price" type="text" '.$disable.' '.$value.' /></div>
                
                <div class="popup-prem-section">
                    <div class="buyer-prem5">Buyers Premium: <span>5% = '.$this->getCurrentCurrencySymbol().'</span><span class="buy5price">00.0</span></div>
                    <div class="ccd-prem3">Credit Card Processing: <span>3% = '.$this->getCurrentCurrencySymbol().'</span><span class="buyprem3">00.0</span></div>
                    <div class="taxes65">Taxes: <span>'.$taxRate.'% = '.$this->getCurrentCurrencySymbol().'</span><span class="tax65price">00.0</span></div>
                </div>
                <button id="getrate">Click to get Shipping rate</button>
                <div class="getrate-error">Please Click here to get Shipping rate</div>
                <div class="popup-shipping-section">
                </div>
                <div class="total-cost">Total cost: <span>'.$this->getCurrentCurrencySymbol().'</span><span class="totalcostprice">00.0</span></div>
                <div class="popup-payment-section">Select Payment Method: 
                        '.$paymentBlock.'
                </div>
                <input id="customer-id" name="customer_id" type="hidden" type="text" value="'.$customer->getId().'">
                <input id="is-buy-now" name="is_buy_now" type="hidden" type="text" value="'.$isBuyNow.'">                
                <input id="total-auction-bid" name="total_auction_bid" type="hidden" maxlength="12" type="text">
                <input id="shipping-amount" name="shipping_amount" type="hidden" maxlength="12" type="text">
                <input id="payment-opt" name="payment_opt" type="hidden" maxlength="12" type="text">
                <input id="saved-card-id" name="saved-card-id" type="hidden" maxlength="12" type="text">
                <input id="stripe-customer-id" name="stripe-customer-id" type="hidden" value="'.$this->stripeCustomer->getStripeId().'" type="text">
                
                <div class="popup-confirm-bid">
                <input id="popup-auction-bid" type="hidden" maxlength="12" type="text">
                <button type="button" id="bidding-popup" class="bidding-popup" value="Confirm Bid Now">Confirm Bid <span>Now</span></buton></div>
                <div><a href="#">Terms and Condition</a></div>
            </div>
        </form>
        <script>
            require(["jquery","Magento_Catalog/js/price-utils"],function($, priceUtils) {

                $( document ).ready(function() {
                    $("div.getrate-error").hide();
                });

                $(".bid-current-price").on("input", function(){
                    var val = $(this).val();
                    updatePrice(val);          
                });

                function updatePrice(val){

                    $("#popup-auction-bid").val(val);

                    var buyerPremium5 = parseFloat((val * 5) / 100).toFixed(2);
                    var buyerPremium5Format = formatNumber(buyerPremium5);
                    $(".buyer-prem5>.buy5price").text(buyerPremium5Format);

                    var ccdPrem3 = parseFloat(parseFloat(buyerPremium5) + parseFloat(val));
                    var ccdPrem3 = parseFloat((ccdPrem3 * 3) / 100).toFixed(2);
                    var ccdPrem3Format = formatNumber(ccdPrem3);
                    $(".ccd-prem3>.buyprem3").text(ccdPrem3Format);

                    var taxes65 = parseFloat(parseFloat(buyerPremium5) + parseFloat(ccdPrem3) + parseFloat(val));
                    var taxes65 = parseFloat((taxes65 * '.$taxRate.') / 100).toFixed(2);
                    var taxes65Format = formatNumber(taxes65);
                    $(".taxes65>.tax65price").text(taxes65Format);

                    var shippingAmount = $("#shipping-amount").val();                 
                    var totalCost = parseFloat(parseFloat(val) + parseFloat(buyerPremium5) + parseFloat(ccdPrem3) + parseFloat(taxes65) + parseFloat(shippingAmount)).toFixed(2);
                    var totalCostFormat = formatNumber(totalCost);
                    $(".total-cost>.totalcostprice").text(totalCostFormat);

                    $("#total-auction-bid").val(totalCost);
                }

                function formatNumber(num) {
                  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
                }

                $("#getrate").on("click", function (e) {
                    e.preventDefault();
                    $.ajax({
                        url : "'.$url.'",
                        type : "POST",
                        data: {
                            "productSku": "'.$product->getSku().'", "addressId": "'.$shippingAddress->getId().'", "productId": "'.$product->getId().'" 
                        },
                        dataType:"json",
                        showLoader: true,
                        success : function(data) {
                            $("#getrate").attr("disabled", true);
                            $(".getrate-error").hide();
                            $(".popup-shipping-section").html(data.html);                            
                        },
                        error : function(request,error)
                        {
                            alert("Error");
                        }
                    });
                });                

                $(".popup-payment-radio").change(function(){
                    var pclass = $(this).attr("id");
                    if(pclass == "stripe_payments_ach"){
                        if($(this).data("clicked")) {

                        } else {
                            var totalcost = $("#total-auction-bid").val();
                            var buyprem3 = $(".buyprem3").text();
                            var achTotal = parseFloat(parseFloat(totalcost) - parseFloat(buyprem3)).toFixed(2);
                            var achTotalFormat = formatNumber(achTotal);
                            $(".total-cost>.totalcostprice").text(achTotalFormat);
                            $("#total-auction-bid").val(achTotal);
                            $(".ccd-prem3").hide();
                            $(this).data("clicked", true);
                        }
                    } else {
                        var val1 = $("#popup-auction-bid").val();
                        var val = val1.replace(",", "");                       
                        $(".ccd-prem3").show();
                        updatePrice(val);
                        $("#stripe_payments_ach").data("clicked", false);
                    }

                    $(".stripe-options").hide();
                    $("."+ pclass).show();
                    $("#payment-opt").val(pclass);
                    if($("#use-saved-card").prop("checked") == true){
                        $(".bid-saved-payment").show();
                    }
                    else if($("#use-saved-card").prop("checked") == false){
                        $(".bid-saved-payment").hide();
                    }                    
                });

                $("#bid-selected-payment").change(function(){
                    $("#saved-card-id").val($(this).val());                                       
                });

                $("#use-saved-card").click(function(){
                    $("#bid-selected-payment option[value=0]").attr("selected", true);
                    if($("#use-saved-card").prop("checked") == true){
                        $(".bid-saved-payment").show();
                        $(".payment-credit-opt").hide();
                    }
                    else if($("#use-saved-card").prop("checked") == false){
                        $(".bid-saved-payment").hide();
                        $(".payment-credit-opt").show();
                    }
                });

            });
        </script>
        ';

        $resultJson = $this->resultJsonFactory->create();
        //$htmlContent = 'Pass Html Content';
        $success = true;
        return $resultJson->setData([
            'html' => $htmlContent,
            'success' => $success,
            'tax' => $taxRate,
            'isBuyNow' => $isBuyNow,
            'currentPrice' => $currentPrice
        ]);
    }

    /**
     * Get current store currency symbol with price
     * $price price value
     * true includeContainer
     * Precision value 2
     */
    public function getCurrencyFormat($price)
    {
        $price = $this->_priceCurrency->format($price,true,2);
        return $price;
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }  

    public function getUsedPaymentMethods() 
    {
        $collection = $this->_orderPayment;
        $collection->getSelect()->group('method');
        $paymentMethods = array();
        foreach ($collection as $col) { 
            $paymentMethods[] = $col->getMethod();            
        }        
        //echo "<pre>"; print_r($paymentMethods); die();
        return $paymentMethods;
    }

    public function getActivePaymentMethods() 
    {
        $payments = $this->_paymentConfig->getActiveMethods();
        
        //$payments = $this->scopeConfig->getValue('payment');
        $getUsedPaymentMethods = $this->getUsedPaymentMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel)
        {    
            //if(in_array($paymentCode, $getUsedPaymentMethods))
            if($paymentCode == 'stripe_payments_ach' || $paymentCode == 'stripe_payments')
            {
                $paymentTitle = $this->scopeConfig->getValue('payment/'.$paymentCode.'/title');
                $methods[$paymentCode] = array(
                    'label' => $paymentTitle,
                    'value' => $paymentCode
                );
            }  
                   
        }
        return $methods;
    }
}