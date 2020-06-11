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
namespace Ced\GiftCard\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

/**
 * Class GiftCoupon
 * @package Ced\GiftCard\Model\Creditmemo\Total
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
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $couponAmount = $creditmemo->getOrder()->getCsGiftcouponAmount();
        $TotalQtyOrdered = $creditmemo->getOrder()->getTotalQtyOrdered();

        $invoicedItems = $this->getRequest()->getParam('invoice', []);
        $couponAmount = ($couponAmount/$TotalQtyOrdered);
        $totalInvoicedItems = $creditmemo->getTotalQty();
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

            if($creditmemo->getGrandTotal() > $couponAmount){
                $gt = $creditmemo->getGrandTotal() - $couponAmount;
                $gt_c = $couponAmount;
            }elseif($creditmemo->getGrandTotal() == $couponAmount){
                $gt = 0;
                $gt_c = $couponAmount;
            }elseif($creditmemo->getGrandTotal() < $couponAmount){
                $gt_c = $creditmemo->getGrandTotal();
                $gt = 0;
            }

            if($creditmemo->getBaseGrandTotal() > $couponAmount){
                $bgt = $creditmemo->getBaseGrandTotal() - $couponAmount;
                $gt_c = $couponAmount;
            }elseif($creditmemo->getBaseGrandTotal() == $couponAmount){
                $bgt = 0;
                $gt_c = $couponAmount;
            }elseif($creditmemo->getBaseGrandTotal() < $couponAmount){
                $gt_c = $creditmemo->getBaseGrandTotal();
                $bgt = 0;
            }

            $creditmemo->setCsGiftcouponAmount($gt_c);
            $creditmemo->setGrandTotal($bgt);
            $creditmemo->setBaseGrandTotal($gt);
        }
        return $this;
    }
}