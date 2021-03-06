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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'mageUtils'
], function (DynamicRows, utils) {
    'use strict';

    return DynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            listens: {
                'insertData': 'processingInsertData'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertData'
                ]);

            return this;
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertData: function (data) {
            if (!data.length) {
                return false;
            }

            data.each(function (options) {
                options.options.each(function (option) {
                    var path = this.dataScope + '.' + this.index + '.' + this.recordIterator,
                        curOption = utils.copy(option);

                    if (curOption.hasOwnProperty('sort_order')) {
                        delete curOption['sort_order'];
                    }

                    this.source.set(path, curOption);
                    this.addChild(curOption, false);
                }, this);
            }, this);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        }
    });
});
