import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';
import DeviceDetection from 'src/helper/device-detection.helper';
import Iterator from 'src/helper/iterator.helper';

export default class FastOrderWidgetPlugin extends Plugin {

    static options = {
        searchWidgetSelector: '.js-search-form',
        searchWidgetResultSelector: '.js-search-result',
        searchWidgetResultItemSelector: '.js-result',
        searchWidgetInputFieldSelector: 'input.fast-order-input',
        searchWidgetButtonFieldSelector: 'button[type=submit]',
        searchWidgetUrlDataAttribute: 'data-url',
        searchWidgetCollapseButtonSelector: '.js-search-toggle-btn',
        searchWidgetCollapseClass: 'collapsed',
        articleResultsSelector:'li',
        amountMainResultSelector: '.amountMainProductSelector',
        productQuantitySelector: 'input.fast-order-quantity',

        searchWidgetDelay: 250,
        searchWidgetMinChars: 3
    };

    init() {
        try {
            this._inputFields = DomAccess.querySelectorAll(this.el, this.options.searchWidgetInputFieldSelector);
            this._inputQuantityFields = DomAccess.querySelectorAll(this.el, this.options.productQuantitySelector);
            this._url = DomAccess.getAttribute(this.el, this.options.searchWidgetUrlDataAttribute);
        } catch (e) {
            return;
        }

        this._client = new HttpClient();
        this._registerEvents();
    }

    /**
     * Register events
     * @private
     */
    _registerEvents() {
        const thisPlugin = this;
        // add listener to the form's input event
        this._inputFields.forEach(function(inputField) {
            inputField.addEventListener(
                'input',
                Debouncer.debounce(thisPlugin._handleInputEvent.bind(thisPlugin), thisPlugin.options.searchWidgetDelay),
                {
                    capture: true,
                    passive: true
                },
            );
        });

        this._inputQuantityFields.forEach(function(inputQuantityField) {
            inputQuantityField.addEventListener(
                'input',
                Debouncer.debounce(thisPlugin._handlePriceEvent.bind(thisPlugin), thisPlugin.options.searchWidgetDelay),
                {
                    capture: true,
                    passive: true
                },
            );
        });

        // add click event listener to body
        const event = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'click';
        document.body.addEventListener(event, this._onBodyClick.bind(this));
    }

    _handleSearchEvent(event) {
        const value = this._inputField.value.trim();

        // stop search if minimum input value length has not been reached
        if (value.length < this.options.searchWidgetMinChars) {
            event.preventDefault();
            event.stopPropagation();
        }
    }

    /**
     * Fire the XHR request if user inputs a search term
     * @private
     */
    _handleInputEvent(e) {
        this._inputField = e.target;
        const value = this._inputField.value.trim();
        // stop search if minimum input value length has not been reached
        if (value.length < this.options.searchWidgetMinChars) {
            // further clear possibly existing search results
            this._clearSuggestResults();
            return;
        }

        this._suggest(value);
        this.$emitter.publish('handleInputEvent', { value });
    }

    /**
     * Process the AJAX suggest and show results
     * @param {string} value
     * @private
     */
    _suggest(value) {
        const url = this._url + encodeURIComponent(value);

        this.$emitter.publish('beforeSearch');

        this._client.abort();
        this._client.get(url, (response) => {
            // remove existing search results popover first
            this._clearSuggestResults();
            // attach search results to the DOM
            this.el.insertAdjacentHTML('beforeend', response);

            this.el.querySelectorAll(this.options.articleResultsSelector).forEach(article => {
                article.addEventListener('click', this._onArticleClick.bind(this));
            });

            this.$emitter.publish('afterSuggest');
        });
    }

    /**
     *
     * @param e
     * @private
     */
    _onArticleClick(e) {
        const id = e.target.closest('li.article-section').id;
        this._inputField.value = e.target.closest('li.article-section').dataset.productNumber;

        let productPrice = e.target.closest('li.article-section').dataset.productPrice;
        const sequentialNumber = this._inputField.id.substr(this._inputField.id.length - 1);

        let inputFieldId = this._inputField.id;
        let inputHiddenId = 'input#' + inputFieldId.replace('fast-order-', 'fast-order-product-id-');
        let inputHiddenPriceId = 'input#' + inputFieldId.replace('fast-order-', 'fast-order-product-price-');
        let inputQuantityId = 'input#' + inputFieldId.replace('fast-order-', 'fast-order-quantity-');

        const inputHiddenField = DomAccess.querySelector(this.el, inputHiddenId);
        inputHiddenField.value = id;

        const inputPrice = DomAccess.querySelector(this.el, inputHiddenPriceId);
        const inputQuantity = DomAccess.querySelector(this.el, inputQuantityId);
        // set 1 as default quantity
        inputQuantity.value = !inputQuantity.value ? 1 : inputQuantity.value;
        inputPrice.value = productPrice;

        this._handlePriceEvent();

        this._clearSuggestResults();
    }

    _handlePriceEvent() {
        let totalPrice = 0;
        // determine total price
        this.el.querySelectorAll('.fast-order-product-price').forEach(priceInputHiddenField => {
            if (priceInputHiddenField.value) {
                const quantitySequentialFieldId = 'input#' + priceInputHiddenField.id.replace('fast-order-product-price-', 'fast-order-quantity-');
                const quantitySequentialField = DomAccess.querySelector(this.el, quantitySequentialFieldId);
                totalPrice += priceInputHiddenField.value * quantitySequentialField.value;

                // add price to main product row in the list
                if (quantitySequentialFieldId === 'input#fast-order-quantity-0') {
                    const mainPrice = priceInputHiddenField.value * quantitySequentialField.value;
                    document.querySelector(".article-search-amount-main").innerText
                        = parseFloat(mainPrice).toFixed(2);
                }
            }
        });

        document.querySelector(".article-search-amount-total").innerText
            = parseFloat(totalPrice).toFixed(2);
    }


    /**
     * Remove existing search results popover from DOM
     * @private
     */
    _clearSuggestResults() {
        // remove all result popovers
        const results = document.querySelectorAll(this.options.searchWidgetResultSelector);
        Iterator.iterate(results, result => result.remove());

        this.$emitter.publish('clearSuggestResults');
    }

    /**
     * Close/remove the search results from DOM if user
     * clicks outside the form or the results popover
     * @param {Event} e
     * @private
     */
    _onBodyClick(e) {
        // early return if click target is the search form or any of it's children
        if (e.target.closest(this.options.searchWidgetSelector)) {
            return;
        }

        // early return if click target is the search result or any of it's children
        if (e.target.closest(this.options.searchWidgetResultSelector)) {
            return;
        }
        // remove existing search results popover
        this._clearSuggestResults();

        this.$emitter.publish('onBodyClick');
    }

    /**
     * Sets the focus on the input field
     * @private
     */
    _focusInput() {
        if (!this._toggleButton.classList.contains(this.options.searchWidgetCollapseClass)) {
            this._toggleButton.blur(); // otherwise iOS won´t focus the field.
            this._inputField.setAttribute('tabindex', '-1');
            this._inputField.focus();
        }

        this.$emitter.publish('focusInput');
    }
}
