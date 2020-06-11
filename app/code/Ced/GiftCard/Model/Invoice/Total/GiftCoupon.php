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
namespace Ced\GiftCard\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class GiftCoupon
 * @package Ced\GiftCard\Model\Invoice\Total
 */
class GiftCoupon extends AbstractTotal
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * GiftCoupon constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ){
        $this->_request = $request;
        parent::__construct($data);
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $couponAmount = $invoice->getOrder()->getCsGiftcouponAmount();
        $TotalQtyOrdered = $invoice->getOrder()->getTotalQtyOrdered();

        $invoicedItems = $this->getRequest()->getParam('invoice', []);
        $couponAmount = ($couponAmount/$TotalQtyOrdered);
        $totalInvoicedItems = $invoice->getTotalQty();
        if(!empty($invoicedItems)){
            if(isset($invoicedItems['items']) && is_array($invoicedItems['items'])){
                $totalInvoicedItems = 0;
                foreach ($invoicedItems['items'] as $in_itms){
                    $totalInvoicedItems = $totalInvoicedItems+$in_itms;
                }
            }
        }

        $couponAmount = $couponAmount*$totalInvoicedItems;

        if (isset($couponAmount) && $couponAmount > 0){

            if($invoice->getGrandTotal() > $couponAmount){
                $gt = $invoice->getGrandTotal() - $couponAmount;
                $gt_c = $couponAmount;
            }elseif($invoice->getGrandTotal() == $couponAmount){
                $gt = 0;
                $gt_c = $couponAmount;
            }elseif($invoice->getGrandTotal() < $couponAmount){
                $gt_c = $invoice->getGrandTotal();
                $gt = 0;
            }

            if($invoice->getBaseGrandTotal() > $couponAmount){
                $bgt = $invoice->getBaseGrandTotal() - $couponAmount;
                $gt_c = $couponAmount;
            }elseif($invoice->getBaseGrandTotal() == $couponAmount){
                $bgt = 0;
                $gt_c = $couponAmount;
            }elseif($invoice->getBaseGrandTotal() < $couponAmount){
                $gt_c = $invoice->getBaseGrandTotal();
                $bgt = 0;
            }


            $invoice->setCsGiftcouponAmount($gt_c);
            $invoice->setGrandTotal($gt);
            $invoice->setBaseGrandTotal($bgt);
        }
        return $this;
    }
}