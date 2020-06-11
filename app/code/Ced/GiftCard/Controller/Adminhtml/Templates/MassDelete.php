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
 * @category    Ced
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 * @package Ced\GiftCard\Controller\Adminhtml\Templates
 */
class MassDelete extends Action
{
    const ALL = 'all';
    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftTemplate\CollectionFactory $giftTemplateFactory
     */
    protected $_giftTemplateFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory,
     * @param \Ced\GiftCard\Model\ResourceModel\GiftTemplate\CollectionFactory $giftTemplateFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\GiftCard\Model\ResourceModel\GiftTemplate\CollectionFactory $giftTemplateFactory
    )
    {
        $this->productFactory = $productFactory;
        $this->_giftTemplateFactory = $giftTemplateFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try { 
            $ids = $this->getRequest()->getPostValue('selected', self::ALL);
            $templates = $this->_giftTemplateFactory->create();
            if($ids != self::ALL){
                $templates->addFieldToFilter('id', ['in' => $ids]);
            }
            $unsuccessfulDelete = [];
            $catalog = $this->productFactory->create();
            $catalogCollection = $catalog->getCollection()->addFieldToFilter('type_id', 'giftcard');
            $notAllowToDelete = '';
            $notAllowToDeleteProducts = [];
            foreach ($templates as $template) {
                try{
                    $copyCollection = clone $catalogCollection;
                    $copyCollection->addAttributeToFilter('gift_template', $template->getId());
                    $count = $copyCollection->count();
                    if($count > 0){
                        $notAllowToDelete = $template->getId().' ( '.$count.' ), '.$notAllowToDelete;
                        $notAllowToDeleteProducts = array_merge(
                            $notAllowToDeleteProducts,
                            $copyCollection->getColumnValues('entity_id')
                        );
                    }else{
                        $template->getResource()->delete($template);
                    }
                }catch(\Exception $e){
                    $unsuccessfulDelete[] = $template->getId();
                }
            }

            if ($notAllowToDelete != '') {
                $this->messageManager->addErrorMessage(
                    __(
                        'Template with Ids already have assigned product [ %1 ]. eg. [Template Id ( Product Count )]',
                        $notAllowToDelete
                    )
                );
                $this->messageManager->addErrorMessage(
                    __(
                        'Please Remove them Before Deleting the templates. Assigned Product Ids [ %1 ]',
                        implode(", ", $notAllowToDeleteProducts)
                    )
                );
            }
            if (count($unsuccessfulDelete) > 0) {
                $this->messageManager->addErrorMessage(__('Unable to Delete some templates [ %1 ]',implode(", ", $unsuccessfulDelete)));
            }
        }catch(\Exception $e){ 
            $this->messageManager->addErrorMessage(__('Unable to Delete templates. Error Message %1',$e->getMessage()));
        }
        $this->messageManager->addSuccessMessage(__('Successfully Deleted Giftcard Templates.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $ruleID
     * @return bool
     */
    public function deleteRule($ruleID){
        $ruleModel = $this->productFactory->create();

        if (isset($ruleID) && $ruleID > 0) {
            try{
                $ruleModel = $ruleModel->load($ruleID);
                $ruleModel->getResource()->delete($ruleModel);
            }catch (\Exception $e){
                return false;
            }
        }
        return true;
    }
}
