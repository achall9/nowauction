define([
        "jquery", "Magento_Ui/js/modal/modal"
    ], function($){
        var AuctionModal = {
            initModal: function(config, element) {
                $target = $(config.target);
                $target.modal();
                $element = $(element);
                $element.click(function() {
                    $target.modal('openModal');
                });
            }
        };
        return {
            'auction-modal': AuctionModal.initModal
        };
    }
);
