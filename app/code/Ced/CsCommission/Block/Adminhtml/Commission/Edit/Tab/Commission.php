<?php
namespace Ced\CsCommission\Block\Adminhtml\Commission\Edit\Tab;
class Commission extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;
    protected $_vendorFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_categoryFactory = $categoryFactory;
        $this->_vendorFactory = $vendorFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
		/* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('cscommission_commission');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        //$form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Commission')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'category',
            'select',
            array(
                'name' => 'category',
                'label' => __('Category'),
                'title' => __('Category'),
                'options' => $this->_getCategoryOptions(),
                'required' => true,
                'after_element_html' => $this->getAfterElementHtml()
            )
        );
        /*
        $fieldset->addField(
            'vendor',
            'select',
            array(
                'name' => 'vendor',
                'label' => __('Vendor'),
                'title' => __('Vendor'),
                'options' => $this->_getVendorOptions(),
                'required' => true
            )
        );
        */
		$fieldset->addField(
            'method',
            'select',
            array(
                'name' => 'method',
                'label' => __('Calculation Method'),
                'title' => __('Calculation Method'),
                'required' => true,
                'options' => ['fixed'=>'Fixed','percentage'=>'Percentage']
            )
        );
		$fieldset->addField(
            'fee',
            'text',
            array(
                'name' => 'fee',
                'label' => __('Commission Fee'),
                'title' => __('Commission Fee'),
                'required' => true,
            )
        );
		$fieldset->addField(
            'priority',
            'text',
            array(
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'class' => 'validate-zero-or-greater',
                'required' => true,
            )
        );

        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();   
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Commission');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Commission');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Get category options
     *
     * @return array
     */
    protected function _getCategoryOptions()
    {
        $items = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )/*->setPageSize(
            3
        )*/->load()->getItems();

        $result = [];
        foreach($items as $item) {
            
            $result[$item->getEntityId()] = $item->getName();
        }
       
        return $result;
    }

    /**
     * Get vendor options
     *
     * @return array
     */
    protected function _getVendorOptions()
    {
        $items = $this->_vendorFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )/*->setPageSize(
            3
        )*/->load()->getItems();

        $result = [];
        $result[0] = 'All';
        foreach($items as $item) {
            
            $result[$item->getEntityId()] = $item->getName();
        }
       
        return $result;
    }

    /**
     * Attach new category dialog widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $widgetOptions = $this->_jsonEncoder->encode(
            [
                'suggestOptions' => [
                    'source' => $this->getUrl('catalog/category/suggestCategories'),
                    'valueField' => '#category',
                    'className' => 'category-select',
                    'multiselect' => false,
                    'showAll' => true,
                ],
                'saveCategoryUrl' => $this->getUrl('catalog/category/save'),
            ]
        );
        //TODO: JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
<script>
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#category_ids-template' available
        $('#new-category').mage('newCategoryDialog', $widgetOptions);
    });
});
</script>
HTML;
    }
}
