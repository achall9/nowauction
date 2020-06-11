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
namespace Ced\GiftCard\Plugin\Checkout;
 
use Magento\Framework\Serialize\Serializer\Serialize as SerializerInterface;

/**
 * Class QuoteToOrderItem
 * @package Ced\GiftCard\Plugin\Checkout
 */
class QuoteToOrderItem
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * QuoteToOrderItem constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer) {
      $this->serializer = $serializer;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return mixed
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ){
        $orderItem = $proceed($item, $additional);
        if ($item->getCedGiftcarddata()) { 
         $orderItem->setCedGiftcarddata($item->getCedGiftcarddata());
        
         $data = $this->serializer->unserialize($item->getCedGiftcarddata());
         $orderItem->setCedGiftToMail($data['gift_to_email']);      
        }

        return $orderItem;
    }
}
