<?php
namespace Ced\CustomizeAuction\Helper;
use Magento\Customer\Model\Session;
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $base_url = '';
    public $customer_token = '';
    protected $_customerSession;
    protected $stripeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $customerSession,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer,
        \StripeIntegration\Payments\Model\Config $stripeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $api_url = $baseUrl.'index.php/rest/V1';
        // Set the integrations access token.
               
        $this->base_url = $api_url;
        $this->_customerSession = $customerSession;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->stripeCustomer = $stripeCustomer;
        $this->stripeConfig = $stripeConfig;

        $customerId = $this->_customerSession->getCustomer()->getId();
        $tokenModel = $this->_tokenModelFactory->create();
        $customerToken = $tokenModel->createCustomerToken($customerId)->getToken();
        $this->customer_token = $customerToken;

        parent::__construct($context);
    }

      public function request($endpoint, $method = 'GET', $body = FALSE) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $headers = array();
        $headers[] = "Authorization: Bearer " . $this->customer_token;
        if ($body) {
          $headers[] = "Content-Type: application/json";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return $result;
      }

      public function getProduct($product_id) {
      //  return $this->request('/products/' . $product_id . '/', 'GET');
      }

      public function createCart() {
        $customerId = $this->_customerSession->getCustomer()->getId();
        /*$order = array(
          'customerId' => $customerId
        );*/
        $guestCart = $this->request('/carts/mine', 'POST');
        return $jsonDecode = json_decode($guestCart,true); 

       /* $customerId = $this->_customerSession->getCustomer()->getId();
        $storeId = $this->_storeManager->getStore()->getId();
        $customerData = array(
          'cart_id' => $guestCart,
          'customerId' => $customerId,
          'storeId' => $storeId
        );
        $loginCart = $this->request2('/guest-carts/'.$guestCart, 'PUT', json_encode($customerData));*/
      }

      public function addToCart($cart_id, $product_sku, $quantity = 1) {
        $order = array(
          'cartItem' => array(
            'quote_id' => $cart_id,
            'sku' => $product_sku,
            'qty' => $quantity
          )
        );
        $cartItem = $this->request('/carts/mine/items',
          'POST',
          json_encode($order)
          );

       return $jsonDecode = json_decode($cartItem,true);

      }


      public function setShipping($cart_id, $shipping) {
        return $this->request('/carts/mine/shipping-information',
          'POST',
          json_encode($shipping)
        );
      }

      public function placeOrder($cart_id, $winnerData, $email, $billingAddress, $firstName, $lastName) {

            $getSecretKey = $this->stripeConfig->getSecretKey();

            $stripe = new \Stripe\StripeClient(
              $getSecretKey
            );
            
            $paymentIntents = $stripe->paymentIntents->create([
              'amount' => str_replace(',','',number_format($winnerData['winning_price'])),
              'currency' => 'usd',
              'payment_method_types' => ['card'],
            ]);

            $paymentMethod = $stripe->paymentIntents->confirm(
              $paymentIntents['id'],
              ['payment_method' => 'pm_card_visa']
            );

            $additionalData = '';
            if($winnerData['payment_detail'] == 'stripe_payments')
            {
                //$this->stripeCustomer->createStripeCustomerIfNotExists();
                //$explode = explode(":", $winnerData['payment_token']);
                
                //$this->stripeCustomer->addCard($explode[0]);
                $additionalData = array (
                    "cc_stripejs_token" => $winnerData['payment_token']
                );
            }

            if($winnerData['payment_detail'] == 'stripe_payments_ach')
            {
                $additionalData = array (
                    'token' => $winnerData['payment_token']
                );             
            }
            
            $paymentTo = array(
              "cartId" => $cart_id,
              "payment_method" => array(
                  "method" => $winnerData['payment_detail'],
                  "extension_attributes" => array (
                      "agreement_ids" => ["1"]
                  ),
                  "additional_data" => $additionalData
              ),
              "billing_address" => array(
                  "email" => $email,
                  "region" => $billingAddress['region'],
                  "region_id" => $billingAddress['region_id'],
                  "country_id" => $billingAddress['country_id'],
                  "street" => [$billingAddress['street']],
                  "postcode" => $billingAddress['postcode'],
                  "city" => $billingAddress['city'],
                  "telephone" => $billingAddress['telephone'],
                  "firstname" => $firstName,
                  "lastname" => $lastName
              ),
              "email" => $email
              
           );

        $payment = $this->request('/carts/mine/set-payment-information', 'POST', json_encode($paymentTo)); 
        return $this->request('/carts/mine/order',
          'PUT',
          json_encode($payment)
        );
        //echo "<pre>"; print_r($order); die();
      }

    public function getPaymentMethods($cart_id, $payment) {

        echo $this->request('/guest-carts/' . $cart_id . '/payment-information', 'POST', json_encode($payment)); die();
        return $this->request('/carts/mine/payment-information', 'POST', json_encode($payment));
    }

}
