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

use Magento\Framework\View\Result\PageFactory;

class Delete extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected $__entityTypeId;

    protected $entity;

    protected $attribute;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Magento\Eav\Model\Entity $entity,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attribute
    )
    {
        $this->entity = $entity;
        $this->attribute = $attribute;
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $this->_entityTypeId = $this->entity->setType(
            'csmarketplace_vendor'
        )->getTypeId();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->attribute->create();

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('We can\'t delete the attribute.'));
                return $resultRedirect->setPath('*/*/');
            }

            try {
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Vendor attribute.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an attribute to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
