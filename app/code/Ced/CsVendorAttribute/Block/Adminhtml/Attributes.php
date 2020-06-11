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
  * @package   Ced_CsVendorAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsVendorAttribute\Block\Adminhtml;
 
class Attributes extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'attributes/view.phtml';
 
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        
        parent::__construct($context, $data);
        $this->getAddButtonOptions();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {  
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsVendorAttribute\Block\Adminhtml\Attributes\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }
 
    /**
     * @return array
     */
    protected function getAddButtonOptions()
    {
        $splitButtonOptions = [
           'label' => __('Add New Attribute'),
        'class' => 'primary',
           'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);        
    }
    
 
    /**
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'csvendorattribute/*/new'
        );
    }
 
    /**
     * Render grid
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
