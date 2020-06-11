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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class VpaymentsSaveBefore
 * @package Ced\CsTransaction\Observer
 */
class VpaymentsSaveBefore implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $_vorders;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $_csorderHelper;

    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $_vtorders;

    /**
     * VpaymentsSaveBefore constructor.
     * @param RequestInterface $request
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsTransaction\Model\Items $vtorders
     */
    public function __construct(
        RequestInterface $request,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Model\Items $vtorders
    )
    {
        $this->request = $request;
        $this->_vorders = $vorders;
        $this->_csorderHelper = $csorderHelper;
        $this->_vtorders = $vtorders;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            if ($this->_csorderHelper->isActive()) {
                if (!$observer->getVpayment()->getId()) {
                    $data = $this->request->getPost();
                    $vpayment = $observer->getVpayment();
                    $type = $vpayment->getData('transaction_type');
                    if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
                        if (isset($data['amount_desc'])) {
                            if ($vpayment->getData('item_wise_amount_desc')) {
                                $amountDesc = json_decode($vpayment->getData('item_wise_amount_desc'),true);
                                foreach ($amountDesc as $orderId=>$vorderItems) {
                                    foreach ($vorderItems as $itemId=>$amount) {
                                        $vorderItem = $this->_vtorders->load($itemId);
                                        $vorderItem->setAmountRefunded($vorderItem->getAmountRefunded()+$amount);
                                        $qty = (int)($amount/$vorderItem->getItemFee());
                                        $vorderItem->setQtyRefunded($vorderItem->getQtyRefunded()+$qty);
                                        $vorderItem->setQtyReadyToRefund($vorderItem->getQtyReadyToRefund()-$qty);
                                        $vorderItem->save();
                                    }
                                }
                            }
                        }
                    } else {
                        $orderPaidQty = [];
                        if (isset($data['amount_desc'])) {
                            if ($vpayment->getData('item_wise_amount_desc')) {
                                $amountDesc = json_decode($vpayment->getData('item_wise_amount_desc'), true);
                                foreach ($amountDesc as $orderId=>$vorderItems) {
                                    foreach ($vorderItems as $itemId=>$amount) {
                                        $vorderItem = $this->_vtorders->load($itemId);
                                        $amount = $amount-($vorderItem->getItemCommission()*$vorderItem->getQtyReadyToPay());
                                        $orderId = $vorderItem->getParentId();//added for the vorder id
                                        $vorderItem->setAmountPaid($vorderItem->getAmountPaid()+$amount);
                                        $qty = (int)($amount/$vorderItem->getItemFee());
                                        $vorderItem->setQtyPaid($vorderItem->getQtyPaid()+$qty);
                                        $vorderItem->setQtyReadyToPay($vorderItem->getQtyReadyToPay()-$qty);
                                        $vorderItem->save();
                                        if (isset($orderPaidQty[$orderId])) {
                                            $orderPaidQty[$orderId]['qty_paid'] += $vorderItem->getQtyPaid();
                                            $orderPaidQty[$orderId]['qty_ordered'] += $vorderItem->getProductQty();
                                        } else {
                                            $orderPaidQty[$orderId]['qty_paid'] = $vorderItem->getQtyPaid();
                                            $orderPaidQty[$orderId]['qty_ordered'] = $vorderItem->getProductQty();
                                            $orderPaidQty[$orderId]['parent_id'] = $vorderItem->getParentId();
                                        }
                                        $vorderItem->setQtyForRefund($vorderItem); // fixed for the multiple refunds
                                    }
                                }

                                $orderPaymentStatus = [];
                                foreach ($orderPaidQty as $vorderId=>$details) {
                                    if ($details['qty_paid']==$details['qty_ordered']) {
                                        $vorder = $this->_vorders->load($vorderId);
                                        $vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_PAID);
                                        $vorder->save();
                                        $orderPaymentStatus[$vorderId] = \Ced\CsMarketplace\Model\Vorders::STATE_PAID;
                                    } else {
                                        $vorder = $this->_vorders->load($vorderId);
                                        $vorder->setPaymentState(\Ced\CsOrder\Model\Vorders::STATE_PARTIALLY_PAID);
                                        $vorder->save();
                                        $orderPaymentStatus[$vorderId] = \Ced\CsOrder\Model\Vorders::STATE_PARTIALLY_PAID;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
