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
namespace Ced\GiftCard\Block\Customer\Order;

use Magento\Sales\Model\Order;


/**
 * Class Totals
 * @package Ced\GiftCard\Block\Customer\Order
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{

    /**
     * @return $this|\Magento\Sales\Block\Order\Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        if ($this->getSource()->getCsGiftcouponAmount() != null){
            $cs_giftcoupon_amount = new \Magento\Framework\DataObject(
                [
                    'code' => 'cs_giftcoupon_amount',
                    'field' => 'cs_giftcoupon_amount',
                    'value' => -1*$this->getSource()->getCsGiftcouponAmount(),
                    'label' => __('Gift Coupon ( %1 )', $this->getSource()->getCsGiftcouponCode()),
                ]
            );
            $this->addTotalBefore($cs_giftcoupon_amount, 'discount');     
        } 

        return $this; 
 
    }
 
}
