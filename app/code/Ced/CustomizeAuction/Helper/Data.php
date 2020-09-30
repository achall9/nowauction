<?php
namespace Ced\CustomizeAuction\Helper;

use Magento\Quote\Model\QuoteFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_addressFactory;
    protected $_stripePayment;
    protected $api;
    protected $quoteFactory;
    protected $resourceConnection;

    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Magento\Catalog\Model\Product $product,
    * @param Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
    * @param Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
    * @param Magento\Customer\Model\CustomerFactory $customerFactory,
    * @param Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    * @param Magento\Sales\Model\Order $order
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Ced\CustomizeAuction\Helper\Api $api,
        QuoteFactory $quoteFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        $this->_addressFactory = $addressFactory;
        $this->api = $api;
        $this->quoteFactory = $quoteFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
 
    /**
     * Create Order On Your Store
     * 
     * @param array $orderData
     * @return array
     * 
    */
    public function createMageOrder($winnerData) {
        
        $product = $this->_product->load($winnerData['product_id']);

        $sku = $product->getSku();

        $magento = $this->api;

         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $cartObject = $objectManager->create('Magento\Checkout\Model\Cart')->truncate();
         $cartObject->saveQuote();

        //$product = $magento->getProduct($sku);
        $cart = $magento->createCart();
        
        $cartId = $cart;

        $order_filled = $magento->addToCart($cartId, $sku, 1);

        //$shippingAmount = $winnerData['shipping_amount'];
        $winningPrice = $winnerData['winning_price'];

        //$finalPrice = ($winningPrice - $shippingAmount);

        $resource = $this->resourceConnection;
        $connection = $resource->getConnection();
    
        $winTable = $resource->getTableName('quote_item');
        //Select Data from table
        $sql = "Update " . $winTable." Set custom_price = ".$winningPrice.", original_custom_price = ".$winningPrice." WHERE item_id = ".$order_filled['item_id'];
        $connection->query($sql);

        $customer = $this->customerRepository->getById($winnerData['customer_id']);

        $billingAddressId = $customer->getDefaultBilling();
        $shippingAddressId = $customer->getDefaultShipping();
        $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
        $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);

        $shippingMethod = json_decode($winnerData['shipping_detail'], true);

        $shippingMethodCode = $shippingMethod['method_code'];
        $shippingCarrierCode = $shippingMethod['carrier_code'];

        $ship_to = array (
          'addressInformation' =>
            array (
              'shippingAddress' =>
                array (
                  'region' => $shippingAddress['region'],
                  'region_id' => $shippingAddress['region_id'],
                  'country_id' => $shippingAddress['country_id'],
                  'street' =>
                    array (
                      0 => $shippingAddress['street'],
                    ),
                  'company' => $shippingAddress['company'],
                  'telephone' => $shippingAddress['telephone'],
                  'postcode' => $shippingAddress['postcode'],
                  'city' => $shippingAddress['city'],
                  'firstname' => $shippingAddress['firstname'],
                  'lastname' => $shippingAddress['lastname'],
                  'email' => $customer->getEmail(),
                  'prefix' => $shippingAddress['prefix'],
                  'sameAsBilling' => 1,
                ),
              'billingAddress' =>
                array (
                  'region' => $billingAddress['region'],
                  'region_id' => $billingAddress['region_id'],
                  'country_id' => $billingAddress['country_id'],
                  'street' =>
                    array (
                      0 => $billingAddress['street'],
                    ),
                  'company' => $billingAddress['company'],
                  'telephone' => $billingAddress['telephone'],
                  'postcode' => $billingAddress['postcode'],
                  'city' => $billingAddress['city'],
                  'firstname' => $billingAddress['firstname'],
                  'lastname' => $billingAddress['lastname'],
                  'email' => $customer->getEmail(),
                  'prefix' => $billingAddress['prefix'],
                  'sameAsBilling' => 1,
                ),
              'shipping_method_code' => $shippingMethodCode,
              'shipping_carrier_code' => $shippingCarrierCode,
            ),
        );

        $order_shipment = $magento->setShipping($cartId, $ship_to);
        
        $ordered = $magento->placeOrder($cartId, $winnerData, $customer->getEmail(), $billingAddress, $customer->getFirstName(), $customer->getLastName());


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/winner.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info('you placed order successfully. Order id is: '.$ordered);

        if($ordered){
            $result['order_id']= $ordered;
        }else{
            $result=['error'=>1,'msg'=>'Your custom message'];
        }
        //echo "\nordered:\n";
       // var_dump($ordered);
        return $result;
    }
}
