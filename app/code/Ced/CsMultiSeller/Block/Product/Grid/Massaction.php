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

namespace Ced\CsMultiSeller\Block\Product\Grid;

class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    /**
     * Massaction items
     *
     * @var array
     */
    protected $_items = [];

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/massaction_extended.phtml';

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Helper\Data $backendData,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_backendData = $backendData;
        parent::__construct($context,$jsonEncoder,$backendData, $data);
    }

    /**
     * Sets Massaction template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setErrorText($this->escapeJsQuote(__('Please select items.')));
        $this->setData('area','adminhtml');
    }
    
    /**
     * Retrieve apply button html
     *
     * @return string
     */
    public function getApplyButtonHtml()
    {
    	return $this->getButtonHtml(__('Submit'), $this->getJsObjectName() . ".apply()");
    }

   /**
     * Retrieve  button html
     */
    public function getButtonHtml($label, $onclick, $class = '', $buttonId = null, $dataAttr = [])
    {
    	return $this->getLayout()->createBlock(
    			'Magento\Backend\Block\Widget\Button'
    	)->setData(
    			['label' => $label, 
    			 'onclick' => $onclick, 
    			 'class' => $class, 
    			 'type' => 'button', 
    			 'id' => $buttonId, 
    			 'area'=>'adminhtml']
    	)->setDataAttribute(
    			$dataAttr
    	)->toHtml();
    }
}
