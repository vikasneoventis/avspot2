/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

DataFeedManager = {
    updater: {
        init: function () {
            data = new Array();
            jQuery('.updater').each(function () {
                var feed = [jQuery(this).attr('id').replace("feed_", ""), jQuery(this).attr('cron')];
                data.push(feed);
            });

            jQuery.ajax({
                url: updater_url,
                data: {
                    data: JSON.stringify(data)
                },
                type: 'GET',
                showLoader: false,
                success: function (data) {
                    data.each(function (r) {
                        jQuery("#feed_" + r.id).parent().html(r.content);
                    });
                    setTimeout(DataFeedManager.updater.init, 1000);
                }
            });

        }
    },
    importDataFeedModal: function () {
        jQuery('#dfm-import-datafeed').modal({
            'type': 'slide',
            'title': 'Import a Data Feed',
            'modalClass': 'mage-new-category-dialog form-inline',
            buttons: [{
                    text: 'Import Data Feed',
                    'class': 'action-primary',
                    click: function () {
                        DataFeedManager.importDataFeed();
                    }
                }]
        });
        jQuery('#dfm-import-datafeed').modal('openModal');
    },
    importDataFeed: function () {
        jQuery("#import-datafeed").find("#datafeed-error").remove();
        var input = jQuery("#import-datafeed").find("input#datafeed");
        var csv_file = input.val();

        // file empty ?
        if (csv_file == "") {
            jQuery("<label>", {
                "class": "mage-error",
                "id": "datafeed-error",
                "text": "This is a required field"
            }).appendTo(input.parent());
            return;
        }

        // valid file ?
        if (csv_file.indexOf(".dfm") < 0) {
            jQuery("<label>", {
                "class": "mage-error",
                "id": "datafeed-error",
                "text": "Invalid file type"
            }).appendTo(input.parent());
            return;
        }

        // file not empty + valid file
        jQuery("#import-datafeed").submit();

    }
};

require([
    "jquery",
    "mage/mage",
    "jquery/ui",
    "Magento_Ui/js/modal/modal"
], function ($) {
    $(function () {
        if (typeof updater_url === 'undefined') {
            updater_url = "";
        }
        DataFeedManager.updater.init();
    });
});