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
 * Class VorderSaveAfter
 * @package Ced\CsTransaction\Observer
 */
class VorderSaveAfter implements ObserverInterface
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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * VorderSaveAfter constructor.
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsTransaction\Model\Items $vtorders
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Model\Items $vtorders,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->_vorders = $vorders;
        $this->_csorderHelper = $csorderHelper;
        $this->_vtorders = $vtorders;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($observer->getVorder()->getId() && $this->_csorderHelper->isActive()) {
                $vorder = $this->_vorders->load($observer->getVorder()->getId());

                $itemsFee = json_decode($vorder->getItemsCommission(), true);

                $order = $this->orderFactory->create()->load($vorder->getOrderId(), 'increment_id');

                foreach ($order->getAllItems() as $item) {
                    if ($item->getParentItem()) continue;
                    $existingItem = $this->_vtorders->getCollection()
                        ->addFieldToFilter('order_item_id', array('eq' => $item->getId()))
                        ->getFirstItem();
                    if (!$existingItem->getId() && $item->getVendorId() == $vorder->getVendorId()) {
                        $vorderItem = $this->_vtorders;
                        $vorderItem->setParentId($observer->getVorder()->getId());
                        $vorderItem->setOrderItemId($item->getId());
                        $vorderItem->setOrderId($order->getId());
                        $vorderItem->setOrderIncrementId($order->getIncrementId());
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
                        $vorderItem->setItemPaymentState(0);
                        $vorderItem->setItemFee(($item->getRowTotal() - $itemsFee[$item->getQuoteItemId()]['base_fee']) / $item->getQtyOrdered());
                        $vorderItem->setItemCommission($itemsFee[$item->getQuoteItemId()]['base_fee'] / $item->getQtyOrdered());
                        $vorderItem->save();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
