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
namespace Ced\GiftCard\Model;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class GiftTemplate
 * @package Ced\GiftCard\Model
 */
class GiftTemplate extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var
     */
    protected $_options;
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Ced\GiftCard\Model\ResourceModel\GiftTemplate::class);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $giftTemplate = $this->getCollection()->setOrder('id', 'ASC');
            $data = [];
            
            foreach ($giftTemplate->getData() as $key => $value){
                $data[] = array('value' => $value['id'], 'label' => $value['name']);
            }
            array_unshift($data, ['value' => ' ', 'label' => __('Please Select Name.')]);
            $this->_options = $data;
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function toOptionArray(){
    	return $this->getAllOptions();
    }
}
