/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Bkademy_Webpos/js/model/catalog/product/detail-popup'
    ],
    function ($, ko, Component, detailPopup) {
        "use strict";
        window.timeout = 0;
        return Component.extend({
            itemData: ko.pureComputed(function () {
                return detailPopup.itemData();
            }),
            focusQtyInput: true,
            qtyAddToCart: ko.observable(1),

            defaults: {
                template: 'Bkademy_Webpos/catalog/product/detail-popup'
            },
            initialize: function () {
                this._super();
            },
            getTypeId: function () {
            },
            incQty: function(){
            },
            descQty: function(){
            },
            getQtyAddToCart: function(){
                return 1;
            },
            modifyQty: function(data,event){
                this.qtyAddToCart(parseFloat(event.target.value));
            },
            setAllData: function () {
                var self = this;
            },
            prepareAddToCart: function() {
                var self = this;
            },

            getProductData: function(){
                var self =  this;
            },
            addProduct: function(product){
                var self = this;
            },
            closeDetailPopup: function() {
                $("#popup-product-detail").hide();
                $(".wrap-backover").hide();
                $('.notification-bell').show();
                $('#c-button--push-left').show();
            },
            reloadJs: function () {
                var $j = jQuery.noConflict();
                if ($j("#product-img-slise").find('div.owl-controls').length > 0) {
                    var removeControl = $j("#product-img-slise").find('div.owl-controls');
                    removeControl[0].remove();
                }
                setTimeout(function(){
                    $j("#product-img-slise").owlCarousel({
                        items: 1,
                        itemsDesktop: [1000, 1],
                        itemsDesktopSmall: [900, 1],
                        itemsTablet: [600, 1],
                        itemsMobile: false,
                        navigation: true,
                        pagination:true,
                        navigationText: ["", ""]
                    });
                }, 50);
            },
            updatePrice: function () {
                var self = this;
            },
            showPopup: function(){

            },

            isShowAvailableQty: function(){
                return true;
            },

            getAvailableQty: function(productData){
            }
        });
    }
);