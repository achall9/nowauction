/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mage/translate',

], function (Component, $, ko, _) {
    'use strict';
    return Component.extend({
        defaults: {
        },
        options:{},
        vcreceiver: ko.observableArray([]),

        /**
         * @override
         */
        initialize: function(options) {
            this.options = options;
            var self = this;
            this.getReceiver();
            return this._super();
        },
        allLoaded:false,
        getReceiver: function(){
            var self = this;
            if(self.allLoaded)
                return;
            $.ajax({
                type: 'GET',
                url: self.options.url,
                success: function(entries) {
                    $.each(entries, function(entry,value) {
                      //  data = {name:value.name++}
                        self.vcreceiver.push(value);
                    });
                },
                error: function() {
                },
                dataType: 'json'
            });
        }
    });
});