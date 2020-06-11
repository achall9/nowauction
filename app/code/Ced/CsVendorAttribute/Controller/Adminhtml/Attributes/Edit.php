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
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    protected $_entityTypeId;

    protected $session;

    protected $entity;

    protected $attribute;

    /**
     * Edit constructor.
     * @param Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session $session,
        \Magento\Eav\Model\Entity $entity,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->session = $session;
        $this->entity = $entity;
        $this->attribute = $attribute;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {        
        $id = $this->getRequest()->getParam('attribute_id');

        $model = $this->attribute->create();

        if ($id) {
        	$storeId = (int) $this->getRequest()->getParam('store', 0);
            $model->setStoreId($storeId)->load($id);
        
            if (!$model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $this->_entityTypeId = $this->entity->setType(
                'csmarketplace_vendor'
            )->getTypeId();
            
            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        // set entered data if was error when we do save
        $data = $this->session->getAttributeData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('entity_attribute', $model);
        
        $item = $id ? __('Edit Vendor Attribute') : __('New Vendor Attribute');
        
        $resultPage = $this->createActionPage($item);
        
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Vendor Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }   
}