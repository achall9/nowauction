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

namespace Ced\CsTableRateShipping\Block;

class ListBlock extends \Magento\Backend\Block\Widget\Container
{    
  protected $_template = 'cstablerateshipping/tablerate/list.phtml';
    protected $_typeFactory;
    protected $_productFactory;

  protected function _construct()
  {
    $this->_controller = 'deal';
    $this->_blockGroup = 'Ced_CsTableRateShipping';
    $this->_headerText = __('Table Rate Shipping');
  
    parent::_construct();
    //$this->buttonList->update('add', 'label', __('Add New Product'));
  }
  
  public function __construct(
    \Magento\Backend\Block\Widget\Context $context,
    \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    array $data = []
  ) {
    $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
    
    parent::__construct($context, $data);
  }
  
  protected function _prepareLayout()
  {
    $newurl = $this->getUrl('cstablerateshipping/rates/new');
    $this->addButton('add_rates', array(
        'label'   => __('Add New Rate'),
        'onclick' => "setLocation('{$newurl}')",
        'class'   => 'action-default primary add',
        'area' => 'frontend'
            ));
    $this->setChild(
        'grid',
        $this->getLayout()->createBlock('Ced\CsTableRateShipping\Block\ListBlock\Grid', 'ced.cstablerate.vendor.rate.grid')
    );  
    
    return parent::_prepareLayout(); 
    
  }
  
    
    
    public function getGridHtml()
    {
      return $this->getChildHtml('grid');
    }
    protected function _getAddButtonOptions()
    {
 
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
 
        return $splitButtonOptions;
    }
    
}