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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block;

class Transaction extends \Magento\Backend\Block\Widget\Container
{    
	protected $_template = 'transaction.phtml';
	
	protected function _construct()
    {
        $this->_controller = 'transaction';
        $this->_blockGroup = 'Ced_CsTransaction';
        $this->_headerText = __('Request Transaction List');
        
        parent::_construct();
        $this->removeButton('add');
      
    }
    
    
    protected function _prepareLayout()
    {
    	$this->setChild(
    			'grid',
    			$this->getLayout()->createBlock('Ced\CsTransaction\Block\Transaction\Grid', 'ced.transaction.grid')
    	);
    	return parent::_prepareLayout();
    }
    
    public function getGridHtml()
    {
    	return $this->getChildHtml('grid');
    }
}
