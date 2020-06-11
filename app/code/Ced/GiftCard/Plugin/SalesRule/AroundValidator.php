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
namespace Ced\GiftCard\Plugin\SalesRule;

use Ced\GiftCard\Model\Product\Type\GiftCard;
/**
 * Coupon management object.
 */
class AroundValidator
{

    /**
     * @param $subject
     * @param callable $proceed
     * @param $item
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundProcess($subject, callable $proceed, $item)
    {
        $canProceed = true;

        $giftCoupon = $item->getAddress()->getQuote()->getCsGiftcouponCode();
    	if ($giftCoupon){
    	    $canProceed = false;
        }

        if ($item->getProductType() == GiftCard::TYPE_CODE ) {
            $canProceed = false;
        }

        if (!$canProceed){
            if ($giftCoupon && $subject->getCouponCode()){
                return false;
            }else{ 
                return false;
            }

        }else{
            return $proceed($item);
        }
    }
}
