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

/**
 * Catalog manage products block
 *
 * @category   Ced
 * @package    Ced_CsMultiSeller
 * @author 	   CedCommerce Core Team <connect@cedcommerce.com >
 */
namespace Ced\CsMultiSeller\Block;
/**
 * Class Product
 * @package Ced\CsMultiSeller\Block
 */
/**
 * Class Product
 * @package Ced\CsMultiSeller\Block
 */
class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'product.phtml';

    /**
     * Product constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
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

    protected function _construct()
    {
    	parent::_construct();
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('Ced\CsMultiSeller\Block\Product\Grid', 'product.grid'));
        return parent::_prepareLayout();
        
    }
    
    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
   /* protected function _getAddButtonOptions()
    {
    	$splitButtonOptions = [
    			'label' => __('Add New Category'),
    			'class' => 'primary',
    			'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
    	];
    	$this->buttonList->add('add', $splitButtonOptions);
    
    }*/
    
    /**
     * Adds New Button
     */
  /*  protected function _addNewButton()
    {
    	$this->addButton(
    			'add',
    			[
    					'label' => 'ddadsdada',
    					'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
    					'class' => 'add primary'
    			]
    	);
    }*/
    
    /**
     * Add Button Label
     */
    public function getAddButtonLabel()
    {
    	return $this->_addButtonLabel;
    }
   
   
    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        return $this->getUrl(
            'csmultiseller/*/new'
        );
    }
   
    /**
     * Retrieve create url 
     */
    protected function _getCreateUrl()
    {
    	return $this->getUrl(
    			'*/*/new'
    	);
    }
   	

    /**
     * Retrieve Grid Html
     */
    public function getGridHtml()
    {
    	return $this->getChildHtml('grid');
    }
    
    /**
     * Is Single Store Mode is Enable
     *  @return boolean
     */
    public function isSingleStoreMode()
    {
    	if (!$this->_storeManager->isSingleStoreMode()) {
    		return false;
    	}
    	return true;
    }
    
}
