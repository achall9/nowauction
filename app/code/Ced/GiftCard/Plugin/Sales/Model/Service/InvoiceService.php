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
namespace Ced\GiftCard\Plugin\Sales\Model\Service;

use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class InvoiceService
 * @package Ced\GiftCard\Plugin\Sales\Model\Service\Sales\InvoiceService
 */
class InvoiceService
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(\Magento\Sales\Model\Order $order)
    {
        $this->order = $order;
    }
    public function beforeExecute($subject){
        $orderId = $subject->getRequest()->getParam('order_id');
        $invoiceData = $subject->getRequest()->getParam('invoice', []);
        $isUpdated = false;
        $order = $this->order->load($orderId);

        foreach($order->getAllItems() as $item){
            if($item->getProductType() == GiftCard::TYPE_CODE){
                if(isset($invoiceData['items'][$item->getItemId()])){
                    $isUpdated = true;
                    $invoiceData['items'][$item->getItemId()] = round($invoiceData['items'][$item->getItemId()]);
                }
            }
        }
        if($isUpdated){
            $subject->getRequest()->setParam('invoice', $invoiceData);
        } 
    }
}
