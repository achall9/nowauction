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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Ced\CsMarketplace\Model\Vendor;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Front extends Generic
{
    protected $_coreRegistry;

    protected $setCollection;

    protected $vendor;

    protected $attributeCollection;

    protected $groupCollection;

    /**
     * Front constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Collection $setCollection
     * @param Vendor $vendor
     * @param Attribute\CollectionFactory $attributeCollection
     * @param Attribute\Group\Collection $groupCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Collection $setCollection,
        Vendor $vendor,
        Attribute\CollectionFactory $attributeCollection,
        Attribute\Group\Collection $groupCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->setCollection = $setCollection;
        $this->vendor = $vendor;
        $this->attributeCollection = $attributeCollection;
        $this->groupCollection = $groupCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $this->setForm($form);
        $vendor = $this->vendor;
        $entityTypeId  = $vendor->getEntityTypeId();
        $setIds =$this->setCollection->setEntityTypeFilter($entityTypeId)->getAllIds();
        $setId = isset($setIds[0])?$setIds[0]:0;

        $options = $this->getGroupOptions($setId, true);
        $attribute = $this->_coreRegistry->registry('entity_attribute');
        $groupName = '';

        foreach($this->getGroupOptions($setId, false) as $id=>$label) {
            $attributes = $this->attributeCollection->create()->setAttributeGroupFilter($id)->getAllIds();
            if(in_array($attribute->getId(), $attributes)) {
                $groupName = $label;
                break;
            }
        }

        $fieldset = $form->addFieldset('group_fieldset', array('legend'=>__('Group')));

        $element = $fieldset->addField(
            'group_select', 'select',
            array(
                'name'      => "group",
                'label'     => __('Group'),
                'required'  => true,
                'values'    => $options,
                'after_element_html' => $this->getChildHtml('csmarketplace_add_new_group_button'),
            )
        );
        if(strlen($groupName)) {
            $element->setValue($groupName);
        }

        return parent::_prepareForm();
    }

    /**
     * @param $setId
     * @param bool $flag
     * @return array
     */
    protected function getGroupOptions($setId,$flag = false)
    {

        $groupCollection = $this->groupCollection->setAttributeSetFilter($setId);


        $groupCollection->setSortOrder()
            ->load();

        $options = array();
        if($flag) {
            foreach ($groupCollection as $group) {
                $options[$group->getAttributeGroupName()] = __($group->getAttributeGroupName());
            }
        } else {
            foreach ($groupCollection as $group) {
                $options[$group->getId()] = $group->getAttributeGroupName();
            }
        }
        return     $options;
    }
}
