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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Controller\Adminhtml\Vpayments;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Save
 * @package Ced\CsTransaction\Controller\Adminhtml\Vpayments
 */
class Save extends \Ced\CsMarketplace\Controller\Adminhtml\Vpayments\Save
{
    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $vpaymentFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $cstransactionItems;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderitemCollection;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $vordersCollection;

    /**
     * Save constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $cstransactionItems
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderitemCollection
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Directory\Helper\Data $helperData
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Ced\CsMarketplace\Helper\Mail $mailHelper
     */
    public function __construct(
        \Ced\CsTransaction\Model\ItemsFactory $itemsFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $cstransactionItems,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderitemCollection,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection,
        Context $context,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Directory\Helper\Data $helperData,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->orderFactory = $orderFactory;
        $this->cstransactionItems = $cstransactionItems;
        $this->orderitemCollection = $orderitemCollection;
        $this->vordersCollection = $vordersCollection;
        $this->itemsFactory = $itemsFactory;
        parent::__construct($context, $vpaymentFactory, $helperData, $currencyFactory, $mailHelper);
    }

    /**
     * Customer edit action
     *
     * @return bool|\Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $params = $this->getRequest()->getParams();
                $type = isset($params['type']) && in_array($params['type'],
                    array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates())) ?
                    $params['type'] : \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
                $itemid = json_decode($data['order_item_id']);

                $model = $this->vpaymentFactory->create();
                //transaction id check
                $transaction_id_unique = $model->getCollection()->addFieldToFilter('transaction_id', $data['transaction_id'])->getData();

                if (sizeof($transaction_id_unique) > 0) {
                    $this->messageManager->addErrorMessage("Transaction id already exist. Please enter another transaction id");
                    $resultRedirects = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirects->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirects;
                }
                //transaction id end

                $amount_desc = isset($data['amount_desc']) ? $data['amount_desc'] : json_encode(array());
                $shipping_info = isset($data['shipping_info']) ? $data['shipping_info'] : json_encode(array());
                $total_shipping_amount = isset($data['total_shipping_amount']) ? $data['total_shipping_amount'] : 0;

                $total_amount = json_decode($amount_desc, true);

                $this->csmarketplaceHelper->logProcessedData($total_amount, \Ced\CsMarketplace\Helper\Data::VPAYMENT_TOTAL_AMOUNT);
                $baseCurrencyCode = $this->helperData->getBaseCurrencyCode();
                $allowedCurrencies = $this->currencyFactory->getConfigAllowCurrencies();
                $rates = $this->currencyFactory->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
                $data['base_to_global_rate'] = isset($data['currency']) && isset($rates[$data['currency']]) ? $rates[$data['currency']] : 1;

                if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
                    $oldAmountDesc = [];
                    $base_amount = 0;
                    if (count($total_amount) > 0) {
                        foreach ($total_amount as $orderid => $items) {
                            foreach ($items as $vorderItemId => $value) {
                                $vorder = $this->orderFactory->create()->load($orderid);
                                $incrementId = $vorder->getIncrementId();
                                if (isset($oldAmountDesc[$incrementId])) {
                                    $oldAmountDesc[$incrementId] += $value;
                                } else {
                                    $oldAmountDesc[$incrementId] = $value;
                                }
                                $base_amount += (float)$value;
                            }
                        }
                    }

                    $oldAmountDesc = json_encode($oldAmountDesc);
                    $data['item_wise_amount_desc'] = $data['amount_desc'];
                    $data['amount_desc'] = $oldAmountDesc;
                    $base_amount += $total_shipping_amount;

                    //check if RMA Fee is included
                    if (isset($data['processed_orders'])) {
                        $rmaFee = 0;
                        foreach ($data['processed_orders'] as $val)
                            $rmaFee += $val;
                        $base_amount += $rmaFee;
                    }

                } else {
                    if (isset($data['base_fee'])) {
                        if ($data['base_fee'] > $data['base_amount']) {
                            $this->messageManager->addError(__('Adjustment Amount cannot be greater that net paid amount'));
                            return $this->_redirect('csmarketplace/vorders/index');
                        }
                    }
                    $oldAmountDesc = [];
                    $paymentData = [];
                    $paymentData['vendor_id'] = $data['vendor_id'];
                    $paymentData['transaction_id'] = $data['transaction_id'];
                    $order_ids = [];
                    $adjustment_amount = 0;
                    if (isset($data['base_fee'])) {
                        if ($data['base_fee'] > 0)
                            $adjustment_amount = $data['base_fee'];
                    }
                    foreach ($itemid as $_id) {
                        $item_model = $this->itemsFactory->create()->load($_id);
                        $order_ids[] = $item_model->getOrderId();
                        $item_model->setQtyPaid($item_model->getQtyReadyToPay());
                        $item_model->setQtyReadyToPay(0);
                        $item_model->setAmountPaid($data['base_amount'] + $adjustment_amount);
                        $item_model->setItemPaymentState(\Ced\CsTransaction\Model\Items::STATE_PAID);
                        $item_model->save();
                        if (isset($oldAmountDesc[$item_model->getOrderIncrementId()])) {
                            $oldAmountDesc[$item_model->getOrderIncrementId()] += $item_model->getTotalInvoicedAmount();
                        } else {
                            $oldAmountDesc[$item_model->getOrderIncrementId()] = $item_model->getTotalInvoicedAmount();
                        }
                    }
                    $paymentData['total_shipping_amount'] = isset($data['total_shipping_amount']) ? $data['total_shipping_amount'] : 0;

                    $data['base_amount'] = $data['base_amount'] - $adjustment_amount;
                    $paymentData['amount_desc'] = json_encode($oldAmountDesc);
                    $paymentData['base_amount'] = $data['base_amount'];
                    $paymentData['amount'] = $data['base_amount'];
                    $paymentData['currency'] = $data['currency'];
                    $paymentData['net_amount'] = $data['base_amount'];
                    $paymentData['base_net_amount'] = $data['base_amount'];
                    $paymentData['balance'] = $data['base_amount'];
                    $paymentData['base_balance'] = $data['base_amount'];
                    if (isset($data['notes']))
                        $paymentData['notes'] = $data['notes'];
                    $paymentData['transaction_type'] = $type;
                    $paymentData['payment_code'] = $data['payment_code'];
                    $paymentData['payment_detail'] = isset($data['payment_detail']) ? $data['payment_detail'] : 'n/a';
                    $paymentData['status'] = $model->getOpenStatus();
                    $paymentData['base_to_global_rate'] = $data['base_to_global_rate'];
                    $paymentData['item_wise_amount_desc'] = $data['amount_desc'];
                    $paymentData['tax'] = 0.00;
                    $paymentData['payment_method'] = '0';
                    $paymentData['base_fee'] = $adjustment_amount;

                    $model->addData($paymentData);
                    $model->save();
                    foreach ($data['processed_orders'] as $order_inc_id) {
                        $order_id = $this->orderFactory->create()->loadByIncrementId($order_inc_id)->getId();

                        $orderItemData = $this->orderitemCollection->create()
                            ->addFieldToFilter('vendor_id', $data['vendor_id'])
                            ->addFieldToFilter('order_id', $order_id)
                            ->addFieldToFilter('parent_item_id', array('null' => true), 'left');

                        $orderItemData->getSelect()->reset('columns')->columns(['total_ordered_qty' => 'SUM(qty_ordered)']);
                        $totalOrderedQty = $orderItemData->getFirstItem()->getTotalOrderedQty();

                        $total_qty_paid = $this->cstransactionItems->create()
                            ->addFieldToFilter('vendor_id', $data['vendor_id'])
                            ->addFieldToFilter('order_id', $order_id);

                        $total_qty_paid->getSelect()->reset('columns')
                            ->columns(['total_orderqty_paid' => new \Zend_Db_Expr('SUM((qty_paid)+(qty_refunded))')]);
                        $totalPaidQty = $total_qty_paid->getFirstItem()->getTotalOrderqtyPaid();

                        $vorder = $this->vordersCollection->create()
                            ->addFieldToFilter('vendor_id', $data['vendor_id'])
                            ->addFieldToFilter('order_id', $order_inc_id)
                            ->getFirstItem();

                        if ($totalOrderedQty == $totalPaidQty) {
                            $vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_PAID);
                            $vorder->save();
                        } else {
                            $vorder->setPaymentState(\Ced\CsOrder\Model\Vorders::STATE_PARTIALLY_PAID);
                            $vorder->save();
                        }
                    }
                }

                $this->csmarketplaceHelper
                    ->logProcessedData($model->getData(), \Ced\CsMarketplace\Helper\Data::VPAYMENT_CREATE);

                $this->messageManager->addSuccessMessage(__('Payment is  successfully saved'));
                $this->_session->setFormData(false);
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->csmarketplaceHelper->logException($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setFormData($data);
                return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        return false;
    }
}
