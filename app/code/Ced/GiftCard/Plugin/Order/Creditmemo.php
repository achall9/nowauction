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
namespace Ced\GiftCard\Plugin\Order;

use Ced\GiftCard\Model\Product\Type\GiftCard;
/**
 * Class Creditmemo
 * @package Ced\GiftCard\Plugin\Order
 */
class Creditmemo extends \Magento\Sales\Model\Order\CreditmemoFactory
{

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = [])
    {
        if ($item->getProductType() == GiftCard::TYPE_CODE) {
            return  false;
        }else{
            return parent::canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = []);
        }
    } 
}
