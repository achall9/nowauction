<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;


use Magento\Framework\Event\ObserverInterface;
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;
use Ced\CsMarketplace\Helper\Data;

/**
 * Class OrderCreditmemoRefund
 * @package Ced\CsMarketplace\Observer
 */
Class OrderCreditmemoRefund implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $vOrdersCollectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * OrderCreditmemoRefund constructor.
     * @param CollectionFactory $vOrdersCollectionFactory
     * @param Data $dataHelper
     */
    public function __construct(
        CollectionFactory $vOrdersCollectionFactory,
        Data $dataHelper
    )
    {
        $this->vOrdersCollectionFactory = $vOrdersCollectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Refund the associated vendor order
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getDataObject();
        try {
            if ($order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED) {
                $vOrders = $this->vOrdersCollectionFactory->create()
                    ->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);

                if (count($vOrders) > 0) {
                    foreach ($vOrders as $vOrder) {
                        if ($vOrder->canCancel()) {
                            $vOrder->setOrderPaymentState(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED);
                            $vOrder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_CANCELED);
                            $vOrder->save();
                        } else if ($vOrder->canMakeRefund()) {
                            $vOrder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_REFUND);
                            $vOrder->save();
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            $this->dataHelper->logException($e);
        }
        return $this;
    }
}    
