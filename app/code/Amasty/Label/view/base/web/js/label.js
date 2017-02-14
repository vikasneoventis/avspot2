define([
    "jquery",
    "jquery/ui"
], function ($) {

    $.widget('mage.amShowLabel', {
        options: {},
        textElement: null,
        image: null,
        imageWidth: null,
        imageHeight: null,
        parent: null,

        _create: function () {
            this.element     = $(this.element);
            this.image       = this.element.find('.amasty-label-image');
            this.textElement = this.element.find('.amasty-label-text');
            this.parent      = this.element.parent();

            if(!this.image.length) {
                this.setStyleIfNotExist(
                    this.element,
                    {
                        'width': '100px',
                        'height': '50px'
                    }
                );
            }

            /* move label to container from settings*/
            if (this.options.path && this.options.path != "") {
                var newParent = this.parent.find(this.options.path);
                if (newParent.length) {
                    this.parent = newParent;
                    newParent.append(this.element);
                }
            }

            /*required for child position absolute*/
            this.parent.css('position', 'relative');
            /*fix issue with hover on product grid*/
            this.element.closest('.product-item-info').css('zIndex', '2000');


            /* observe zoom load event for moving label*/
            this.productPageZoomEvent();

            /*get default image size*/
            if (this.imageLoaded( this.image)) {
                var me = this;
                this.image.load(function () {
                    me.element.fadeIn();
                    me.imageWidth = this.naturalWidth;
                    me.imageHeight = this.naturalHeight;
                });
            }
            else{
                this.element.fadeIn();
                if (this.image[0]) {
                    this.imageWidth = this.image[0].naturalWidth;
                    this.imageHeight = this.image[0].naturalHeight;
                }
            }

            this.setLabelStyle();
            this.setLabelPosition();
        },


        imageLoaded: function (img) {
            if (!img.complete) {
                return false;
            }

            if (typeof img.naturalWidth !== "undefined" && img.naturalWidth === 0) {
                return false;
            }

            return true;
        },

        productPageZoomEvent: function () {
            if (this.options.mode == 'prod') {
                var amlabelObject = this;

                $(document).on('fotorama:load', function (event) {
                    if (amlabelObject && amlabelObject.options.path && amlabelObject.options.path != "") {
                        var newParent = amlabelObject.parent.find(amlabelObject.options.path);
                        if (newParent.length && newParent != amlabelObject.parent) {
                            amlabelObject.parent.css('position', '');
                            amlabelObject.parent = newParent;
                            newParent.append(amlabelObject.element);
                            newParent.css('position', 'relative');
                        }
                    }
                })

                $(window).resize(function() {
                    amlabelObject.setLabelStyle();
                    amlabelObject.setLabelPosition();
                });
            }
        },

        setStyleIfNotExist: function (element, styles){
            for(style in styles) {
                if (element.attr('style').indexOf('; ' + style) == -1 && element.attr('style').indexOf(';' + style) == -1) {
                    element.css(style, styles[style]);
                }
            }

        },

        setLabelStyle: function () {
            /*for text element*/
            this.setStyleIfNotExist(
                this.textElement,
                {
                'padding'    : '0 3px',
                'position'   : 'absolute',
                'white-space' : 'nowrap',
                'width'      : '100%'
            });
            if(this.image.length) {
                /*for image*/
                this.image.css({
                    'width': '100%'
                });

                /*get block size depend settings*/
                if (this.options.size) {
                    var parentWidth = parseInt(this.parent.css('width').replace(/\D+/g, ""))
                    if (parentWidth && this.options.size > 0) {
                        var nativeWidth = this.imageWidth;
                        this.imageWidth = parentWidth * this.options.size / 100;

                        //set responsive font size
                        var font = this.textElement.css('fontSize').replace(/\D+/g, "");
                        var newFont = font * this.imageWidth / nativeWidth * 2;
                        if (newFont < font) {
                            this.textElement.css({'fontSize': newFont});
                        }
                    }
                }
                else {
                    this.imageWidth = this.imageWidth + 'px';
                }
                this.setStyleIfNotExist(this.element, {'width': this.imageWidth});
                this.imageHeight = this.image.height();

                /*if container doesn't load(height = 0 ) set proportional height*/
                if (!this.imageHeight && this.image[0] && 0 != this.image[0].naturalWidth) {
                    var tmpWidth = this.image[0].naturalWidth;
                    var tmpHeight = this.image[0].naturalHeight;
                    this.imageHeight = parseFloat(this.imageWidth) * (tmpHeight / tmpWidth);
                }

                this.textElement.css('lineHeight', this.imageHeight + 'px');
                /*for whole block*/
                this.setStyleIfNotExist(this.element, {
                    'height': this.imageHeight + 'px'
                });
            }

            this.setStyleIfNotExist(this.element, {
                'line-height': 'normal',
                'position': 'absolute',
                'z-index': 995
            });

            this.element.click(function() {
                $(this).parent().trigger('click');
            });
        },

        setPosition: function (position) {
            this.options.position = position;
            this.element.css("top", "").css("left", "").css("right", "").css("bottom", "");
            this.element.css("margin-top", "").css("margin-bottom", "").css("margin-left", "").css("margin-right", "");
            this.setLabelPosition();
        },

        setLabelPosition: function () {
            switch (this.options.position) {
                case 'top-left':
                    this.setStyleIfNotExist(this.element, {
                        'top'  : 0,
                        'left' : 0
                    });
                    break;
                case 'top-center':
                    this.setStyleIfNotExist(this.element, {
                        'top': 0,
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'top-right':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'right' : 0
                    });
                    break;

                case 'middle-left':
                    this.setStyleIfNotExist(this.element, {
                        'left' : 0,
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto'
                    });
                    break;
                case 'middle-center':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto',
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'middle-right':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto',
                        'right' : 0
                    });
                    break;

                case 'bottom-left':
                    this.setStyleIfNotExist(this.element, {
                        'bottom'  : 0,
                        'left'    : 0
                    });
                    break;
                case 'bottom-center':
                    this.setStyleIfNotExist(this.element, {
                        'bottom': 0,
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'bottom-right':
                    this.setStyleIfNotExist(this.element, {
                        'bottom'   : 0,
                        'right'    : 0
                    });
                    break;
            }
        }
    });

    return $.mage.amshowLabel;
});
