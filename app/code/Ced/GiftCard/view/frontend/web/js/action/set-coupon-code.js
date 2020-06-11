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

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'Magento_Checkout/js/model/error-processor', 
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (ko, $, quote, urlManager, errorProcessor, discountmessage, storage, $t, getPaymentInformationAction,
    totals, fullScreenLoader
) {
    'use strict';

    return function (couponCode, isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.build('giftcard/checkout/applycoupon/couponCode/'+couponCode+'/quoteId/'+quoteId),
            message = $t('Your coupon was successfully applied.');

        fullScreenLoader.startLoader();

        var messageContainer = $('#giftcard-checkout-messages');
        var addSuccessMessage = $('#gift-message-success');
        var addErrorMessage = $('#gift-message-error');
        messageContainer.hide();
        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var deferred; 
            if (response) {
                // var msg = JSON.parse(response.responseText);
                var msg = response;
                if (msg.indexOf("Success:") >= 0) {
                    deferred = $.Deferred();
                    isApplied(true);
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                    });
                    messageContainer.show();
                    addErrorMessage.hide();
                    addSuccessMessage.show();
                    addSuccessMessage.find('#smessage').html(msg);

                }else{

                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                    
                    message = $t('Unable to apply coupon code. Please Check and try again.');
                    message =  msg;
 
                    messageContainer.show();
                    addSuccessMessage.hide();
                    addErrorMessage.show();
                    addErrorMessage.find('#emessage').html(msg);

                    // errorProcessor.process(response, discountmessage);
                }
            }else{ 
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                
                message = $t('Unable to apply coupon code. Please Check and try again.');

                messageContainer.show();
                addSuccessMessage.hide();
                addErrorMessage.show();
                addErrorMessage.find('#emessage').html(msg);

                // errorProcessor.process(response, discountmessage);
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            
            message = $t('Unable to apply coupon code. Please Check and try again.');

            messageContainer.show();
            addSuccessMessage.hide();
            addErrorMessage.show();
            addErrorMessage.find('#emessage').html(message);

            // errorProcessor.process(response, discountmessage);
        });
    };
});
