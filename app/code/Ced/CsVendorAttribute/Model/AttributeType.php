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
 * @package     Ced_CsVendorAttribute
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorAttribute\Model;

class AttributeType
{
    /**
     * @param \Magento\Framework\Registry $registry
     */
	public function __construct(
			\Magento\Framework\Registry $registry
	){
	    $this->_coreRegistry = $registry;
	}
    
    public function afterToOptionArray($subject, $result)
    { 
         $entityModel = $this->_coreRegistry->registry('entity_attribute');
         if($entityModel) {
             if ($entityModel->getEntityType()->getEntityTypeCode() == 'csmarketplace_vendor') {
                 foreach ($result as $type => $typeCode) {
                     if ($typeCode['value'] == 'texteditor') {
                         unset($result[$type]);
                     }
                 }
             }
         }
         return $result;
    }
}
