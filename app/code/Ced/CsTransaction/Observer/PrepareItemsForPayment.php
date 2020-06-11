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

/**
 * Class PrepareItemsForPayment
 * @package Ced\CsTransaction\Observer
 */
class PrepareItemsForPayment implements ObserverInterface
{
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $salesItem;

    /**
     * PrepareItemsForPayment constructor.
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsTransaction\Model\Items $vtorders
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Model\Order\Item $salesItem
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Model\Items $vtorders,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order\Item $salesItem
    )
    {
        $this->_vorders = $vorders;
        $this->_csorderHelper = $csorderHelper;
        $this->_vtorders = $vtorders;
        $this->request = $request;
        $this->salesItem = $salesItem;
    }


    /**
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_csorderHelper->isActive()) {
            try {
                $invoice = $observer->getEvent()->getInvoice();
                foreach ($invoice->getAllItems() as $item) {
                    if ($item->getOrderItem()->getParentItem())
                        continue;
                    $quoteItem = $this->salesItem->load($item->getOrderItem()->getId());
                    $vorder = $this->_vorders->getCollection()
                        ->addFieldToFilter('order_id', $invoice->getOrder()->getIncrementId())
                        ->addFieldToFilter('vendor_id', $item->getVendorId())->getFirstItem();
                    $itemCollection = $this->_vtorders->getCollection()
                        ->addFieldToFilter('vendor_id', $item->getVendorId())
                        ->addFieldToFilter('parent_id',$vorder->getId())
                        ->addFieldToFilter('order_item_id', $item->getOrderItemId());
                    
                    if (empty($itemCollection->getData())) {

                        if (($vorder->getVendorId() > 0) && !empty($vorder->getData())) {
                            $this->saveOrderItem($item, $invoice, $vorder, $quoteItem);
                        }
                    } elseif (!empty($itemCollection->getData())) {
                        foreach ($itemCollection as $items) {
                            $itemsFee = json_decode($vorder->getItemsCommission(), true);
                            $saveItems = $this->_vtorders->load($items->getId());
                            $saveItems->setItemPaymentState(\Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY);
                            $saveItems->setQtyReadyToPay($items->getQtyReadyToPay() + $item->getQty());
                            $saveItems->setTotalInvoicedAmount($items->getTotalInvoicedAmount() + $item->getRowTotal());
                            $total = $this->getRowTotalFeeAmount($item);
                            if (isset($itemsFee[$quoteItem->getQuoteItemId()]['base_fee'])) {
                                $itemCommission = $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem->getQtyOrdered();
                                $saveItems->setItemFee($saveItems->getItemFee() + ($total - ($itemCommission * $item->getQty())));
                                $saveItems->setItemCommission($saveItems->getItemCommission() + ($itemCommission * $item->getQty()));
                            }

                            $saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() + $item->getBaseRowTotal());
                            $saveItems->setRowTotal($saveItems->getRowTotal() + $item->getRowTotal());
                            $saveItems->setTotalInvoicedAmount($saveItems->getTotalInvoicedAmount() + $item->getRowTotal());
                            $saveItems->setQtyForPayNow($items->getQtyReadyToPay() + $item->getQty());
                            $saveItems->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
        return $this;
    }

    /**
     * @param $item
     * @param $invoice
     * @param $vorder
     * @param $quoteItem
     * @throws \Exception
     */
    public function saveOrderItem($item, $invoice, $vorder, $quoteItem)
    {
        $itemsFee = json_decode($vorder->getItemsCommission(), true);
        $vorderItem = $this->_vtorders;
        $vorderItem->setParentId($vorder->getId());
        $vorderItem->setOrderItemId($item->getOrderItemId());
        $vorderItem->setOrderId($invoice->getOrder()->getId());
        $vorderItem->setOrderIncrementId($invoice->getOrder()->getIncrementId());
        $vorderItem->setVendorId($vorder->getVendorId());
        $vorderItem->setCurrency($vorder->getCurrency());
        $vorderItem->setBaseRowTotal($item->getBaseRowTotal());
        $vorderItem->setRowTotal($item->getRowTotal());
        $vorderItem->setSku($item->getSku());
        $vorderItem->setShopCommissionTypeId($vorder->getShopCommissionTypeId());
        $vorderItem->setShopCommissionRate($vorder->getShopCommissionRate());
        $vorderItem->setShopCommissionBaseFee($vorder->getShopCommissionBaseFee());
        $vorderItem->setShopCommissionFee($vorder->getShopCommissionFee());
        $vorderItem->setProductQty($item->getQtyOrdered());
        $vorderItem->setItemPaymentState(false);
        $total = $this->getRowTotalFeeAmount($item);
        if (isset($itemsFee[$quoteItem->getQuoteItemId()])) {
            $itemCommission = $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem->getQtyOrdered();
            $vorderItem->setItemFee($total - ($itemCommission * $item->getQty()));
            $vorderItem->setItemCommission($itemCommission * $item->getQty());
        }

        $vorderItem->setQtyOrdered($quoteItem->getQtyOrdered());

        $vorderItem->setItemPaymentState(\Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY);
        $vorderItem->setQtyReadyToPay($item->getQty());
        $vorderItem->setTotalInvoicedAmount($item->getRowTotal());
        $vorderItem->setIsRequested('0');
        $vorderItem->setQtyForPayNow($item->getQty());
        $vorderItem->save();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getRowTotalFeeAmount($item)
    {
        $amount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount();
        return $amount;
    }
}
