define([
    'jquery',
    'mage/url',
    'text!Amasty_Storelocator/template/modal/modal-popup.html',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Amasty_Storelocator/vendor/fancybox/jquery.fancybox.min',
    'Amasty_Storelocator/js/validate-review',
    'Amasty_Base/vendor/slick/slick.min',
    'Magento_Review/js/error-placement',
    'Magento_Ui/js/lib/view/utils/async',
    'domReady!',
    'mage/loader'
], function ($, urlBuilder, popupTpl) {

    $.widget('mage.amLocationPage', {
        options: {},
        mapSelector: '[data-amlocator-js="location-map"]',
        directionsSelector: '[data-amlocator-js="directions"]',
        originPointSelector: '[data-amlocator-js="origin-point"]',
        destinationPointSelector: '[data-amlocator-js="destination-point"]',
        reviewMessageSelector: '[data-amlocator-js="review-message"]',
        toggleReviewSelector: '[data-amlocator-js="toggle-review"]',
        reviewPopupSelector: '[data-amlocator-js="review-popup"]',
        panoramaSelector: '[data-amlocator-js="locator-panorama"]',
        reviewPopupContentSelector: '[data-amlocator-js="amlocator-review-popup-content"]',
        reviewFormSelector: '#amlocator-review-form',
        travelModeSelector: '[data-amlocator-js="travel-mode"]',
        swapModeSelector: '[data-amlocator-js="swap-mode"]',
        directionsPanelImageSelector: '[data-amlocator-js="directions-panel"] img[class^=adp-marker]',
        reviewControllerUrl: 'amlocator/location/savereview',
        reviewPopupClassName: 'amlocator-review-popup',
        directionsService: null,
        directionsDisplay: null,
        panoramaService: null,
        travelMode: 'DRIVING',
        actionTriggerKeyCodes: [13, 32], /* Enter and Space */
        popup: null,

        _create: function () {
            var self = this;

            if (google !== undefined) {
                self.directionsService = new google.maps.DirectionsService();
                self.directionsDisplay = new google.maps.DirectionsRenderer();
                self.panoramaService = new google.maps.StreetViewService();
            }

            if ($(window).width() <= 768) {
                $('[data-amlocator-js="route-creator"]').before($(this.mapSelector));
                $(this.directionsSelector).after($('[data-amlocator-js="location-attributes"]'));
            }

            this.initializeMap();
            this.initializeRoute();
            this.initializeGallery();
            this.initReviewPopup();
            this.initReviewSubmit();

            $(self.reviewMessageSelector).each(function () {
                if ($(this)[0].clientHeight == $(this)[0].scrollHeight) {
                    $(this).siblings('.amlocator-footer').find(self.toggleReviewSelector).hide();
                }
            });

            $(self.toggleReviewSelector).on('click', function () {
                var reviewMessage = $(this).parents('[data-amlocator-js="location-review"]').find(self.reviewMessageSelector);

                reviewMessage.toggleClass('-collapsed');
                if (reviewMessage.is('.-collapsed')) {
                    $(this).text($.mage.__('See full review'));
                } else {
                    $(this).text($.mage.__('Collapse'));
                }
            });

            $('[data-amlocator-js="collapse-trigger"]').on('click keyup', function (event) {
                if (event.type === 'keyup' && !self.actionTriggerKeyCodes.includes(event.which)) {
                    return;
                }

                self.toggleAttribute($(this), 'aria-expanded');
                $(this).siblings('[data-amlocator-js="collapse-content"]').slideToggle().toggleClass('-collapsed');
                $(this).find('[data-amlocator-js="collapse-indicator"]').toggleClass('-down');
                event.stopPropagation();
            });

            $('[data-amlocator-js="write-review"]').on('click', function () {
                $(self.reviewPopupSelector).fadeIn();
                self.popup.modal('openModal');
            });

            $(self.panoramaSelector).on('click', function () {
                self.initPanorama();
            });

            $.async(self.directionsPanelImageSelector, (image) => {
                const $image = $(image);
                if (!!$image.attr('alt')) {
                    return;
                }

                $image.attr('alt', $.mage.__('Marker Image'));
            });
        },

        initReviewPopup: function () {
            this.popup = $(this.reviewPopupContentSelector).modal({
                modalClass: this.reviewPopupClassName,
                popupTpl: popupTpl,
                buttons: []
            });
        },

        initReviewSubmit: function () {
            var self = this,
                reviewForm = $(this.reviewFormSelector);

            if (reviewForm.length) {
                reviewForm.submit(function (e) {

                    if (reviewForm.valid()) {
                        e.preventDefault();
                        self.sendReview(reviewForm);
                    }
                });
            }
        },

        sendReview: function (form) {
            var self = this,
                formData = form.serializeArray(),
                url = urlBuilder.build(this.reviewControllerUrl);

            $.ajax({
                showLoader: true,
                data: formData,
                url: url,
                method: "POST"
            }).done(function () {
                $(self.reviewPopupSelector).fadeOut();
                form[0].reset();
                self.popup.modal('closeModal');
            });
        },

        initializeMap: function () {
            var self = this,
                mapOptions = {
                    zoom: 9,
                    center: self.options.locationData,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    mapId: this.options.locationData.mapId
                },
                map = new google.maps.Map($(this.mapSelector)[0], mapOptions)

            const markerOptions = {
                position: new google.maps.LatLng(self.options.locationData.lat, self.options.locationData.lng),
                map: map
            };

            if (!!this.options.locationData.marker_url) {
                const pinIcon = document.createElement('img');
                pinIcon.src = this.options.locationData.marker_url;
                markerOptions.content = pinIcon;
            }

            const locationMarker
                = new google.maps.marker.AdvancedMarkerElement(markerOptions);

            self.directionsDisplay.setMap(map);
        },

        initializeRoute: function () {
            var self = this,
                autocompleteOrigin = new google.maps.places.Autocomplete($(self.originPointSelector)[0]),
                locationLatLng = self.options.locationData.lat.toString() + ', ' + self.options.locationData.lng.toString(),
                swapMode = $(this.swapModeSelector);

            google.maps.event.addListener(autocompleteOrigin, 'place_changed', function () {
                self.calcRoute($(self.originPointSelector).val(), locationLatLng, swapMode.data('checked'));
            });

            $(this.travelModeSelector).on('click keyup', (event) => {
                if (event.type === 'keyup' && !self.actionTriggerKeyCodes.includes(event.which)) {
                    return;
                }

                this.updateCurrentTravelMode($(event.target))
                    && this.calcRoute($(self.originPointSelector).val(), locationLatLng, swapMode.data('checked'));
            });

            swapMode.on('click', (event) => {
                const $swapMode = $(event.target);

                this.toggleAttribute($swapMode, 'aria-checked');
                $swapMode.data('checked', !$swapMode.data('checked'));
                this.calcRoute($(this.originPointSelector).val(), locationLatLng, $swapMode.data('checked'));
            });

            self.directionsDisplay.setPanel($('[data-amlocator-js="directions-panel"]')[0]);
        },

        updateCurrentTravelMode: function ($travelModeNode) {
            if (this.travelMode === $travelModeNode.data('travelModeValue')) {
                return false;
            }

            this.travelMode = $travelModeNode.data('travelModeValue');
            $(this.travelModeSelector).removeClass('radio-checked');
            $(this.travelModeSelector).attr('aria-checked', false);
            $travelModeNode.addClass('radio-checked');
            $travelModeNode.attr('aria-checked', true);
            return true;
        },

        calcRoute: function (origin, destination, swapMode) {
            var self = this,
                request = {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode[this.travelMode]
            };

            if (swapMode) {
                request.origin = destination;
                request.destination = origin;
                $(self.destinationPointSelector).after($(self.originPointSelector));
            } else {
                $(self.destinationPointSelector).before($(self.originPointSelector));
            }

            if (origin) {
                $('body').trigger('processStart');

                self.directionsService.route(request, function (result, status) {
                    if (status == 'OK') {
                        self.directionsDisplay.setDirections(result);
                        $(self.directionsSelector).show();
                    } else {
                        alert($.mage.__('Sorry, Google failed to get directions and answered with status: ') + status);
                    }
                    $('body').trigger('processStop');
                });
            }
        },

        initializeGallery: function () {
            $('[data-amlocator-js="locator-gallery"]').slick({
                infinite: false,
                slidesToShow: 3,
                slidesToScroll: 3
            });
        },

        initPanorama: function () {
            this.panoramaService.getPanorama(
                {location: new google.maps.LatLng(this.options.locationData.lat, this.options.locationData.lng)},
                function (result, status) {
                    if (status === 'OK') {
                        panorama = new google.maps.StreetViewPanorama(
                            $('[data-amlocator-js="location-map"]')[0],
                            {
                                pano: result.location.pano,
                                enableCloseButton: true
                            }
                        );
                    } else {
                        alert($.mage.__('Sorry, there is no available view of the street yet.'));
                    }
                }
            )
        },

        toggleAttribute: function (element, attributeName) {
            element.attr(attributeName, (i, attr) => {
                return attr === 'true' ? 'false' : 'true';
            });
        }
    });

    return $.mage.amLocationPage;
});
