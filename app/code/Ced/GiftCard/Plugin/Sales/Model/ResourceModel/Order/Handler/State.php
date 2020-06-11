<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\GiftCard\Plugin\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class State
 */
class State
{
    /**
     * Check order status before save
     *
     * @param Order $order
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundCheck($ssubject, $proceed, Order $order)
    {

        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {

            if ($order->getState() !== Order::STATE_COMPLETE) {
                foreach($order->getAllItems() as $item){
                    if($item->getProductType() == GiftCard::TYPE_CODE){
                        $order->setForcedCanCreditmemo(true);
                        break;
                    }
                }
            }
        }

        return $proceed($order);
    }
}
