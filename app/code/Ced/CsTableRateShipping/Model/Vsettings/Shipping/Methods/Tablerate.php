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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Model\Vsettings\Shipping\Methods;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\UrlInterface;

/**
 * Class Tablerate
 * @package Ced\CsTableRateShipping\Model\Vsettings\Shipping\Methods
 */
class Tablerate extends \Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'tablerate';

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\OfflineShipping\Model\Config\Source\Tablerate
     */
    protected $tablerate;

    /**
     * Tablerate constructor.
     * @param UrlInterface $urlBuilder
     * @param \Magento\OfflineShipping\Model\Config\Source\Tablerate $tablerate
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\OfflineShipping\Model\Config\Source\Tablerate $tablerate,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $csmarketplaceHelper,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->tablerate = $tablerate;
    }

    /**
     * @return array|mixed
     */
    public function getFields()
    {
        $fields['active'] = array('type' => 'select',
            'required' => true,
            'values' => array(
                array('label' => __('Yes'), 'value' => 1),
                array('label' => __('No'), 'value' => 0)
            )
        );

        $condition = $this->tablerate->toOptionArray();
        $fields['condition'] = array('type' => 'select',
            'values' => $condition
        );

        $url = $this->urlBuilder->getUrl('cstablerateshipping/export/exportTablerates', ['condition_name' => $condition, 'date' => time()]);
        $fields['export'] = array('type' => 'text', 'class' => 'hide', 'after_element_html' => '<button onclick="window.location=\'' . $url . '\'" class="btn btn-primary uptransform" type="button">Export CSV</button>');

        $fields['import'] = array('type' => 'file', 'name' => "groups[tablerate][fields][import][value]");

        return $fields;
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
            case 'label' :
                return __('Table Rate Shipping');
                break;
            case 'condition':
                return __('Condition');
                break;
            case 'export':
                return __('Export');
                break;
            case 'import':
                return __('Import');
                break;
            case 'active':
                return __('Active');
                break;
            default :
                return parent::getLabel($key);
                break;
        }
    }
}
