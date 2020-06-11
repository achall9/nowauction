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
 * @category    Ced
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Model\Product\Price;

/**
 * Class Slabs
 * @package Ced\GiftCard\Model\Product\Price
 */
class Slabs extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serializer;

    /**
     * Slabs constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $json
    )
    {
        $this->_serializer = $json;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
       if (!$this->_options) { 
            $data = [];
            $config = $this->_scopeConfig->getValue(
                'giftcard/giftcard/gift_price_slab',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
             
            if ($config  !== '' && isset($config) && $config != null){

                $config =  $this->_serializer->unserialize($config);

                foreach ($config as $key=>$value){
                    $data[] = array('value' => $value['priceslab'], 'label' => $value['priceslab']);
                }
            }
            $this->_options = $data;
        }
        return $this->_options;
    }
}