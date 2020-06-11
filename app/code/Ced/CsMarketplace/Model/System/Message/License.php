<?php
namespace Ced\CsMarketplace\Model\System\Message;

/**
 * Class License
 * @package Ced\CsMarketplace\Model\System\Message
 */
class License implements \Magento\Framework\Notification\MessageInterface
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
     * License constructor.
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
        return md5('license');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        $configValue = $this->marketplaceDataHelper->getStoreConfig('ced/csmarketplace/islicensevalid');
        return is_null($configValue) || $configValue=='' ? false : true ;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getText()
    {
        return __('Invalid License <a href="%1">Activate now</a>',$this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/cedcore/'));
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}