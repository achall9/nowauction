/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    $.widget('mage.categoryWiseGrid', {
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        _initModal: function () {
            var self = this;
            
            this.modal = $('<div id="create_new_category_commision"/>').modal({
                title: $.mage.__('Category Wise Commission'),
                type: 'slide',
                buttons: [],
                opened: function () {
                    $(this).parent().addClass('modal-content-new-attribute');
                    self.iframe = $('<iframe id="create_new_category_commision_container">').attr({
                        src: self._prepareUrl(),
                        frameborder: 0
                    });
                    self.modal.append(self.iframe);
                    self._changeIframeSize();
                    $(window).off().on('resize', _.debounce(self._changeIframeSize.bind(self), 400));
                },
                closed: function () {
                    var doc = self.iframe.get(0).document;

                    if (doc && $.isFunction(doc.execCommand)) {
                        //IE9 break script loading but not execution on iframe removing
                        doc.execCommand('stop');
                        self.iframe.remove();
                    }
                    self.modal.data('modal').modal.remove();
                }
            });
            if($('#ced_csmarketplace_vpayments_commission_cw_inherit').is(':checked')){
                $('#create_category_wise_commission_button').addClass("disabled");
            }
        },

        _getHeight: function () {
            var modal = this.modal.data('modal').modal,
                modalHead = modal.find('header'),
                modalHeadHeight = modalHead.outerHeight(),
                modalHeight = modal.outerHeight(),
                modalContentPadding = this.modal.parent().outerHeight() - this.modal.parent().height();

            return modalHeight - modalHeadHeight - modalContentPadding;
        },

        _getWidth: function () {
            return this.modal.width();
        },

        _changeIframeSize: function () {
            this.modal.parent().outerHeight(this._getHeight());
            this.iframe.outerHeight(this._getHeight());
            this.iframe.outerWidth(this._getWidth());

        },

        _prepareUrl: function () {
            var $inherit = 0 ;
            if($('#ced_csmarketplace_vpayments_commission_cw_inherit').is(':checked')) 
                $inherit = 1 ;

            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'inherit=' +$inherit  ;
        },

        _showPopup: function () {
            this._initModal();
            this.modal.modal('openModal');
        }
    });

    return $.mage.categoryWiseGrid;
});
