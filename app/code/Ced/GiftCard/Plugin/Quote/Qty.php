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
 * @package     Ced_CsSplitCart
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Plugin\Quote;

/**
 * Class Qty
 * @package Ced\GiftCard\Plugin\Quote
 */
class Qty
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Qty constructor.
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(\Magento\Checkout\Model\Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param $subject
     * @param $result
     * @return float|int
     */
    public function afterGetItemsQty($subject, $result)
    {

        $itemCollection = $this->session
            ->getQuote()->getAllVisibleItems();
        $qty = 0;
        foreach ($itemCollection as $itm) {
            $qty += $itm->getQty();
        }
        return $qty * 1;
    }
}
