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

namespace Ced\CsVendorAttribute\Controller\Adminhtml\Attributes;

use \Magento\Framework\Exception\AlreadyExistsException;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\BuildFactory
     */
    protected $buildFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;
    
    protected $_entityTypeId;

    protected $entity;

    protected $attribute;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Ced\CsMarketplace\Model\Vendor\Attribute $attribute
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Model\Entity $entity,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute
    ) {
    	
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->buildFactory = $buildFactory;
        $this->filterManager = $filterManager;
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->entity = $entity;
        $this->attribute = $attribute;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
   
        $data = $this->getRequest()->getPostValue();
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $this->_entityTypeId = $this->entity->setType(
        		'csmarketplace_vendor'
        )->getTypeId();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $setId = $this->getRequest()->getParam('set');
            $attributeSet = null;
            if (!empty($data['new_attribute_set_name'])) {
                $name = $this->filterManager->stripTags($data['new_attribute_set_name']);
                $name = trim($name);

                try {
                    /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
                    $attributeSet = $this->buildFactory->create()
                        ->setEntityTypeId($this->_entityTypeId)
                        ->setSkeletonId($setId)
                        ->setName($name)
                        ->getAttributeSet();
                } catch (AlreadyExistsException $alreadyExists) {
                    $this->messageManager->addError(__('An attribute set named \'%1\' already exists.', $name));
                    $this->messageManager->setAttributeData($data);
                    return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the attribute.'));
                }
            }
            $redirectBack = $this->getRequest()->getParam('back', false);
            $model = $this->attribute->create();
			$model->setStoreId($storeId);
            $attributeId = $this->getRequest()->getParam('attribute_id');

            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $frontendLabel = $this->getRequest()->getParam('frontend_label');
            $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
            if (strlen($this->getRequest()->getParam('attribute_code')) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $resultRedirect->setPath('*/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                }
            }
            $data['attribute_code'] = $attributeCode;

            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $inputType \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator */
                $inputType = $this->validatorFactory->create();
                if (!$inputType->isValid($data['frontend_input'])) {
                   foreach ($inputType->getMessages() as $message) {
                       $this->messageManager->addError($message);
                    }
                    return $resultRedirect->setPath('*/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                }
            }

            if ($attributeId) {
                $model->load($attributeId);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This attribute no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addError(__('We can\'t update the attribute.'));
                    $this->_session->setAttributeData($data);
                    return $resultRedirect->setPath('*/*/');
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );
            }

            $data += ['is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$model->getIsUserDefined() && $model->getId()) {
                // Unset attribute field for system attributes
                unset($data['apply_to']);
            }
            $data['store_id']=$storeId;
            if (!isset($data['is_configurable'])) {
            	$data['is_configurable'] = 0;
            }
            	
            
            if (!isset($data['is_filterable'])) {
            	$data['is_filterable'] = 0;
            }
            	
            if (!isset($data['is_filterable_in_search'])) {
            	$data['is_filterable_in_search'] = 0;
            }
            
            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            	if(in_array($data['frontend_input'],array('multiimage','file','multifile'))) {
            		$data['backend_type'] = 'text';
            	} else {
            		$data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            	}
            }
            
            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
            	$data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }
            
            if(!isset($data['apply_to'])) {
            	$data['apply_to'] = array();
            }
            $model->addData($data);
			
            if (!$attributeId) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }
            if($this->getRequest()->getParam('set')) {
            	$model->setAttributeSetId($this->getRequest()->getParam('set'));
            }
            	
            if ($model->getData('frontend_input') == 'multiselect') {
            	$model->setData('source_model','Magento\Eav\Model\Entity\Attribute\Source\Table');
            }
            $groupCode = $this->getRequest()->getParam('group');
           
            if ($setId && $groupCode) {
               
                $attributeSetId = $attributeSet ? $attributeSet->getId() : $setId;
                $groupCollection = $attributeSet
                    ? $attributeSet->getGroups()
                    : $this->groupCollectionFactory->create()->setAttributeSetFilter($attributeSetId)->load();
                foreach ($groupCollection as $group) {
                    if ($group->getAttributeGroupCode() == $groupCode) {
                        $attributeGroupId = $group->getAttributeGroupId();
                        break;
                    }
                }
                $model->setAttributeSetId($attributeSetId);
                $model->setAttributeGroupId($attributeGroupId);
            }

            try {
                $model->save();
                $model->addToGroup($this->getRequest()->getParam('group'));
                $this->messageManager->addSuccess(__('You saved the vendor attribute.'));

                $this->_attributeLabelCache->clean();
                $this->_session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = [
                        'attributeId' => $this->getRequest()->getParam('product'),
                        'attribute' => $model->getId(),
                        '_current' => true,
                        'product_tab' => $this->getRequest()->getParam('product_tab'),
                    ];
                    if (!is_null($attributeSet)) {
                        $requestParams['new_attribute_set_id'] = $attributeSet->getId();
                    }
                    $resultRedirect->setPath('catalog/product/addAttribute', $requestParams);
                } elseif ($redirectBack) {
                    $resultRedirect->setPath('*/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('*/*/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setAttributeData($data);
                return $resultRedirect->setPath('*/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}

