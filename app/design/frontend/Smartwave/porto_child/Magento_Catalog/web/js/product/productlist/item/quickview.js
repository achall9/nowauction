define([  
    'jquery',  
    'Magento_Ui/js/modal/modal',  
    'mage/loader',  
    'Magento_Customer/js/customer-data'  
], 
function ($, modal, loader, customerData) {
    'use strict';  
    return function(config, node) {  
        var product_id = jQuery(node).data('product_id');  
        var customer_id = jQuery(node).data('customer_id');
        var product_url = jQuery(node).data('url');  
        var options = {  
            type: 'popup',  
            responsive: true, 
            innerScroll: false,  
            title: $.mage.__('BID NOW'),  
            buttons: [{
                text: $.mage.__('Close'),  
                class: 'close-modal',  
                click: function () {  
                    this.closeModal();  
                }  
            }]  
        };  
        var popup = modal(options, $('#quickViewContainer' + product_id));  
        $("#quickViewButton" + product_id).on("click", function () {
                openQuickViewModal();   
        });  
        var openQuickViewModal = function () {  
            var modalContainer = $("#quickViewContainer" + product_id);  
            modalContainer.html(createIframe());  
            var iframearea = "#new_frame" + product_id;  
            $(iframearea).css("height","500px");
            $(iframearea).on("load", function () { 
                $(iframearea).contents().find('header').attr("style", "display: none;");   
                $(iframearea).contents().find('.left-side-content').attr("style", "display: none;");
                $(iframearea).contents().find('.breadcrumbs').attr("style", "display: none;");
                $(iframearea).contents().find('footer').attr("style", "display: none;"); 
                modalContainer.addClass("product-quickview");  
                modalContainer.modal('openModal');  
                observeAddToCart(this);  
            });  
        }; 
        var observeAddToCart = function (iframe) {  
            var doc = iframe.contentWindow.document;
            $(doc).contents().find('header').attr("style", "display: none;");   
            $(doc).contents().find('.left-side-content').attr("style", "display: none;");
            $(doc).contents().find('.breadcrumbs').attr("style", "display: none;");
            $(doc).contents().find('footer').attr("style", "display: none;");
            $(doc).contents().find('#product_addtocart_form').submit(function(e) { 
                e.preventDefault();   
                $.ajax({

                    data: $(this).serialize(),
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    success: function(response) {  
                        $(".close-modal").trigger("click"); 
                        $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("open"); 
                    }}); 
                });
        };
        var createIframe = function () {  
            return $('<iframe />', { id: 'new_frame' + product_id, src: product_url + "?iframe=1" });  
        } 
    };
});