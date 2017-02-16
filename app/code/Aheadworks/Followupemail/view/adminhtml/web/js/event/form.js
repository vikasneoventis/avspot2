/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    "jquery",
    "loadingPopup"
], function($){
    'use strict';

    $.widget("awfue.eventForm", {
        options: {
        },
        _create: function() {
            this.typeChooser = $(this.options.typeChooserSelector);
            this.previewBtn = $(this.options.previewBtnSelector);
            this.sendTestBtn = $(this.options.sendTestBtnSelector);
            this._bind();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            this.typeChooser.on('change', $.proxy(this._typeChange, this));
            this.previewBtn.on('click', $.proxy(this._preview, this));
            this.sendTestBtn.on('click', $.proxy(this._sendTest, this));
        },
        _unbind: function() {
            this.typeChooser.off('change');
            this.previewBtn.off('click');
            this.sendTestBtn.off('click');
        },
        _typeChange: function()
        {
            var value = this.typeChooser.val();
            if (value in this.options.refreshUrls) {
                setLocation(this.options.refreshUrls[value]);
            }
        },
        _preview: function()
        {
            var previewUrl = this.options.previewUrl;
            this._doAjax(previewUrl, this.element.serializeArray(), function() {
                window.open(previewUrl, '_blank', 'resizable, scrollbars, status, top=0, left=0, width=600, height=500');
            });
        },
        _sendTest: function() {
            this._doAjax(this.options.sendTestUrl, this.element.serializeArray(), function() {
                location.reload();
            });
        },
        _doAjax: function(url, params, success) {
            params.form_key = FORM_KEY;
            $.ajax({
                url: url,
                data: params,
                context: $('body'),
                showLoader: true
            }).done(function(data){
                success.apply(this, [data]);
            });
        }
    });

    return $.awfue.eventForm;
});