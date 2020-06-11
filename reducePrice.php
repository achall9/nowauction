<?php			
use Magento\Framework\App\Bootstrap;
require 'app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();	
			
	$product_id = $_REQUEST['productid'];
	echo $price = $_REQUEST['price'];
			$productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
		$product = $productFactory->create()->load($product_id);
		
		$product->setPrice($price);
			$product->save();
?>