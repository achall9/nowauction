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
namespace Ced\GiftCard\Block\Adminhtml\Sales;

/**
 * Class Totals
 * @package Ced\GiftCard\Block\Adminhtml\Sales
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        if ($this->getSource()->getCsGiftcouponAmount() == null)
            return $this;
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'cs_giftcoupon_amount',
                'value' => -1*$this->getSource()->getCsGiftcouponAmount(),
                'label' => __('Gift Coupon Code %1', $this->getSource()->getCsGiftcouponCode()),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
