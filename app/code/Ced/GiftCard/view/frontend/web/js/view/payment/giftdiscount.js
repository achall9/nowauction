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
 * @license      https://cedcommerce.com/license-agreement.txt
 */ 

define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Ced_GiftCard/js/action/set-coupon-code',
    'Ced_GiftCard/js/action/cancel-coupon'
], function ($, ko, _, Component, quote, setCouponCodeAction, cancelCouponAction) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = ko.observable(null),
        isApplied;
        var coupon_code = '';
    if (totals()) { 
        for (var i = totals()['total_segments'].length - 1; i >= 0; i--) {
            var val = totals()['total_segments'][i]; 
            if (val.code == 'gift_coupon_code') { 
                coupon_code = val.title;
                coupon_code = coupon_code.slice(12);
              
                break;
            }
        }
        if (coupon_code) {  
            couponCode(coupon_code); 
            $('#gift-discount').click();
        }else{ 
            couponCode(''); 
        }
    }  
    isApplied = ko.observable(couponCode() != '');
     
    return Component.extend({
        defaults: {
            template: 'Ced_GiftCard/payment/giftdiscount'
        },
        couponCode: couponCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#gift-discount-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});