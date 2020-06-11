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

namespace Ced\GiftCard\Helper;


/**
 * Class Config
 * @package Ced\GiftCard\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SECTION_GROUP = 'giftcard/giftcard/';

    /**
     * @param null $store
     * @return bool
     */
    public function enableModule($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::SECTION_GROUP . 'enable_giftcard',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function giftNotAvaliableText($store = null)
    {
        $text = $this->scopeConfig->getValue(
            self::SECTION_GROUP . 'gift_not_avaliable_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$text || $text == '') {
            $text = __('Not Avaliable');
        }
        return $text;
    }
}