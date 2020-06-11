<?php
namespace Ced\CsMarketplace\Model\System\Message;

use Ced\CsMarketplace\Model\Vproducts;

/**
 * Class PendingProducts
 * @package Ced\CsMarketplace\Model\System\Message
 */
class PendingProducts implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * PendingProducts constructor.
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->vProductsFactory = $vProductsFactory;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        // Retrieve unique message identity
        return md5('PENDING_PRODUCTS');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        // Return true to show your message, false to hide it
        return (count($this->vProductsFactory->create()->getVendorProducts(Vproducts::PENDING_STATUS))) ? true : false;
    }

    /**
     * @return string
     */
    public function getText()
    {
        // Retrieve message text
        return '<b>'.count($this->vProductsFactory->create()->getVendorProducts(Vproducts::PENDING_STATUS)).'</b>'.__(' Approval Request for Vendor Product(s).'.__(' Approve Vendor Product(s) from').'<a href="'.$this->_urlBuilder->getUrl('csmarketplace/vproducts/pending').'">'.__(' Vendor Pending Products').'</a>');
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}