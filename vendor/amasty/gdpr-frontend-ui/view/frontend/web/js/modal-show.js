/**
 * Show Modal
 */

define([
    'mage/template',
    'Amasty_GdprFrontendUi/js/model/need-show',
    'mage/translate'
], function (
    template,
    cookieModel,
    $t
) {
    'use strict';

    var initialized = false,
        generalConfig = {},
        cssConfig = {};

    function initialize() {
        var aside = document.createElement('aside'),
            body = document.querySelector('body');

        aside.className = generalConfig.className;
        aside.setAttribute('aria-label', $t('Cookie Bar'));
        if (generalConfig.isPopup) {
            aside.classList.add('-popup');
        }

        aside.dataset.role = 'gdpr-cookie-container';
        aside.dataset.amgdprJs = 'modal';
        aside.innerHTML = template(
            generalConfig.template,
            {
                data: generalConfig,
                css: cssConfig,
                phrase: {
                    cookieBar: $t('Cookie Bar'),
                    cookieGroupFieldset: $t('Cookies Group Fieldset'),
                    moreInfo: $t('More Information'),
                    about: $t('About'),
                    cookieGroup: $t('Cookie Group'),
                    selectAndAcceptCookieGroup: $t('Please select and accept your Cookies Group')
                }
            });

        if (!generalConfig.barLocation && !generalConfig.isPopup) {
            body.append(aside);
        } else {
            body.prepend(aside);
        }
        initialized = true;

        if (generalConfig.isModal) {
            const event = document.createEvent('Event');

            event.initEvent('amclosemodal', false, true);
            setModalHeight(aside);

            if (generalConfig.isAllowCustomersCloseBar) {
                document.addEventListener('click', onOutsideClick.bind(this, event), true);
                document.getElementById('close-modal').addEventListener('click', closeModal.bind(this, event), true);
            }

            createOverlay(body, aside, event);
        } else {
            !generalConfig.isAllowCustomersCloseBar && createOverlay(body);
        }
    }

    function onOutsideClick(closeModalEvent, event) {
        var modal = document.querySelector('.amgdprcookie-modal-container'),
            groupsModal = document.querySelector('.amgdprcookie-groups-modal._show'),
            gdrpPrivacyModal = document.querySelector('.gdpr-privacy-container._show');

        if (!modal.contains(event.target) && !groupsModal && !gdrpPrivacyModal) {
            modal.dispatchEvent(closeModalEvent);
            document.removeEventListener('click', onOutsideClick, true);
        }
    }

    function closeModal(closeModalEvent) {
        var modal = document.querySelector('.amgdprcookie-modal-container');

        modal.dispatchEvent(closeModalEvent);
    }

    function createOverlay(body) {
        var div = document.createElement('div');

        div.className = 'ammodals-overlay';
        div.dataset.amgdprJs = 'overlay';
        body.append(div);
    }

    function setModalHeight(container) {
        var policyHeight = container.querySelector('[data-amcookie-js="policy"]').clientHeight,
            windowHeight = window.innerHeight,
            groupsContainer = container.querySelector('[data-amcookie-js="groups"]');

        groupsContainer.style.height = windowHeight - policyHeight + 'px';
    }

    return function (config) {
        if (!config.isSecond) {
            generalConfig = config;
            cssConfig = config.cssConfig;
        }

        if (!config.isSecond && cookieModel.isShowNotificationBarBefore(config.firstShowProcess)) {
            initialize();
        }

        if (!initialized && config.lastUpdate && cookieModel.isShowNotificationBarAfter(config.lastUpdate)) {
            initialize();
        }

        return {
            buttons: generalConfig.buttons,
            onOutsideClick
        }
    };
});
