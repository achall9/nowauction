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
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Block\Adminhtml\Promo\Quote\Edit\Coupons;

/**
 * Class Grid
 * @package Ced\GiftCard\Block\Adminhtml\Promo\Quote\Edit\Coupons
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Ced\GiftCard\Model\GiftTemplate
     */
    protected $_giftTemplate;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\GiftCard\Model\ResourceModel\GiftCoupon\Collection $salesRuleCoupon,
        \Ced\GiftCard\Model\GiftTemplate $giftTemplate,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ){
        $this->_giftTemplate = $giftTemplate;
        $this->_coreRegistry = $coreRegistry;
        $this->_salesRuleCoupon = $salesRuleCoupon;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $template_id = 0;
        if(null !== $this->_coreRegistry->registry('template_id'))
            $template_id = $this->_coreRegistry->registry('template_id');
        if ($template_id == 0){
            $template_id = $this->getTemplateId();
        }
        /**
         * @var \Ced\GiftCard\Model\ResourceModel\GiftCoupon\Collection $collection
         */
        $collection = $this->_salesRuleCoupon->addFieldToFilter('template_id', $template_id);
        //print_r($collection->getData());die;
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return int|mixed
     */
    public function getTemplateId(){

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = $this->_giftTemplate;
            $model->load($id);
            return $model->getId();
        }else{
            return 0;
        }

    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'code',
            [
                'header' => __('Coupon Code'),
                'type' => 'text',
                'index' => 'code'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'date',
                'align' => 'center',
            ]
        );
        $this->addColumn(
            'expiration_date',
            [
                'header' => __('Expiration Date'),
                'index' => 'expiration_date',
                'type' => 'date',
                'align' => 'center',
            ]
        );
        $currencyCode = $this->getCurrencyCode();
        $rate = $this->getRate($currencyCode);
        $this->addColumn(
            'coupon_price',
            [
                'header' => __('Actual Amount'),
                'index' => 'coupon_price',
                'align' => 'center',
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'rate' => $rate,
            ]
        );

        $this->addColumn(
            'current_amount',
            [
                'header' => __('Current Amount'),
                'index' => 'current_amount',
                'type' => 'currency',
                'align' => 'center',
                'currency_code' => $currencyCode,
                'rate' => $rate,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'time_used',
            [
                'header' => __('Times Used'),
                'index' => 'time_used',
                'width' => '50',
                'type' => 'number'
            ]
        );
 

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string|\Magento\Directory\Model\Currency $currencyCode
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
    /**
     * Get currency rate (base to given currency)
     *
     * @param string|\Magento\Directory\Model\Currency $toCurrency
     * @return float
     */
    public function getRate($toCurrency)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        $template_id = 0;
        if(null !== $this->_coreRegistry->registry('template_id'))
            $template_id = $this->_coreRegistry->registry('template_id');

        return $this->getUrl('giftcard/templates/grid', ['_current' => true, 'id'=>$template_id]);
    }

}
