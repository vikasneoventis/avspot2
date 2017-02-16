/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    "jquery",
    "loadingPopup"
], function($){
    'use strict';

    $.widget("awfue.eventGridAction", {
        options: {
            restoreTimeout: 5000
        },
        _create: function() {
            if (this.options.message) {
                this.restoredContent = this.element.html();
                this.element
                    .html(this.options.message)
                    .addClass('mess');
                setTimeout($.proxy(this.restoreContent, this), this.options.restoreTimeout);
            } else {
                this._bind();
            }
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            this.element.on('click', $.proxy(this._click, this));
        },
        _unbind: function() {
            this.element.off('click');
        },
        _click: function() {
            if (!this.options.confirmation || confirm(this.options.confirmation)) {
                this._doAjax(this.element.data('url'));
            }
        },
        _doAjax: function(url) {
            var gridSelector = this.options.gridSelector;
            $.ajax({
                url: url,
                data: {form_key: FORM_KEY},
                context: $('body'),
                showLoader: true
            }).done(function(data){
                if (!data.error) {
                    if (data.grid) {
                        var grid = $(gridSelector);
                        if (grid.length > 0) {
                            grid.html(data.grid);
                            grid.trigger('contentUpdated');
                        }
                    }
                }
            });
        },
        restoreContent: function() {
            this.element.children().fadeOut(300, $.proxy(this._restore, this));
        },
        _restore: function() {
            this.element
                .html(this.restoredContent)
                .removeClass('mess');
            this._bind();
        }
    });

    return $.awfue.eventGridAction;
});