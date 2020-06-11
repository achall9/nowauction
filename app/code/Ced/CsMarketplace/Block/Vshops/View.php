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
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMarketplace\Block\Vshops;

/**
 * Class View
 * @package Ced\CsMarketplace\Block\Vshops
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $_timezone;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Attribute
     */
    public $_attribute;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * View constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_attribute = $attribute;
        $this->_timezone = $timezone;
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $registry;
        if ($this->getVendor()) {
            $vendor = $this->getVendor();
            if ($vendor->getMetaDescription())
                $this->pageConfig->setDescription($vendor->getMetaDescription());
            if ($vendor->getMetaKeywords())
                $this->pageConfig->setKeywords($vendor->getMetaKeywords());
        }

    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $date
     * @return string
     */
    public function getTimezone($date)
    {
        return $this->_timezone->date(new \DateTime($date))->format('m/d/y');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor)
            $this->_vendor = $this->_coreRegistry->registry('current_vendor');
        return $this->_vendor;
    }

    /**
     * @param $key
     */
    public function camelize($key)
    {
        return $this->_camelize($key);
    }

    /**
     * @param $name
     */
    protected function _camelize($name)
    {
        $this->uc_words($name, '');
    }

    /**
     * @param $str
     * @param string $destSep
     * @param string $srcSep
     * @return mixed
     */
    function uc_words($str, $destSep = '_', $srcSep = '_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    /**
     * @param null $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLeftProfileAttributes($storeId = null)
    {
        if ($storeId == null) $storeId = $this->_storeManager->getStore()->getId();
        $attributes = $this->_attribute->create()->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('use_in_left_profile', ['gt' => 0])
            ->setOrder('position_in_left_profile', 'ASC');
        $this->_eventManager->dispatch('ced_csmarketplace_left_profile_attributes_load_after', array('attributes' => $attributes));
        return $attributes;
    }


    /**
     * @return mixed
     */
    public function getVendorLogo()
    {
        return $this->getVendor()->getData('profile_picture');
    }

    /**
     * @return mixed
     */
    public function getVendorBanner()
    {
        return $this->getVendor()->getData('company_banner');
    }


    /**
     * @param $code
     * @return bool
     */

    public function Method($code)
    {
        if ($this->getVendor()->getData($code) != "") {
            return $this->getVendor()->getData($code);
        } else {
            return false;
        }
    }

}

