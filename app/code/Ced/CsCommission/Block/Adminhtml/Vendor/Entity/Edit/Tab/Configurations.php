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
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Configurations extends \Magento\Config\Block\System\Config\Form implements TabInterface
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Block\System\Config\Form\Fieldset\Factory $fieldsetFactory,
        \Magento\Config\Block\System\Config\Form\Field\Factory $fieldFactory,
        array $data = []
    ) {
        $this->_configFactory = $configFactory;
        $this->_configStructure = $configStructure;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_fieldFactory = $fieldFactory;
        $this->_coreRegistry = $registry;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $configFactory,
            $configStructure,
            $fieldsetFactory,
            $fieldFactory
        );
    }

    public function getTabLabel()
    {
        return __('Commission Configuration');
    }

    public function getTabTitle()
    {
        return __('Commission Configuration');
    }

    public function canShowTab()
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        return !$this->canShowButton();
    }

    public function getAfter()
    {
        return 'vpayments';
    }

    public function canShowButton()
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            return true;
        }
        return false;
    }

    protected function _initObjects()
    {
        parent::_initObjects();
        return $this;
    }

    protected function _prepareForm()
    {
        try {
            $this->initForm();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getSectionCode()
    {
        return 'ced_csmarketplace';
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isAjax()) {
            return parent::_toHtml();
        }
        $parent = '<div id="vendor_group_configurations_section">' . parent::_toHtml() . '</div>';
        if (strlen($parent) <= 50) {
            $parent .= '<div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>' .
                __('No Configurations are Available for Current Configuration Scope. 
                Please Up the Configuration Scope by One Level.') . '</span></li></ul></li></ul></div>';
            return $parent;
        }
        return $parent;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initForm()
    {
        $this->_initObjects();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $current = $this->getSectionCode();
        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        $website = $this->getWebsiteCode();
        $store = $this->getStoreCode();
        if ($section && $section->isVisible($website, $store)) {
            foreach ($section->getChildren() as $group) {
                if ($group->getId() == 'vpayments') {
                    $this->_initGroup($group, $section, $form);
                }
            }
        }

        $this->setForm($form);
        return $this;
    }

    public function initFields(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        \Magento\Config\Model\Config\Structure\Element\Group $group,
        \Magento\Config\Model\Config\Structure\Element\Section $section,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $extraConfigGroups = [];

        /** @var $element \Magento\Config\Model\Config\Structure\Element\Field */
        foreach ($group->getChildren() as $element) {
            if ($element instanceof \Magento\Config\Model\Config\Structure\Element\Group) {
                $this->_initGroup($element, $section, $fieldset);
            } else {
                $path = $element->getConfigPath() ?: $element->getPath($fieldPrefix);

                if ($this->_coreRegistry->registry('vendor_data') &&
                    is_object($this->_coreRegistry->registry('vendor_data')) &&
                    $this->_coreRegistry->registry('vendor_data')->getId()
                ) {
                    $path = 'v'.$this->_coreRegistry->registry('vendor_data')->getId() . '/' . $path;
                }

                if ($element->getSectionId() != $section->getId()) {
                    $groupPath = $element->getGroupPath();
                    if (!isset($extraConfigGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject->extendConfig(
                            $groupPath,
                            false,
                            $this->_configData
                        );
                        $extraConfigGroups[$groupPath] = true;
                    }
                }
                $this->_initElement($element, $fieldset, $path, $fieldPrefix, $labelPrefix);
            }
        }
        return $this;
    }

    public function getConfigValue($path)
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            $paths = 'v'.$this->_coreRegistry->registry('vendor_data')->getId() . '/' . $path;
            if ($this->_scopeConfig->getValue($paths, $this->getScope(), $this->getScopeCode()) != null) {
                return $this->_scopeConfig->getValue($paths, $this->getScope(), $this->getScopeCode());
            }
        }

        return $this->_scopeConfig->getValue($path, $this->getScope(), $this->getScopeCode());
    }

    /**
     * Initialize form element
     *
     * @param \Magento\Config\Model\Config\Structure\Element\Field $field
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $path
     * @param string $fieldPrefix
     * @param string $labelPrefix
     * @return void
     */
    protected function _initElement(
        \Magento\Config\Model\Config\Structure\Element\Field $field,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $path,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        $data = null;
        if (array_key_exists($path, $this->_configData)) {
            $data = $this->_configData[$path];
        } elseif ($field->getConfigPath() !== null) {
            $data = $this->getConfigValue($field->getConfigPath());
        } else {
            $data = $this->getConfigValue($path);
        }

        $fieldRendererClass = $field->getFrontendModel();
        if ($fieldRendererClass) {
            $fieldRenderer = $this->_layout->getBlockSingleton($fieldRendererClass);
        } else {
            $fieldRenderer = $this->_fieldRenderer;
        }

        $fieldRenderer->setForm($this);
        $fieldRenderer->setConfigData($this->_configData);

        $elementName = $this->_generateElementName($field->getPath(), $fieldPrefix);
        $elementId = $this->_generateElementId($field->getPath($fieldPrefix));

        if ($field->hasBackendModel()) {
            $backendModel = $field->getBackendModel();
            $backendModel->setPath(
                $path
            )->setValue(
                $data
            )->setWebsite(
                $this->getWebsiteCode()
            )->setStore(
                $this->getStoreCode()
            )->afterLoad();
            $data = $backendModel->getValue();
        }

        $dependencies = $field->getDependencies($fieldPrefix, $this->getStoreCode());
        $this->_populateDependenciesBlock($dependencies, $elementId, $elementName);

        $sharedClass = $this->_getSharedCssClass($field);
        $requiresClass = $this->_getRequiresCssClass($field, $fieldPrefix);

        $formField = $fieldset->addField(
            $elementId,
            $field->getType(),
            [
                'name' => $elementName,
                'label' => $field->getLabel($labelPrefix),
                'comment' => $field->getComment($data),
                'tooltip' => $field->getTooltip(),
                'hint' => $field->getHint(),
                'value' => $data,
                'inherit' => $this->isInherit($path),
                'class' => $field->getFrontendClass() . $sharedClass . $requiresClass,
                'field_config' => $field->getData(),
                'scope' => $this->getScope(),
                'scope_id' => $this->getScopeId(),
                'scope_label' => $this->getScopeLabel($field),
                'can_use_default_value' => true,
                'can_use_website_value' => false
            ]
        );
        $field->populateInput($formField);

        if ($field->hasValidation()) {
            $formField->addClass($field->getValidation());
        }
        if ($field->getType() == 'multiselect') {
            $formField->setCanBeEmpty($field->canBeEmpty());
        }
        if ($field->hasOptions()) {
            $formField->setValues($field->getOptions());
        }
        $formField->setRenderer($fieldRenderer);
    }

    public function isInherit($path)
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            $data = $this->_scopeConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            if ($data != '') {
                return false;
            }
            return true;
        }
        return true;
    }
}
