define([
    'jquery',
    'Amasty_Storelocator/js/model/states-storage',
    'mage/translate',
    'Amasty_Storelocator/vendor/chosen/chosen.min',
    'Amasty_Storelocator/vendor/jquery.ui.touch-punch.min',
    'Magento_Ui/js/lib/knockout/bindings/range',
    'Magento_Ui/js/modal/modal',
    'jquery-ui-modules/slider'
], function ($, statesStorage) {
    $.widget('mage.amLocator', {
        options: {},
        url: null,
        useBrowserLocation: null,
        useGeo: null,
        imageLocations: null,
        map: {},
        marker: {},
        storeListIdentifier: '',
        mapId: '',
        mapContainerId: '',
        needGoTo: false,
        hasNavigateMe: false,
        markerCluster: {},
        bounds: {},
        selectors: {
            filterContainer: '[data-amlocator-js="filters-container"]',
            attributeForm: '[data-amlocator-js="attributes-form"]',
            multipleSelect: '[data-amlocator-js="multiple-select"]',
            radiusSelect: '[data-amlocator-js="radius-select"]',
            radiusSlider: '[data-amlocator-js="range-slider"]',
            sliderHandle: '[data-amlocator-js="ui-slider-handle"]',
            radiusSelectValue: '[data-amlocator-js="radius-value"]',
            resetSelector: '[data-amlocator-js="reset"]',
            addressSelector: '[data-amlocator-js="address"]',
            searchSelector: '[data-amlocator-js="search"]',
            attributeFilterTitle: '[data-amlocator-js="filters-title"]',
            mapPinIcon: '[data-amlocator-js="map-pin-icon"]',
            todayCollapsible: '.amlocator-today[data-amlocator-js="collapse-trigger"]'
        },
        hiddenState: '-hidden',
        latitude: 0,
        longitude: 0,
        actionTriggerKeyCodes: [13, 32], /* Enter and Space */

        _create: function () {
            this.ajaxCallUrl = this.options.ajaxCallUrl;
            this.useBrowserLocation = this.options.useBrowserLocation;
            this.useGeo = this.options.useGeo;
            this.imageLocations = this.options.imageLocations;
            this.mapContainer = $('#' + this.options.mapContainerId);
            this.latitude = this.options.lat ?? 0;
            this.longitude = this.options.lng ?? 0;

            this.initializeMap();
            this.initializeFilter();
            this.Amastyload();
        },

        navigateMe: function () {
            var self = this;

            self.needGoTo = 1;

            if (navigator.geolocation && self.useBrowserLocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    if (!self.mapContainer.find('.amlocator-text').val()) {
                        self.latitude = position.coords.latitude;
                        self.longitude = position.coords.longitude;
                    }

                    self.makeAjaxCall(true);
                }, this.navigateFail.bind(self));
            } else if (this.options.useGeoConfig) {
                this.makeAjaxCall(true);
            } else {
                alert($.mage.__('Sorry we\'re unable to display the nearby stores because the "Use browser location" or "Use Geo IP" options are disabled in the module settings. Please, contact the administrator.'));
            }
        },

        navigateFail: function (error) {
            // error param exists when user block browser location
            if (this.options.useGeoConfig == 1) {
                this.makeAjaxCall(true);
            } else if (error.code == 1) {
                alert(error.message);
            }
        },

        collectParams: function (sortByDistance, isReset) {
            return {
                'lat': this.latitude,
                'lng': this.longitude,
                'radius': this.getRadius(isReset),
                'product': this.options.productId,
                'category': this.options.categoryId,
                'attributes': this.mapContainer.find(this.selectors.attributeForm).serializeArray(),
                'sortByDistance': sortByDistance ? 1 : 0
            };
        },

        getMeasurement: function () {
            if (this.mapContainer.find('#amlocator-measurement').length > 0) {
                return this.mapContainer.find('#amlocator-measurement').val();
            }

            return 'km';
        },

        getRadius: function (isReset) {
            var radius = null;
            var currentRadiusValue = this.mapContainer.find(this.selectors.radiusSelectValue);

            if (isReset) {
                return 0;
            }

            if (this.options.isRadiusSlider) {
                if (this.mapContainer.find(this.selectors.radiusSelectValue).length
                    && parseInt(currentRadiusValue.text()) >= this.options.minRadiusValue) {
                    if (!this.hasNavigateMe) {
                        return 0
                    }

                    radius = currentRadiusValue.val() ? currentRadiusValue.val() : this.options.minRadiusValue;
                } else {
                    return null;
                }
            } else if (this.mapContainer.find(this.selectors.radiusSelect)) {
                radius = this.mapContainer.find(this.selectors.radiusSelect).val();
            }

            if (this.getMeasurement() == 'km') {
                radius /= 1.609344;
            }

            return radius;
        },

        makeAjaxCall: function (sortByDistance = false, isReset) {
            var self = this,
                params = this.collectParams(sortByDistance, isReset);

            $.ajax({
                url: self.ajaxCallUrl,
                type: 'POST',
                data: params,
                showLoader: true
            }).done($.proxy(function (response) {
                response = JSON.parse(response);
                self.options.jsonLocations = response;
                self.getIdentifiers();
                self.Amastyload();
            }));
        },

        calculateDistance: function (lat, lng) {
            measurement = this.getMeasurement();

            for (var location in this.options.jsonLocations.items) {
                var distance = MarkerClusterer.prototype.distanceBetweenPoints_(
                        new google.maps.LatLng(
                            lat,
                            lng
                        ),
                        new google.maps.LatLng(
                            this.options.jsonLocations.items[location].lat,
                            this.options.jsonLocations.items[location].lng
                        )
                    ),

                    measurementLabel = $.mage.__('km');

                if (measurement == 'mi') {
                    distance /= 1.609344;
                    measurementLabel = $.mage.__('mi');
                }

                var locationId = this.options.jsonLocations.items[location].id,
                    distanceText = parseInt(distance) + ' ' + measurementLabel;

                this.mapContainer.find('#amasty_distance_' + locationId).show().find('span.amasty_distance_number').text(distanceText);
            }
        },

        plusCodes: function (self) {
            var value = self.mapContainer.find('.amlocator-text').val(),
                regExp = new RegExp('^[A-Z0-9]{8}\\+\\S{2}$');

            if (regExp.test(value) === false) {
                return false;
            }

            self.geocoder.geocode({ 'address': value }, function (results, status) {
                if (status == 'OK') {
                    self.latitude = results[0].geometry.location.lat();
                    self.longitude = results[0].geometry.location.lng();

                    if (self.options.enableCountingDistance) {
                        self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng());
                    }

                    return true;
                }

                return false;
            });
        },

        Amastyload: function () {
            this.deleteMarkers(this.options.mapId);
            var self = this,
                mapId = this.options.mapId;

            this.processLocation();
            this.initializeStoreList();

            if (this.options.enableClustering) {
                this.markerCluster = new MarkerClusterer(this.map[this.options.mapId], this.marker[this.options.mapId], { imagePath: this.imageLocations + '/m' });
            }

            this.geocoder = new google.maps.Geocoder();
        },

        initializeMap: function () {
            var myOptions = {
                    zoom: 9,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    mapId: this.options.mapId
                },

                self = this;

            self.infowindow = [];
            self.marker[self.options.mapId] = [];
            self.map[self.options.mapId] = [];
            self.map[self.options.mapId] = new google.maps.Map($('#' + self.options.mapId)[0], myOptions);

            if (self.options.showSearch) {
                var address = self.mapContainer.find('.amlocator-text')[0],
                    autocompleteOptions = {
                        componentRestrictions: { country: self.options.allowedCountries },
                        fields: [ 'geometry.location' ]
                    },
                    autocomplete = new google.maps.places.Autocomplete(address, autocompleteOptions);

                self.mapContainer.find('.amlocator-text').on('keyup', function (e) {
                    if (self.plusCodes(self) === true) {
                        e.preventDefault();
                    }
                });
                self.autocomplete = autocomplete;
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();

                    if (place.geometry != null) {
                        self.latitude = place.geometry.location.lat();
                        self.longitude = place.geometry.location.lng();

                        if (self.options.enableSuggestionClickSearch) {
                            self.makeAjaxCall(true);
                            self.toggleMapButtons(true);
                        }

                        if (self.options.enableCountingDistance) {
                            self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng());
                        }
                    } else {
                        alert($.mage.__('You need to choose address from the dropdown with suggestions.'));
                        self.makeAjaxCall();
                    }
                });
            }

            if (self.options.automaticLocate) {
                self.navigateMe();
            }
        },

        initializeFilter: function () {
            var self = this;

            self.mapContainer.find('.amlocator-button.-nearby').on('click', function () {
                self.getIdentifiers($(this));
                self.ajaxCallUrl = self.options.ajaxCallUrl;
                self.hasNavigateMe = true;
                self.navigateMe();
            });

            self.mapContainer.find('.amlocator-text').on('keyup', function () {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    self.mapContainer.find('.amlocator-text').click();
                }
            });

            if (this.options.isRadiusSlider) {
                this.createRadiusSlider();
            }

            // workaround for chosen.js accessibility issue with search input
            self.mapContainer.find(self.selectors.multipleSelect).on('chosen:ready', (event, chosenObj) => {
                const $searchField = $(chosenObj?.chosen?.search_field);
                const attributeId = $(event.target).data('attributeId');
                if ($searchField.length === 0 || attributeId === undefined) {
                    return;
                }

                const searchFieldId = `amlocator-chosen-search-input-${attributeId}`;
                const $searchLabel = $('<label></label>');

                $searchField.attr('id', searchFieldId);
                $searchLabel.attr('for', searchFieldId);
                $searchLabel.addClass('amlocator-chosen-search-label');
                $searchLabel.text(chosenObj.chosen?.placeholder_text_multiple ?? $.mage.__('Select Some Options'));
                $searchLabel.insertBefore($searchField);
            });

            self.mapContainer.find(self.selectors.multipleSelect).chosen({
                placeholder_text_multiple: $.mage.__('Select Some Options')
            });

            self.mapContainer.find('.amlocator-clear').on('click', function (e) {
                e.preventDefault();
                var attrForm = $(this).parents(self.selectors.filterContainer)
                    .find(self.selectors.attributeForm);

                attrForm.find('option:selected').removeAttr('selected');
                attrForm[0].reset();
                attrForm.find(self.selectors.multipleSelect).val(null).trigger('chosen:updated');
                self.getIdentifiers($(this));
                self.makeAjaxCall();
            });

            self.mapContainer.find(this.selectors.searchSelector).on('click', self.searchLocations.bind(this));
            self.mapContainer.find(this.selectors.addressSelector).on('keydown', function (e) {
                if (e.keyCode !== 13) {
                    return;
                }

                self.searchLocations();
            });

            self.mapContainer.find(this.selectors.attributeFilterTitle).on('click', function () {
                self.toggleAriaExpanded($(this));
                $(this).parent().find(self.selectors.filterContainer).slideToggle();
                $(this).find('.amlocator-arrow').toggleClass('-down');
            });

            self.mapContainer.find(this.selectors.attributeFilterTitle).on('keyup', function (e) {
                if (e.keyCode !== 13) {
                    return;
                }

                $(this).triggerHandler('click');
            });

            self.mapContainer.find('.amlocator-filter-attribute').on('click', function () {
                self.getIdentifiers($(this));
                $(this).parent().find(self.selectors.filterContainer).slideToggle();
                self.makeAjaxCall();
            });

            self.mapContainer.find(this.selectors.resetSelector).on('click', this.resetMap.bind(this));
        },

        toggleFilters: function () {

        },

        toggleMapButtons: function (isShow) {
            $(this.selectors.resetSelector).toggleClass(this.hiddenState, !isShow);
            $(this.selectors.searchSelector).toggleClass(this.hiddenState, isShow);
        },

        resetMap: function () {
            this.makeAjaxCall(false, true);
            $(this.selectors.addressSelector).val('');
            this.toggleMapButtons(false);
        },

        searchLocations: function () {
            var self = this,
                place = self.autocomplete.getPlace();

            if (!place || place.geometry == null) {
                alert($.mage.__('You need to choose address from the dropdown with suggestions.'));
                return false;
            }

            if (!$(self.selectors.addressSelector).val()) {
                return false;
            }

            self.getIdentifiers($(this));
            self.makeAjaxCall(true);
            self.toggleMapButtons(true);
        },

        toggleAriaExpanded: function (element) {
            element.attr('aria-expanded', (i, attr) => {
                return attr === 'true' ? 'false' : 'true';
            });
        },

        initializeStoreList: function () {
            var self = this,
                mapId = this.options.mapId;

            this.mapContainer.find(this.selectors.todayCollapsible).on('click keyup',function (event) {
                if (event.type === 'keyup' && !self.actionTriggerKeyCodes.includes(event.which)) {
                    return;
                }

                $(this).next('.amlocator-week').slideToggle();
                $(this).find('.amlocator-arrow').toggleClass('-down');
                self.toggleAriaExpanded($(this));
                event.stopPropagation();
            });

            self.mapContainer.find('.amlocator-pager-container .item a').on('click', function (e) {
                this.getIdentifiers(e);
                this.ajaxCallUrl = e.currentTarget.href;
                this.makeAjaxCall(true, false);
                e.preventDefault();
            }.bind(this));

            self.mapContainer.find('.amlocator-store-desc').on('click', function () {
                var id = $(this).attr('data-amid');

                self.getIdentifiers($(this));

                self.gotoPoint(id);
            });

            self.mapContainer.find(self.selectors.mapPinIcon).on('keydown', (event) => {
                if (event.which !== 13) {
                    return;
                }

                $(event.target).closest('.amlocator-store-desc').click();
            });

            if (self.options.enableCountingDistance
                && self.latitude
                && self.longitude
            ) {
                self.calculateDistance(self.latitude, self.longitude);
            }

            statesStorage.storeListIsLoaded(true);
        },

        getIdentifiers: function (event) {
            if (event && !this.mapContainer) {
                this.mapContainer = event.parents().closest('.amlocator-map-container');
            }

            this.storeListIdentifier = this.mapContainer.find('.amlocator-store-list');
            this.mapIdentifier = this.mapContainer.find('.amlocator-map');
        },

        processLocation: function () {
            var self = this,
                locations = self.options.jsonLocations,
                curtemplate = '';

            self.bounds = new google.maps.LatLngBounds();

            for (var i = 0; i < locations.totalRecords; i++) {
                curtemplate = locations.items[i].popup_html;
                this.createMarker(locations.items[i], curtemplate);
            }

            for (var locationId in this.marker[this.options.mapId]) {
                if (this.marker[this.options.mapId].hasOwnProperty(locationId)) {
                    this.bounds.extend(this.marker[this.options.mapId][locationId].position);
                }
            }

            this.map[this.options.mapId].fitBounds(this.bounds);

            if (locations.totalRecords === 1 || self.needGoTo) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setZoom(self.options.mapZoom);
                });
            }

            if (locations.totalRecords === 0) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setCenter(
                        new google.maps.LatLng(
                            0,
                            0
                        )
                    );
                    self.map[self.options.mapId].setZoom(2);
                    alert($.mage.__('Sorry, no locations were found.'));
                });
            }

            if (self.storeListIdentifier) {
                self.storeListIdentifier.html(locations.block);
                $( self.storeListIdentifier).trigger('contentUpdated');

                if (locations.totalRecords > 0 && self.needGoTo) {
                    self.gotoPoint(locations.items[0].id);
                    self.needGoTo = false;
                }
            }
        },

        gotoPoint: function (myPoint) {
            var self = this,
                mapId = self.mapIdentifier.attr('id') || self.options.mapId;

            self.closeAllInfoWindows(mapId);

            self.mapContainer.find('.-active').removeClass('-active');

            // add class if click on marker
            self.mapContainer
                .find('[data-amid=' + myPoint + ']')
                .parent('.amlocator-store-container')
                .addClass('-active');
            self.map[mapId].setCenter(
                new google.maps.LatLng(
                    self.marker[mapId][myPoint].position.lat,
                    self.marker[mapId][myPoint].position.lng
                )
            );
            self.map[mapId].setZoom(self.options.mapZoom);
            self.marker[mapId][myPoint].infowindow.open(
                self.map[mapId],
                self.marker[mapId][myPoint]
            );
        },

        createMarker: function (item, html) {
            const { lat, lng, id: locationId, marker_url: marker } = item;
            const markerOptions = {
                position: new google.maps.LatLng(lat, lng),
                map: this.map[this.options.mapId],
                title: item.name
            };
            if (!!marker) {
                const pinIcon = document.createElement('img');
                pinIcon.src = marker;
                markerOptions.content = pinIcon;
            }

            var self = this,
                newmarker = new google.maps.marker.AdvancedMarkerElement(markerOptions);

            newmarker.infowindow = new google.maps.InfoWindow({
                content: html
            });
            newmarker.locationId = locationId;
            google.maps.event.addListener(newmarker, 'click', function () {
                self.mapIdentifier = $('#' + self.element[0].id);
                self.gotoPoint(this.locationId);
            });

            // using locationId instead 0, 1, 2, i counter
            this.marker[this.options.mapId][locationId] = newmarker;
        },

        closeAllInfoWindows: function (mapId) {
            var spans = $('#' + mapId + ' span');

            for (var i = 0, l = spans.length; i < l; i++) {
                spans[i].className = spans[i].className.replace(/\active\b/, '');
            }

            if (typeof this.marker[mapId] !== 'undefined') {
                for (var marker in this.marker[mapId]) {
                    if (this.marker[mapId].hasOwnProperty(marker)) {
                        this.marker[mapId][marker].infowindow.close();
                    }
                }
            }
        },

        createRadiusSlider: function () {
            var self = this,
                radiusValue = self.mapContainer.find(self.selectors.radiusSelectValue),
                sliderHandle = self.mapContainer.find(self.selectors.sliderHandle),
                radiusMeasurment = self.mapContainer.find('[data-amlocator-js="radius-measurment"]'),
                measurmentSelect = self.mapContainer.find('[data-amlocator-js="measurment-select"]');

            if (self.options.minRadiusValue <= self.options.maxRadiusValue) {
                var slider = self.mapContainer.find(self.selectors.radiusSlider).slider({
                        range: 'min',
                        min: self.options.minRadiusValue,
                        max: self.options.maxRadiusValue,
                        create: function () {
                            radiusValue.text($(this).slider('value'));

                            if (self.options.measurementRadius != '') {
                                radiusMeasurment.text(self.options.measurementRadius);
                            } else {
                                radiusMeasurment.text(measurmentSelect.val());
                            }

                            $('#' + self.options.searchRadiusId).val($(this).slider('value'));
                        },
                        slide: function (event, ui) {
                            sliderHandle.attr('aria-valuenow', ui.value);
                            radiusValue.text(ui.value);
                            radiusValue.val(ui.value);
                            $('#' + self.options.searchRadiusId).val(ui.value);
                        }
                    }),

                    radiusValueBuffer = '',
                    radiusValueTimer = '';

                self.mapContainer.find('.amlocator-tooltip').on('keyup', function (e) {
                    if (e.which !== 8 && e.which !== 0 && (e.which < 48 || e.which > 57)) {
                        return;
                    }

                    if (radiusValueTimer) {
                        clearTimeout(radiusValueTimer);
                    }

                    radiusValueBuffer += e.originalEvent.key;
                    radiusValue.html(radiusValueBuffer);
                    radiusValueTimer = setTimeout(function () {
                        if (radiusValueBuffer < self.options.minRadiusValue) {
                            radiusValueBuffer = self.options.minRadiusValue;
                        } else if (radiusValueBuffer > self.options.maxRadiusValue) {
                            radiusValueBuffer = self.options.maxRadiusValue;
                        }

                        radiusValue.html(radiusValueBuffer);
                        slider.slider('value', radiusValueBuffer);
                        radiusValueBuffer = '';
                    }, 1000);
                });

                slider.on('click', function () {
                    self.mapContainer.find('.amlocator-tooltip').focus();
                });
            }

            measurmentSelect.on('change', function () {
                radiusMeasurment.text(this.value);
            });
        },

        deleteMarkers: function (mapId) {
            if (!_.isEmpty(this.markerCluster)) {
                this.markerCluster.clearMarkers();
            }

            for (var marker in this.marker[mapId]) {
                if (this.marker[mapId].hasOwnProperty(marker)) {
                    this.marker[mapId][marker].setMap(null);
                }
            }

            this.marker[mapId] = [];
        }

    });

    return $.mage.amLocator;
});
