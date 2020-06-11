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
namespace Ced\GiftCard\Plugin\Wishlist\Model;
use \Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class Item
 * @package Ced\GiftCard\Plugin\Wishlist\Model
 */
class Item
{

    /**
     * @param $subject
     * @param callable $proceed
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddToCart(
        $subject,
        callable $proceed,
        \Magento\Checkout\Model\Cart $cart, $delete = false){

        $product = $subject->getProduct();
        if($product->getTypeId() == GiftCard::TYPE_CODE){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please Fill all the options, before add to cart.')
            ); 
        }else{
            return $proceed($cart, $delete);
        }
    }
}
