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
 * @category  Ced
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Block\Adminhtml\Sales\Invoice;

/**
 * Class Totals
 * @package Ced\GiftCard\Block\Adminhtml\Sales\Invoice
 */
class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var null
     */
    protected $_invoice = null;

    /**
     * @var
     */
    protected $_source;


    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
        $this->getSource();
     
        if(!$this->getSource()->getCsGiftcouponAmount()) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'cs_giftcoupon_amount',
                'value' => -1*$this->getSource()->getCsGiftcouponAmount(),
                'label' =>  __('Gift Coupon Code %1',$this->getSource()->getOrder()->getCsGiftcouponCode()),
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}