<?php
namespace Ced\CustomizeAuction\Observer;

use Magento\Framework\Event\ObserverInterface;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;

class CustomPrice implements ObserverInterface
{
   protected $_objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $interface,
        \Magento\Quote\Model\Quote\Item $quote,
        \CollectionFactory $productCollectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->cart = $cart;
        $this->product = $product;
        $this->objectManager = $interface;
        $this->quote = $quote;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();

        echo "<pre>"; print_r($quoteItem->getData()); die();

        $price = 100; //set your price here
		$quoteItem->setCustomPrice($price);
		$quoteItem->setOriginalCustomPrice($price);
    }
}