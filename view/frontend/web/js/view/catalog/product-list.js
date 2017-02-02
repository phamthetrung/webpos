define([
    'jquery',
    'uiComponent',
    'mage/storage',
    'ko',
    'Bkademy_Webpos/js/model/url-builder',
    'Magento_Catalog/js/price-utils',
    'Bkademy_Webpos/js/model/catalog/product/detail-popup'
], function ($, Component, storage, ko, urlBuilder, priceUtils, detailPopup) {
    'use strict';

    return Component.extend({


        defaults: {
            template: 'Bkademy_Webpos/catalog/product-list'
        },

        product: ko.observableArray([]),
        curPage: ko.observable(1),
        numberOfProduct: ko.observable(0),
        numberOfPage: ko.observable(0),
        searchKey: ko.observable(''),
        quote: ko.observableArray([]),

        initialize: function () {
            var self = this;
            this._super();
            self.showProduct(1);
        },

        showProduct: function (curPage) {
            var self = this;
            var params = {};
            var serviceUrl = urlBuilder.createUrl('/webpos/products?searchCriteria[pageSize]=16' +
                '&searchCriteria[currentPage]='+curPage +
                '&searchCriteria[filterGroups][0][filters][0][field]=type_id' +
                '&searchCriteria[filterGroups][0][filters][0][value]=simple' +
                '&searchCriteria[filterGroups][0][filters][0][conditionType]=eq'
                , params);
            var payload = {};
            $('#product-list-overlay').show();
            storage.get(
                serviceUrl, JSON.stringify(payload)
            ).done(function (response) {
                self.product(response.items);
                self.numberOfProduct(response.total_count);
                self.numberOfPage(response.total_count/16 + 1);
                self.curPage(curPage);
                $('#product-list-overlay').hide();
                //self.hideLoader();
            }).fail(function (response) {
                $('#product-list-overlay').hide();
            });
        },

        getAllCategories: function () {
            eventManager.dispatch('load_product_by_category', {"catagory":{}});
        },

        searchProduct: function (key, curPage) {
            var self = this;
            var params = {};
            var serviceUrl = urlBuilder.createUrl('/webpos/products?searchCriteria[pageSize]=16' +
                '&searchCriteria[currentPage]='+curPage +
                '&searchCriteria[filterGroups][0][filters][0][field]=type_id' +
                '&searchCriteria[filterGroups][0][filters][0][value]=simple' +
                '&searchCriteria[filterGroups][0][filters][0][conditionType]=eq' +
                '&searchKey=' + key
                , params);
            var payload = {};
            $('#product-list-overlay').show();
            storage.get(
                serviceUrl, JSON.stringify(payload)
            ).done(function (response) {
                self.product(response.items);
                self.numberOfProduct(response.total_count);
                self.numberOfPage(response.total_count/16 + 1);
                self.curPage(curPage);
                //self.hideLoader();
                $('#product-list-overlay').hide();
            }).fail(function (response) {
                $('#product-list-overlay').hide();
            });
        },

        formatPrice: function (price) {
            return priceUtils.formatPrice(price, window.webposConfig.priceFormat);
        },

        filter: function () {
            this.curPage(1);
            this.searchProduct(this.searchKey(), this.curPage());
        },

        next: function () {
            var curPage = this.curPage();
            this.searchProduct(this.searchKey(), curPage + 1);
        },

        previous: function () {
            var curPage = this.curPage();
            this.searchProduct(this.searchKey(), curPage - 1);
        },

        showPopupDetails: function (item,event) {
            detailPopup.itemData(item);
            $("#popup-product-detail").show();
            $("#popup-product-detail").removeClass("fade");
            $(".wrap-backover").show();

            $(document).click(function(e) {
                if( e.target.id == 'popup-product-detail') {
                    $("#popup-product-detail").hide();
                    $(".wrap-backover").hide();
                    $('.notification-bell').show();
                    $('#c-button--push-left').show();
                }
            });
        },

        addToCart: function (data) {
            var infoBuy = {
                'product_id': data.id,
                'name': data.name,
                'qty': 1,
                'unit_price': data.price,
                'image_url': data.image,
                'is_virtual': false
            };
            CartModel.addProduct(infoBuy);
        }
    });
});