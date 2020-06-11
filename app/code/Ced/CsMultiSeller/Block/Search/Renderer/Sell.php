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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultiSeller\Block\Search\Renderer;
class Sell extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer{

	/**
	 * @return Button
	 */
	public function render(\Magento\Framework\DataObject $row) {
		$html= '';
		$url = $this->getUrl('*/*/assign',array('id'=>$row->getEntityId()));
		$html.='<button type="button" class="button btn btn-info" onclick="setLocation(\''.$url.'\');">'.__('Sell This Product').'</button>';
		return $html;
	}

}
?>