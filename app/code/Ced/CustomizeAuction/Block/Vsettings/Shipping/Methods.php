<?php
namespace Ced\CustomizeAuction\Block\Vsettings\Shipping;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Methods
 * @package Ced\CsMultiShipping\Block\Vsettings\Shipping
 */
class Methods extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var \Ced\CsMultiShipping\Model\Source\Shipping\Methods
     */
    protected $methods;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vsettings
     */
    protected $vsettings;
    protected $shipconfig;
    protected $scopeConfig;
    protected $resourceConnection;
    /**
     * Methods constructor.
     * @param \Magento\Framework\Data\Form $form
     * @param \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\Vsettings $vsettings
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Magento\Framework\Data\Form $form,
        \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\Vsettings $vsettings,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->form = $form;
        $this->methods = $methods;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vsettings = $vsettings;
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changin layout
     * @return Ced_CsMarketplace_Block_Vendor_Abstract
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Element')
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset')
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset\Element')
        );

        return parent::_prepareLayout();
    }

    /**
     * Get form object
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Get form object
     *
     * @return     Varien_Data_Form
     * @see        getForm()
     * @deprecated deprecated since version 1.2
     */
    public function getFormObject()
    {
        return $this->getForm();
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }
        return '';
    }

    /**
     * Set form object
     *
     * @param Varien_Data_Form $form
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    public function setForm(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->getBaseUrl());
        return $this;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = $this->form;
        $form->setAction($this->getUrl('*/methods/save', array('section' => \Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION)))
            ->setId('form-validate')
            ->setMethod('POST')
            ->setEnctype('multipart/form-data')
            ->setUseContainer(true);

        $vendor = $this->getVendor();        

        $activeCarriers = $this->shipconfig->getActiveCarriers();
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {

            if($carrierCode == 'vendor_rates'){
                continue;
            }
            
            $resource = $this->resourceConnection;
            $connection = $resource->getConnection();
        
            $winTable = $resource->getTableName('vendor_shippingmethods');

            $getData = "SELECT * FROM " . $winTable." WHERE vendor_id = ".$vendor->getId();
            $results = $connection->fetchAll($getData);

            $activeSelectedVal = array();
            $allMethSelectesVal = '';
            foreach ($results as $key => $data){
                $shippingMethod = json_decode($data['shipping_method'], true);
                $activeSelectedVal[] = $shippingMethod[$carrierCode.'_active'];

                if(isset($shippingMethod[$carrierCode.'_allowed_method']))
                {
                    $allMethSelectesVal = array_values($shippingMethod[$carrierCode.'_allowed_method']);
                }
            }


            $fields = array();
            $fields['active'] = array('type' => 'select',
                    'label' => 'Active',
                    'required' => true,
                    'name' => $carrierCode.'_active',
                    'values' => array(
                        array('label' => __('Yes'), 'value' => 1),
                        array('label' => __('No'), 'value' => 0)
                    ),
                    'value'=> $activeSelectedVal
                );
            $values = array();
            if($carrierModel) {
                if( $carrierMethods = $carrierModel->getAllowedMethods() )
                {                     
                    foreach ($carrierMethods as $methodCode => $method){                   

                        if(is_object($method))
                        {
                            $values[] = array('label' => $method->getText(), 'value' => $methodCode);
                        }                     
                    }
                }
            }

           $options = array();
           $carrierTitle =$this->scopeConfig->getValue('carriers/'.$carrierCode.'/title');
           if(!empty($values))
           {
                $fields['allowed_methods'] = array('type' => 'multiselect','label' => 'Allowed Methods',
                    'required' => true,
                    'values' => $values,
                    'name' => $carrierCode.'_allowed_method',
                    'value' => $allMethSelectesVal
                );
            }

                if (count($fields) > 0) {
                    $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
                    $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
                    $fieldset = $form->addFieldset('csmultishipping_' . $carrierCode, array('legend' => $carrierTitle));
                    foreach ($fields as $id => $field) {
                        $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION . '/' . $carrierCode . '/' . $id);
                        $value = '';
                       /* $setting = $this->vsettings
                            ->loadByField(array($key_tmp, $vendor_id_tmp), array($key, (int)$vendor->getId()));
                                               

                        if ($setting) {
                            $value = $setting->getValue();
                        } */

                        $fieldset->addField(
                            $carrierCode . '_' . $id, isset($field['type']) ? $field['type'] : 'text', array(
                                'value' => $field['value'],
                                'label' => $field['label'],
                                'title' => __('Shipping Methods No Free'),
                                'values' => $field['values'],
                                'name' => $field['name'],
                                'required' => true,
                            )
                        );
                    }
                }
          
        }

        /*$code = '';
        $methods = array();
        $methods = $this->methods->getMethods();
        if (count($methods) > 0) {
            foreach ($methods as $code => $method) {
                if (!isset($method['model'])) {
                    continue;
                }
                $object = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $object->create($method['model']);
                $fields = $model->getFields();
                if (count($fields) > 0) {
                    $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
                    $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
                    $fieldset = $form->addFieldset('csmultishipping_' . $code, array('legend' => $model->getLabel('label')));
                    foreach ($fields as $id => $field) {
                        $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION . '/' . $code . '/' . $id);
                        $value = '';
                        $setting = $this->vsettings
                            ->loadByField(array($key_tmp, $vendor_id_tmp), array($key, (int)$vendor->getId()));

                        if ($setting) {
                            $value = $setting->getValue();
                        }
                        $fieldset->addField(
                            $code . $model->getCodeSeparator() . $id, isset($field['type']) ? $field['type'] : 'text', array(
                                'label' => $model->getLabel($id),
                                'value' => $value,
                                'name' => 'groups[' . $model->getCode() . '][' . $id . ']',
                                isset($field['class']) ? 'class' : '' => isset($field['class']) ? $field['class'] : '',
                                isset($field['required']) ? 'required' : '' => isset($field['required']) ? $field['required'] : '',
                                isset($field['onchange']) ? 'onchange' : '' => isset($field['onchange']) ? $field['onchange'] : '',
                                isset($field['onclick']) ? 'onclick' : '' => isset($field['onclick']) ? $field['onclick'] : '',
                                isset($field['href']) ? 'href' : '' => isset($field['href']) ? $field['href'] : '',
                                isset($field['target']) ? 'target' : '' => isset($field['target']) ? $field['target'] : '',
                                isset($field['values']) ? 'values' : '' => isset($field['values']) ? $field['values'] : '',
                                isset($field['after_element_html']) ? 'after_element_html' : '' => isset($field['after_element_html']) ? '<div><small>' . $field['after_element_html'] . '</small></div>' : '',
                            )
                        );
                    }
                }
            }
        } else {
            $form->addField('default_message', 'note', array('text' => __('No Shipping Methods are Available.')));
        } */
        $this->setForm($form);
        return $this;
    }

    /**
     * This method is called before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = array())
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType())
                && !in_array($attribute->getAttributeCode(), $exclude)
                && ('media_image' != $inputType)
            ) {

                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField(
                    $attribute->getAttributeCode(), $fieldType,
                    array(
                        'name' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontend()->getLabel(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'note' => $attribute->getNote(),
                    )
                )
                    ->setEntityAttribute($attribute);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(false, true));
                    $element->setCanBeEmpty(true);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setFormat($this->getDateFormat(\IntlDateFormatter::SHORT));
                } else if ($inputType == 'datetime') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setTime(true);
                    $element->setStyle('width:50%;');
                    $element->setFormat($this->getDateTimeFormat(\IntlDateFormatter::SHORT));
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }
    }

    /**
     * Add new element type
     *
     * @param Varien_Data_Form_Abstract $baseElement
     */
    protected function _addElementTypes(\Magento\Framework\Data\Form\AbstractForm $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

    /**
     * Retrieve predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array();
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }


}
