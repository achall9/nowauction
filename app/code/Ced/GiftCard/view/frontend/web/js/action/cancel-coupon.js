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
    'jquery',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, quote, urlManager, errorProcessor, discountmessages,storage, getPaymentInformationAction, totals, $t,
  fullScreenLoader
) {
    'use strict';

    return function (isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.build('giftcard/checkout/applycoupon/coupon/remove'),
            message = $t('Your Gift coupon was successfully removed.');

        discountmessages.clear();
        fullScreenLoader.startLoader();

        var messageContainer = $('#giftcard-checkout-messages');
        var addSuccessMessage = $('#gift-message-success');
        var addErrorMessage = $('#gift-message-error');
        messageContainer.hide();
        return storage.delete(
            url,
            false
        ).done(function (response) {

            if (response) {
                // var msg = JSON.parse(response.responseText);
                var msg = response;
                if (msg.indexOf("Success:") >= 0) { 
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        isApplied(false);
                        totals.isLoading(false);
                        fullScreenLoader.stopLoader();
                    });

                    messageContainer.show();
                    addErrorMessage.hide();
                    addSuccessMessage.show();
                    addSuccessMessage.find('#smessage').html(msg);

                }else{
                    totals.isLoading(false);
                    fullScreenLoader.stopLoader();

                    message = $t('Unable To remove your coupon code.');
                    message = response;

                    messageContainer.show();
                    addSuccessMessage.hide();
                    addErrorMessage.show();
                    addErrorMessage.find('#emessage').html(msg);
 
                    // errorProcessor.process(response, discountmessages);

                }
            }else{
                totals.isLoading(false);
                fullScreenLoader.stopLoader();

                message = $t('Unable To remove your coupon code.');
                
                messageContainer.show();
                addSuccessMessage.hide();
                addErrorMessage.show();
                addErrorMessage.find('#emessage').html(message);

                // errorProcessor.process(response, discountmessages);                
            }
        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();

            message = $t('Unable To remove your coupon code.');
            
            messageContainer.show();
            addSuccessMessage.hide();
            addErrorMessage.show();
            addErrorMessage.find('#emessage').html(message);

            // errorProcessor.process(response, discountmessages);
        });
    };
});
