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

namespace Ced\CsVendorAttribute\Block\Adminhtml\Attributes\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;

class Main extends AbstractMain
{
    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = [])
    {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $eavData,
            $yesnoFactory,
            $inputTypeFactory,
            $propertyLocker,
            $data
        );
        $this->yesno = $yesno;
    }

    /**
     * Adding product form elements for editing attribute
     * @return $this|AbstractMain
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        $not_in_invoice = array('image','file','multiselect','textarea');
        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        
        $yesnoSource = $this->yesno->toOptionArray();
        
        $fieldsett = $form->addFieldset('vendor_registration', array('legend'=>__('Vendor Registration Form')));
        if($this->_coreRegistry->registry('entity_attribute') &&  $this->_coreRegistry->registry('entity_attribute')->getId() && in_array($this->_coreRegistry->registry('entity_attribute')->getAttributeCode(), array('shop_url','public_name'))) {
            $fieldsett->addField(
                'use_in_registration', 'select', array(
                'name'      => 'use_in_registration',
                'label'     => __('Use in Vendor Registration Form'),
                'title'     => __('Use in Vendor Registration Form'),
                'values'    => $yesnoSource,
                'note'        => __('If yes then it will shown as input in vendor registration form.'),
                'disabled'  => true,
                )
            );
        } else {
            $fieldsett->addField(
                'use_in_registration', 'select', array(
                'name'      => 'use_in_registration',
                'label'     => __('Use in Vendor Registration Form'),
                'title'     => __('Use in Vendor Registration Form'),
                'values'    => $yesnoSource,
                'note'        => __('If yes then it will shown as input in vendor registration form.'),
                )
            );
        }
        
        
        $fieldsett->addField(
            'position_in_registration', 'text', array(
            'name' => 'position_in_registration',
            'label' => __('Position in Vendor Registration Form'),
            'title' => __('Position in Vendor Registration Form'),
            'note' => __('Position of attribute in Vendor Registration Form'),
            'class' => 'validate-digits',
            )
        );
        
        $fieldsett = $form->addFieldset('vendor_frontend', array('legend'=>__('Vendor Profile Edit Form')));
        
        $fieldsett->addField(
            'is_visible', 'select', array(
            'name'      => 'is_visible',
            'label'     => __('Use in Vendor Profile Edit Form'),
            'title'     => __('Use in Vendor Profile Edit Form'),
            'values'    => $yesnoSource,
            'note'        => __('If yes then it will shown as input in vendor profile edit form.'),
            )
        );
        
        $fieldsett->addField(
            'position', 'text', array(
            'name' => 'position',
            'label' => __('Position in Vendor Profile Edit Form'),
            'title' => __('Position in Vendor Profile Edit Form'),
            'note' => __('Position of attribute in Vendor Profile Edit Form'),
            'class' => 'validate-digits',
            )
        );

        if (!in_array($attributeObject->getFrontendInput(), $not_in_invoice)) {
            $fieldsett = $form->addFieldset('use_in_invoicee', array('legend'=>__('Use Attribute In Invoice')));
            $fieldsett->addField(
                'use_in_invoice', 'select', array(
                'name'      => 'use_in_invoice',
                'label'     => __('Use In Invoice'),
                'title'     => __('Use In Invoice'),
                'values'    => $yesnoSource,
                'note'        => __('Use this attribute in Invoice form.'),
                )
            );
        }
        
        $fieldsett = $form->addFieldset('vendor_left_profile', array('legend'=>__('Vendor Left Profile View(On Shop Pages)')));
        
        $fieldsett->addField(
            'use_in_left_profile', 'select', array(
            'name'      => 'use_in_left_profile',
            'label'     => __('Use in Vendor Left Profile View'),
            'title'     => __('Use in Vendor Left Profile View'),
            'values'    => $yesnoSource,
            'note'        => __('If yes then it will shown in vendor left profile view at vendor shop pages.'),
            )
        );
        
        $fieldsett->addField(
            'fontawesome_class_for_left_profile', 'text', array(
            'name' => 'fontawesome_class_for_left_profile',
            'label' => __('Font Awesome class for Left Profile View'),
            'title' => __('Font Awesome class for Left Profile View'),
            'note' => __('Font Awesome class for Left Profile of attribute in Vendor Left Profile View at vendor shop pages.<br/>You can get the class name from here <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">https://fontawesome.com/v4.7.0/icons/</a>.'),
            )
        );
        
        $fieldsett->addField(
            'position_in_left_profile', 'text', array(
            'name' => 'position_in_left_profile',
            'label' => __('Position in Vendor Left Profile View'),
            'title' => __('Position in Vendor Left Profile View'),
            'note' => __('Position of attribute in Vendor Left Profile View at vendor shop pages'),
            'class' => 'validate-digits',
            )
        );
        
        $fieldset->addField(
            'is_global', 'hidden', array(
            'name'  => 'is_global',
            ), 'attribute_code'
        );
        
        // frontend properties fieldset
        $frontendValues = $form->getElement('frontend_input')->getValues();
        $frontendValues['image'] = __('Image Type');
        $frontendValues['file'] = __('File Type');
        $form->getElement('frontend_input')->setValues($frontendValues);
        
        $form->getElement('frontend_input')->setLabel(__('Vendor Input Type for Store Owner'));
        $form->getElement('is_unique')->setNote(__('Not shared with other vendors'));
        $classValidation = $form->getElement('frontend_class')->getValues();
        $classValidation['validate-shopurl'] = __('Vendor Shop URL');
        $form->getElement('frontend_class')->setValues($classValidation);
        
        $this->_eventManager->dispatch(
            'csmarketplace_adminhtml_vendor_attribute_edit_prepare_form', array(
            'form'      => $form,
            'attribute' => $attributeObject
            )
        );
        
        return $this;
    }
}
