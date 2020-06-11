<?php
namespace Ced\CsMarketplace\Model\System\Message;

/**
 * Class PendingVendors
 * @package Ced\CsMarketplace\Model\System\Message
 */
class PendingVendors implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceDataHelper;

    /**
     * PendingVendors constructor.
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->marketplaceDataHelper = $marketplaceDataHelper;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        // Retrieve unique message identity
        return md5('PENDING_VENDORS');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        // Return true to show your message, false to hide it
        return (count($this->marketplaceDataHelper->getNewVendors())) ? true : false;
    }

    /**
     * @return string
     */
    public function getText()
    {
        // Retrieve message text
        return '<b>'.count($this->marketplaceDataHelper->getNewVendors()).'</b>'.__(' Approval Request for Vendor(s).'.__(' Approve Vendor(s) from').'<a href="'.$this->_urlBuilder->getUrl('csmarketplace/vendor/index').'">'.__(' Manage Vendors').'</a>');
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