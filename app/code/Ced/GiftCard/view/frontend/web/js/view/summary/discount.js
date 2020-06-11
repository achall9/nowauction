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
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
], function (Component, quote, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ced_GiftCard/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {   
            return this.isFullMode() && this.getPureValue() != 0;
        },

        /**
         * @return {*}
         */
        getCouponCode: function () { 

            if (!this.totals()) {
                return null;
            }
            
            var coupon_code = '';
            for (var i = this.totals()['total_segments'].length - 1; i >= 0; i--) {
                var val = this.totals()['total_segments'][i]; 
                if (val.code == 'gift_coupon_code') {   
                     
                    coupon_code = val.title;  
                    
                    coupon_code = coupon_code.slice(12);
                    coupon_code = "( "+coupon_code+" )";
                    break;
                }
            } 
            return coupon_code;
        },

        /**
         * @return {*}
         */
        getCouponLabel: function () {
        
            if (!this.totals()) {
                return null;
            }

            var coupon_label = '';
            for (var i = this.totals()['total_segments'].length - 1; i >= 0; i--) {
                var val = this.totals()['total_segments'][i]; 
              
                if (val.code == 'gift_coupon_code') {
                    coupon_label = val.title;
                }
            }
            return coupon_label;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;
            var coupon_value = 0
            for (var i = this.totals()['total_segments'].length - 1; i >= 0; i--) {
                var val = this.totals()['total_segments'][i];  
                if (val.code == 'gift_coupon_code') { 
                    coupon_value = val.value;   
                }
            }
            
            if (
                this.totals() && 
                coupon_value != 0 &&
                coupon_value) {
                price = parseFloat(coupon_value);
            }
            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
  
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
