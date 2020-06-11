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
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vpayments;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $acl;

    /**
     * @var \Magento\Framework\Registry
     */
    protected  $registry;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @var \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname
     */
    protected $vendorname;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * View constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form $form
     * @param \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form $form,
        \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone
    )
    {
        $this->_acl = $acl;
        $this->registry = $registry;
        $this->form = $form;
        $this->vendorname = $vendorname;
        $this->priceCurrency = $priceCurrency;
        $this->timezone = $timezone;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @return mixed
     */

    public function getVpayment()
    {
        $payment = $this->registry->registry('current_vpayment');
        return $payment;
    }

    /**
     * @return View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        list($model, $fieldsets) = $this->loadFields();
        $form = $this->form;

        foreach ($fieldsets as $key => $data) {
            $fieldset = $form->addFieldset($key, array('legend' => $data['legend']));
            foreach ($data['fields'] as $id => $info) {
                if ($info['type'] == 'link') {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'href' => $info['href'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',
                    ]);
                } else {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',

                    ]);
                }
            }
        }
        $this->setForm($form);
        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadFields()
    {
        $model = $this->getVpayment();
        $renderOrderDesc = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc');

        $renderName = $this->vendorname;
        if ($model->getBaseCurrency() != $model->getCurrency()) {
            $fieldsets = array(
                'beneficiary_details' => array(
                    'fields' => array(
                        'vendor_id' => array('label' => __('Vendor Name'), 'text' => $renderName->render($model), 'type' => 'note'),
                        'payment_code' => array('label' => __('Payment Method'), 'type' => 'label', 'value' => $model->getData('payment_code')),
                        'payment_detail' => array('label' => __('Beneficiary Details'), 'type' => 'note', 'text' => $model->getData('payment_detail')),
                    ),
                    'legend' => __('Beneficiary Details')
                ),

                'order_details' => array(
                    'fields' => array(
                        'amount_desc' => array(
                            'label' => __('Order Details'),
                            'text' => $renderOrderDesc->render($model),
                            'type' => 'note',
                        ),
                    ),
                    'legend' => __('Order Details')
                ),

                'payment_details' => array(
                    'fields' => array(
                        'transaction_id' => array('label' => __('Transaction ID#'), 'type' => 'label', 'value' => $model->getData('transaction_id')),
                        'created_at' => array(
                            'label' => __('Transaction Date'),
                            'value' => $model->getData('created_at'),
                            'type' => 'label',
                        ),
                        'payment_method' => array(
                            'label' => __('Transaction Mode'),
                            'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                            'type' => 'label',
                        ),
                        'transaction_type' => array(
                            'label' => __('Transaction Type'),
                            'value' => ($model->getData('transaction_type') == 0) ? __('Credit Type') : __('Debit Type'),
                            'type' => 'label',
                        ),
                        'total_shipping_amount' => array(
                            'label' => __('Total Shipping Amount'),
                            'value' => $this->priceCurrency->format($model->getData('total_shipping_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'amount' => array(
                            'label' => __('Amount'),
                            'value' => $this->priceCurrency->format($model->getData('amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'base_amount' => array(
                            'label' => __('Base Amount'),
                            'value' => $this->priceCurrency->format($model->getData('base_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'fee' => array(
                            'label' => __('Adjustment Amount'),
                            'value' => $this->priceCurrency->format($model->getData('fee'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'base_fee' => array(
                            'label' => __('Base Adjustment Amount'),
                            'value' => $this->priceCurrency->format($model->getData('base_fee'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'net_amount' => array(
                            'label' => __('Net Amount'),
                            'value' => $this->priceCurrency->format($model->getData('net_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'base_net_amount' => array(
                            'label' => __('Base Net Amount'),
                            'value' => $this->priceCurrency->format($model->getData('base_net_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'notes' => array(
                            'label' => __('Notes'),
                            'value' => $model->getData('notes'),
                            'type' => 'label',
                        ),
                    ),
                    'legend' => __('Transaction Details')
                ),
            );
        } else {
            $fieldsets = array(
                'beneficiary_details' => array(
                    'fields' => array(
                        'vendor_id' => array('label' => __('Vendor Name'), 'text' => $renderName->render($model), 'type' => 'note'),
                        'payment_code' => array('label' => __('Payment Method'), 'type' => 'label', 'value' => $model->getData('payment_code')),
                        'payment_detail' => array('label' => __('Beneficiary Details'), 'type' => 'note', 'text' => $model->getData('payment_detail')),
                    ),
                    'legend' => __('Beneficiary Details')
                ),

                'order_details' => array(
                    'fields' => array(
                        'amount_desc' => array(
                            'label' => __('Order Details'),
                            'text' => $renderOrderDesc->render($model),
                            'type' => 'note',
                        ),
                    ),
                    'legend' => __('Order Details')
                ),

                'payment_details' => array(
                    'fields' => array(
                        'transaction_id' => array('label' => __('Transaction ID#'), 'type' => 'label', 'value' => $model->getData('transaction_id')),
                        'created_at' => array(
                            'label' => __('Transaction Date'),
                            'value' => $model->getData('created_at'),
                            'type' => 'label',
                        ),
                        'payment_method' => array(
                            'label' => __('Transaction Mode'),
                            'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                            'type' => 'label',
                        ),
                        'transaction_type' => array(
                            'label' => __('Transaction Type'),
                            'value' => ($model->getData('transaction_type') == 0) ? __('Credit Type') : __('Debit Type'),
                            'type' => 'label',
                        ),
                        'total_shipping_amount' => array(
                            'label' => __('Total Shipping Amount'),
                            'value' => $this->priceCurrency->format($model->getData('total_shipping_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'amount' => array(
                            'label' => __('Amount'),
                            'value' => $this->priceCurrency->format($model->getData('amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'fee' => array(
                            'label' => __('Adjustment Amount'),
                            'value' => $this->priceCurrency->format($model->getData('fee'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'net_amount' => array(
                            'label' => __('Net Amount'),
                            'value' => $this->priceCurrency->format($model->getData('net_amount'), false, 2, null, $model->getCurrency()),
                            'type' => 'label',
                        ),
                        'notes' => array(
                            'label' => __('Notes'),
                            'value' => $model->getData('notes'),
                            'type' => 'label',
                        ),
                    ),
                    'legend' => __('Transaction Details')
                ),
            );
        }

        return array($model, $fieldsets);
    }


    /**
     * Preparing global layout You can redefine this method in child classes for changin layout
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Vpayments\View\Element')
        );

        return parent::_prepareLayout();
    }

    /**
     * Get form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Get form object
     *
     * @deprecated deprecated since version 1.2
     * @see getForm()
     * @return \Magento\Framework\Data\Form
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
     * @param \Magento\Framework\Data\Form $form
     * @return View
     */
    public function setForm(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->getUrl());
        return $this;
    }


    /**
     * This method is called before rendering HTML
     * @return \Ced\CsMarketplace\Block\Vendor\AbstractBlock|View
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @return $this
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param  $fieldset
     * @param array $exclude attributes that should be skipped
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
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

                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
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
                    $element->setFormat($this->timezone->getDateFormatWithLongYear());
                } else if ($inputType == 'datetime') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setTime(true);
                    $element->setStyle('width:50%;');
                    $element->setFormat(
                        $this->timezone->getDateTimeFormat(\Magento\Framework\Stdlib\DateTime\Timezone::FORMAT_TYPE_SHORT)
                    );
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }
    }

    /**
     * Add new element type
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $baseElement
     */
    protected function _addElementTypes($baseElement)
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
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }

    /**
     * back Link url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', array('_secure' => true, '_nosid' => true));
    }


}
