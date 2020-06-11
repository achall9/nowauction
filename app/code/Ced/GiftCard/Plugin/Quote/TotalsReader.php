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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Plugin\Quote;

/**
 * Class TotalsReader
 * @package Ced\GiftCard\Plugin\Quote
 */
class TotalsReader
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterFetch($subject, $result)
    {
        return $result;
    }
}
