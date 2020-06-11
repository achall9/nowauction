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
 * @category  Ced
 * @package   Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMessaging\Block\Customer;

use Magento\Framework\View\Element\Template;

/**
 * Class Vcompose
 * @package Ced\CsMessaging\Block\Customer
 */
class Vcompose extends Template
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    public function __construct(Template\Context $context,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_productFactory = $productFactory;
        $this->vendorFactory = $vendorFactory;
    }

    public function getProductById($productId)
    {
        $product = $this->_productFactory->create();
        $product->load($productId);
        return $product;
    }

    public function getVendorById($vendorId)
    {
        $vendor = $this->vendorFactory->create();
        $vendor->load($vendorId);
        return $vendor;
    }
}
