<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Model\Vsettings\Shipping;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Address
 * @package Ced\CsMultiShipping\Model\Vsettings\Shipping
 */
class Address extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'address';

    /**
     * @var array
     */
    protected $_fields = array();

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Country
     */
    protected $country;

    /**
     * Address constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Config\Model\Config\Source\Locale\Country $country
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Config\Model\Config\Source\Locale\Country $country,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->country = $country;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get current store
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId) {
            return $this->csmarketplaceHelper->getStore($storeId);
        } else {
            return $this->csmarketplaceHelper->getStore();
        }
    }

    /**
     * Get current store
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get the code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get the code separator
     *
     * @return string
     */
    public function getCodeSeparator()
    {
        return $this->_codeSeparator;
    }

    /**
     *  Retreive input fields
     *
     * @return array
     */
    public function getFields()
    {
        $this->_fields = array();
        $this->_fields['country_id'] = array('type' => 'select', 'required' => true, 'values' => $this->country->toOptionArray());
        $this->_fields['region_id'] = array('type' => 'select', 'required' => true, 'values' => array(array('label' => __('Please select region, state or province'), 'value' => '')));
        $this->_fields['region'] = array('type' => 'text', 'required' => true);
        $this->_fields['city'] = array('type' => 'text', 'required' => true);
        $this->_fields['postcode'] = array('type' => 'text', 'required' => true);
        $this->_fields['postcode']['after_element_html'] = "";
        return $this->_fields;
    }

    /**
     * Retreive labels
     *
     * @param string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label'  :
                return __('Origin Address Details');
                break;
            case 'country_id' :
                return __('Country');
                break;
            case 'region_id' :
                return __('State/Province');
                break;
            case 'region' :
                return "";
                break;
            case 'city' :
                return __('City');
                break;
            case 'postcode' :
                return __('Zip/Postal Code');
                break;
            default :
                return __($key);
                break;
        }
    }
}
