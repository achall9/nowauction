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
 * @author 	   CedCommerce Core Team <connect@cedcommerce.com>
 */
namespace Ced\CsMultiSeller\Block;
class Search extends \Magento\Backend\Block\Widget\Container
{
  protected $_template = 'search.phtml';
 
    /**
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
    	//$this->setData('area','adminhtml');
    	//echo "callfssffsing";die;
    }
    
    protected function _construct()
    {
    	$this->_controller = 'product';
    	$this->_blockGroup = 'Ced_CsProduct';
    	$this->_headerText = __('Products');
    
    	parent::_construct();
    	//$this->buttonList->update('add', 'label', __('Add New Product'));
    }
    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
  
	protected function _prepareLayout()
	{
		//$this->setData('area','adminhtml');
		$addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Ced\CsProduct\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
		//echo "class ->"get_class($this->buttonList);die;
        $this->buttonList->add('add_new', $addButtonProps); 
		
		 $this->setChild('grid', $this->getLayout()->createBlock('Ced\CsMultiSeller\Block\Search\Grid', 'search.grid'));
        return parent::_prepareLayout();
		
	}
	/**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
				'href' =>$this->_getProductCreateUrl($typeId)
            ];
        }

        return $splitButtonOptions;
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
            'csproduct/*/new',
            ['set' => $this->_productFactory->create()->getDefaultAttributeSetId(), 'type' => $type]
        );
    }
    
    /**
     *  Add Button options
     * @return array 
     */
    protected function _getAddButtonOptions()
    {
    
    	$splitButtonOptions[] = [
    	'label' => __('Add New'),
    	'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
    	'area' => 'adminhtml'
    			];
    
    	return $splitButtonOptions;
    } 
	
	
    /**
     *  Return Created Url
     */
    protected function _getCreateUrl()
    {
    	return $this->getUrl(
    			'*/*/new'
    	);
    }
   	
    /**
     *  Return Grid Html
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
