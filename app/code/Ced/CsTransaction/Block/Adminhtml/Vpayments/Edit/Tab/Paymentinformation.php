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
 * @package     Ced_CsTransaction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Class Paymentinformation
 * @package Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab
 */
class Paymentinformation extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Paymentinformation
{

    /**
     * @var null
     */
    protected $_availableMethods = null;
    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $_vendor;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $_resourceCollection;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $currencyInterface;

    /**
     * Paymentinformation constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $currencyInterface
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    )
    {
        $this->_resourceCollection = $collectionFactory;
        $this->vordersFactory = $vordersFactory;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->currencyInterface = $currencyInterface;
        parent::__construct($context, $registry, $formFactory, $vendor, $directoryHelper, $data);
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Paymentinformation|\Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) && in_array($params['type'], array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates())) ? $params['type'] : \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_fields',
            array('legend' => __('Transaction Information')));
        $vendorId = $this->getRequest()->getParam('vendor_id', 0);
        $base_amount = $this->getRequest()->getPost('total', 0);
        $shipCheck = $this->getRequest()->getPost('shippingcheck');
        $shippings = $this->getRequest()->getPost('shippings');

        $totalShippingAmount = 0;
        $shippingInfo = array();
        if (is_array($shipCheck)) {
            foreach ($shipCheck as $key => $value) {
                if (isset($shippings[$key])) {
                    $shippingInfo[$key] = $shippings[$key];
                    $totalShippingAmount = $totalShippingAmount + $shippings[$key];
                }
            }
        }

        $base_amount = $this->getRequest()->getPost('total', 0);
        $amountDesc = $this->getRequest()->getPost('orders');

        $vendor = $this->_resourceCollection->create()->toOptionArray($vendorId);
        $ascn = isset($vendor[$vendorId]) ? $vendor[$vendorId] : '';
        $fieldset->addField('vendor_id', 'hidden', array(
            'name' => 'vendor_id',
            'value' => $vendorId,
        ));
        $form->addField('order_item_id', 'hidden',
            array(
                'name' => 'order_item_id',
                'class' => 'required-entry',
                'value' => json_encode($this->getRequest()->getPostValue('order_item_id')),
                'readonly' => true,
            ));

        $form->addField('order_id', 'hidden',
            array(
                'name' => 'order_id',
                'class' => 'required-entry',
                'value' => json_encode($this->getRequest()->getPostValue('order_id')),
                'readonly' => true,
            ));

        $fieldset->addField('amount_desc', 'hidden', array(
            'name' => 'amount_desc',
            'value' => json_encode($amountDesc),
        ));
        $fieldset->addField('shipping_info', 'hidden', array(
            'name' => 'shipping_info',
            'value' => json_encode($shippingInfo),
        ));
        $fieldset->addField('total_shipping_amount', 'hidden', array(
            'name' => 'total_shipping_amount',
            'value' => $totalShippingAmount,
        ));
        $fieldset->addField('currency', 'hidden', array(
            'name' => 'currency',
            'value' => $this->_directoryHelper->getBaseCurrencyCode(),
        ));
        $fieldset->addField('vendor_name', 'label', array(
            'label' => __('Vendor'),
            'after_element_html' => '<a target="_blank" href="' . $this->getUrl('csmarketplace/adminhtml_vendor/edit/', array('vendor_id' => $vendorId, '_secure' => true)) . '" title="' . $ascn . '">' . $ascn . '</a>',
        ));
        $fieldset->addField('base_amount', 'text', array(
            'label' => __('Amount'),
            'class' => 'required-entry validate-greater-than-zero',
            'required' => true,
            'name' => 'base_amount',
            'value' => $base_amount,
            'readonly' => 'readonly',
            'after_element_html' => '<b>[' . $this->_directoryHelper->getBaseCurrencyCode() . ']</b><small><i>' . __('Readonly field') . '</i>.</small>',
        ));

        $fieldset->addField('payment_code', 'select', array(
            'label' => __('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'onchange' => !$type ? 'vpayment.changePaymentDatail(this)' : 'vpayment.changePaymentToOther(this)',
            'name' => 'payment_code',
            'values' => $this->_vendor->getPaymentMethodsArray($vendorId),
            'after_element_html' => '<small id="beneficiary-payment-detail">' . __('Select Payment Method') . '</small><script type="text/javascript"> var vpayment = new ced_csmarketplace("' . $this->getUrl('*/*/getdetail', array('vendor_id' => $vendorId)) . '","");</script>',
        ));

        $fieldset->addField('payment_code_other', 'text', array(
            'label' => '',
            'style' => 'display: none;',
            'disbaled' => 'true',
            'name' => 'payment_code',
            //'required' => true,
            'after_element_html' => '<script type="text/javascript">
	                                            require(["jquery"], function($){
	                                                 $("#payment_code").change(function () {
	                                                     var payment_code = $("#payment_code").val();
	                                                  		$("#payment_code_other").val(payment_code)  ;
	                                                     });    
	                                            });
	                                      </script>',
        ));

        $fieldset->addField('base_fee', 'text', array(
            'label' => __('Adjustment Amount'),
            'class' => 'validate-number',
            'required' => false,
            'name' => 'base_fee',
            'after_element_html' => '<b>[' . $this->_directoryHelper->getBaseCurrencyCode() . ']</b><small>' . __('Enter adjustment amount in +/- (if any)') . '</small>',
        ));


        $fieldset->addField('transaction_id', 'text', array(
            'label' => __('Transaction Id'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'transaction_id',
            'after_element_html' => '<small>' . __('Enter transaction id') . '</small>',
        ));


        $fieldset->addField('textarea', 'textarea', array(
            'label' => __('Notes'),
            'required' => false,
            'name' => 'notes',
        ));
        if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT) {
            $fieldset->addField('amount_description', 'label', array(
                'label' => __('Amount Description'),
                'required' => true,
                'name' => 'amount_description',
                'after_element_html' => $this->getAmountDescriptionHtml()
            ));
        }
        $form->setHtmlIdPrefix('page_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}payment_code",
                'payment_code'
            )
                ->addFieldMap(
                    "{$htmlIdPrefix}payment_code_other",
                    'payment_code_other'
                )
                ->addFieldDependence(
                    'payment_code_other',
                    'payment_code',
                    'other'
                )
        );
        return $this;
    }

    /**
     * prepare continue url for vendor
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl('*/*/*', array(
            '_current' => true,
            '_secure' => true,
            'vendor_id' => '{{vendor_id}}'
        ));
    }

    /**
     * @return string
     */
    public function getAmountDescriptionHtml()
    {

        $orderArray = array();
        $data = $this->getRequest()->getPost();

        $shippingprice = array();
        $price = array();
        $comission = array();
        $ids = array();
        if (isset($data['shippingcheck'])) {
            foreach ($data['shippingcheck'] as $key => $value) {
                $ids[] = $key;
            }
        }

        if (isset($data['shippings'])) {
            foreach ($data['shippings'] as $_id => $shippingvalue) {
                if (in_array($_id, $ids)) {
                    $incrementid = $this->vordersFactory->create()->load($_id)->getOrderId();
                    $_ids = $this->orderFactory->create()->load($incrementid, 'increment_id')->getId();
                    $shippingprice[$_ids] = $shippingvalue;

                } else {
                    $incrementid = $this->vordersFactory->create()->load($_id)->getOrderId();
                    $_ids = $this->orderFactory->create()->load($incrementid, 'increment_id')->getId();
                    $shippingprice[$_ids] = 0;

                }
            }
        }

        foreach ($data['orders'] as $key => $value) {
            if (is_array($value)) {
                $item_price = 0;
                foreach ($value as $itemId => $itemPrice) {
                    $item_price += $itemPrice;
                }
                $price[$key] = $item_price;

            }
        }

        if (isset($data['comissionfee'])) {
            foreach ($data['comissionfee'] as $orderid => $comissionfess) {
                $comissionprice_price = 0;
                if (is_array($comissionfess)) {
                    foreach ($comissionfess as $itemid => $comissionPrice) {
                        $comissionprice_price += $comissionPrice;
                    }
                    $comission[$orderid] = $comissionprice_price;
                } else {
                    $comission[$orderid] = 0;
                }
            }
        }

        $vendorId = $this->getRequest()->getParam('vendor_id');

        $orderIds = $this->getRequest()->getParam('orders');
        $order_enable = $this->scopeConfig->getValue('ced_vorders/general/vorders_active');
        $orderArray['service_tax'] = $this->scopeConfig->getValue('ced_vpayments/general/service_tax');
        $orderArray['headers'] = array('increment_id' => 'Order Id', 'order_grand_total' => 'Grand Total', 'commission_fee' => 'Commision Fee', 'shipping_fee' => 'Shipping Fee');
        $orderArray['pricing_columns'] = array('order_grand_total', 'commission_fee', 'shipping_fee');

        if ($order_enable) {
            $shipCheck = $this->getRequest()->getPost('shippingcheck');
            $shippings = $this->getRequest()->getPost('shippings');
            $shippingInfo = [];
            if (is_array($shipCheck)) {
                foreach ($shipCheck as $key => $value) {
                    if (isset($shippings[$key])) {
                        $shippingInfo[$key] = $shippings[$key];
                    }
                }
            }
        }


        foreach ($orderIds as $id => $amount) {
            $inc_id = 0;
            if ($order_enable) {
                $inc_id = $this->orderFactory->create()->load($id)->getIncrementId();
            }

            $orderArray['values'][$inc_id] = array('increment_id' => $inc_id, 'order_grand_total' => $price[$id],
                'commission_fee' => $comission[$id] * -1);

            if ($order_enable) {
                if (isset($shippingprice[$id])) {
                    $orderArray['values'][$inc_id]['shipping_fee'] = +$shippingprice[$id];
                }
            }

        }
        $html = "";
        $html .= '<div class="entry-edit">
					
					<div class="grid" id="order-items_grid">
						<table cellspacing="0" class="data order-tables" border="1">
							<col width="100" />
							<col width="100" />
							<col width="150" />
							<col width="100" />
							<col width="100" />
							<thead>
								<tr style="background-color: rgb(81, 73, 67); color: white;"><th colspan="5"><h2 style="color:white;margin-top:10px">
Order Amount(s)
</h2></th></tr>
								<tr class="headings" style="height:25px; background-color: grey;">';
        foreach ($orderArray['headers'] as $title) {
            $html .= '<th class="no-link"><center>' . __($title) . '</center></th>';
        }
        $html .= '<th class="no-link"><center>Total</center></th>	
								</tr>
							</thead>
							<tbody>';

        $class = '';
        $trans_sum = 0;
        $serviceable_amount = 0;
        $commissionFee = 0;
        foreach ($orderArray['values'] as $info) {
            $class = ($class == 'odd') ? 'even' : 'odd';
            $html .= '<tr class="' . $class . '">';

            foreach ($orderArray['headers'] as $key => $title) {
                if (isset($info[$key])) {
                    $value = $info[$key];
                } else {
                    $value = "";
                }
                if ($key == 'increment_id') {
                    $html .= '<td><center>#' . $value . ' <input type="hidden" name="processed_orders[' . $value . ']" value="' . $value . '"></center></td>';
                } else {
                    $html .= '<td><center>' . $this->formatPrice($value) . '</center></td>';
                }

                if ($key == 'commission_fee') {
                    $commissionFee += $value;
                }
            }
            $total = 0;
            foreach ($orderArray['pricing_columns'] as $key) {
                $price_valu = isset($info[$key]) ? $info[$key] : 0;
                $total += $price_valu;
                if ($price_valu < 0) {
                    $serviceable_amount += $price_valu;
                }
            }

            $html .= '<td><center>' . $this->formatPrice($total) . '</center></td></tr>';

            $html .= '<input type="hidden" name="commission_total" value="' . $commissionFee . '" />';

            $trans_sum += $total;

        }

        $html .= '</tbody></table></div></div>';

        $service_tax = round($serviceable_amount * $orderArray['service_tax'] / 100, 2);
        $trans_sum += $service_tax;
        $html .= '<h3>' . __('Service Tax') . ' : ' . $this->formatPrice($service_tax) . '</h3>';
        $html .= '<h3>' . __('Total Amount') . ' : ' . $this->formatPrice($trans_sum) . '</h3>';

        $html .= '<script>document.getElementById("base_amount").value = ' . $trans_sum . ';</script>';
        return $html;
    }

    /**
     * @param $price
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function formatPrice($price)
    {
        if (!$price)
            return "-";
        $baseCurrency = $this->_directoryHelper->getBaseCurrencyCode();
        return $this->currencyInterface->getCurrency($baseCurrency)->toCurrency($price);
    }
}
