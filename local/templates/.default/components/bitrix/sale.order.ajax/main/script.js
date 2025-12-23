BX.saleOrderAjax = {

    BXCallAllowed: false,

    options: {},
    indexCache: {},
    controls: {},

    modes: {},
    properties: {},
    BXFormPosting: false,
    isLocationProEnabled: false,
    address: {
        fieldName: '',
        errors: {},
        selected: null,
        //Use for clear address if location type is change
        lastAddressOutRussia: null,
        previousValue: '',
        getInput: () => {
            const addressField = $(BX('ORDER_FORM')).find(`.js-address-field`);

            if ($(addressField).length <= 0) {
                return null;
            }

            return $(addressField);
        }
    },

    // called once, on component load
    init: function (options) {
        var ctx = this;
        this.options = options;

        window.submitFormProxy = BX.proxy(function () {
            ctx.submitFormProxy.apply(ctx, arguments);
        }, this);

        BX(function () {
            ctx.initDeferredControl();
        });
        BX(function () {
            ctx.BXCallAllowed = true; // unlock form refresher
        });

        this.controls.scope = BX('order_form_div');

        // user presses "add location" when he cannot find location in popup mode
        BX.bindDelegate(this.controls.scope, 'click', {className: '-bx-popup-set-mode-add-loc'}, function () {

            var input = BX.create('input', {
                attrs: {
                    type: 'hidden',
                    name: 'PERMANENT_MODE_STEPS',
                    value: '1'
                }
            });

            BX.prepend(input, BX('ORDER_FORM'));

            ctx.BXCallAllowed = false;
            submitForm();
        });
    },

    cleanUp: function () {

        for (var k in this.properties) {
            if (typeof this.properties[k].input != 'undefined') {
                BX.unbindAll(this.properties[k].input);
                this.properties[k].input = null;
            }

            if (typeof this.properties[k].control != 'undefined') {
                BX.unbindAll(this.properties[k].control);
            }
        }

        this.properties = {};
    },

    addPropertyDesc: function (desc) {
        this.properties[desc.id] = desc.attributes;
        this.properties[desc.id].id = desc.id;
    },

    // called each time form refreshes
    initDeferredControl: function () {
        var ctx = this;

        // first, init all controls
        if (typeof window.BX.locationsDeferred != 'undefined') {

            this.BXCallAllowed = false;

            for (var k in window.BX.locationsDeferred) {

                window.BX.locationsDeferred[k].call(this);
                window.BX.locationsDeferred[k] = null;
                delete (window.BX.locationsDeferred[k]);

                this.properties[k].control = window.BX.locationSelectors[k];
                delete (window.BX.locationSelectors[k]);
            }
        }

        for (var k in this.properties) {

            // zip input handling
            if (this.properties[k].isZip) {
                var row = this.controls.scope.querySelector('[data-property-id-row="' + k + '"]');
                if (BX.type.isElementNode(row)) {

                    var input = row.querySelector('input[type="text"]');
                    if (BX.type.isElementNode(input)) {
                        this.properties[k].input = input;

                        // set value for the first "location" property met
                        var locPropId = false;
                        for (var m in this.properties) {
                            if (this.properties[m].type == 'LOCATION') {
                                locPropId = m;
                                break;
                            }
                        }

                        if (locPropId !== false) {
                            BX.bindDebouncedChange(input, function (value) {

                                input = null;
                                row = null;

                                if (/^\s*\d{6}\s*$/.test(value)) {

                                    ctx.getLocationByZip(value, function (locationId) {
                                        ctx.properties[locPropId].control.setValueById(locationId);
                                    }, function () {
                                        try {
                                            ctx.properties[locPropId].control.clearSelected(locationId);
                                        } catch (e) {
                                        }
                                    });
                                }
                            });
                        }
                    }
                }
            }

            if (this.checkAbility(k, 'canHaveAltLocation')) {

                //this.checkMode(k, 'altLocationChoosen');

                var control = this.properties[k].control;

                // control can have "select other location" option
                control.setOption('pseudoValues', ['other']);

                // when control tries to search for items
                control.bindEvent('before-control-item-discover-done', function (knownItems, adapter) {

                    control = null;

                    var parentValue = adapter.getParentValue();

                    // you can choose "other" location only if parentNode is not root and is selectable
                    if (parentValue == this.getOption('rootNodeValue') || !this.checkCanSelectItem(parentValue))
                        return;

                    knownItems.unshift({
                        DISPLAY: ctx.options.messages.otherLocation,
                        VALUE: 'other',
                        CODE: 'other',
                        IS_PARENT: false
                    });
                });

                // currently wont work for initially created controls, so commented out
                /*
                // when control is being created with knownItems
                control.bindEvent('before-control-placed', function(adapter){
                    if(typeof adapter.opts.knownItems != 'undefined')
                        adapter.opts.knownItems.unshift({DISPLAY: so.messages.otherLocation, VALUE: 'other', CODE: 'other', IS_PARENT: false});

                });
                */

                // add special value "other", if there is "city" input
                if (this.checkMode(k, 'altLocationChoosen')) {

                    var altLocProp = this.getAltLocPropByRealLocProp(k);
                    this.toggleProperty(altLocProp.id, true);

                    var adapter = control.getAdapterAtPosition(control.getStackSize() - 1);

                    // also restore "other location" label on the last control
                    if (typeof adapter != 'undefined' && adapter !== null)
                        adapter.setValuePair('other', ctx.options.messages.otherLocation); // a little hack
                } else {

                    var altLocProp = this.getAltLocPropByRealLocProp(k);
                    this.toggleProperty(altLocProp.id, false);

                }
            } else {

                var altLocProp = this.getAltLocPropByRealLocProp(k);
                if (altLocProp !== false) {

                    // replace default boring "nothing found" label for popup with "-bx-popup-set-mode-add-loc" inside
                    if (this.properties[k].type == 'LOCATION' && typeof this.properties[k].control != 'undefined' && this.properties[k].control.getSysCode() == 'sls')
                        this.properties[k].control.replaceTemplate('nothing-found', this.options.messages.notFoundPrompt);

                    this.toggleProperty(altLocProp.id, false);
                }
            }

            if (typeof this.properties[k].control != 'undefined' && this.properties[k].control.getSysCode() == 'slst') {

                var control = this.properties[k].control;

                // if a children of CITY is shown, we must replace label for 'not selected' variant
                var adapter = control.getAdapterAtPosition(control.getStackSize() - 1);
                var node = this.getPreviousAdapterSelectedNode(control, adapter);

                if (node !== false && node.TYPE_ID == ctx.options.cityTypeId) {

                    var selectBox = adapter.getControl();
                    if (selectBox.getValue() == false) {

                        // adapter.getControl().replaceMessage('notSelected', ctx.options.messages.moreInfoLocation);
                        // adapter.setValuePair('', ctx.options.messages.moreInfoLocation);
                    }
                }
            }

        }

        this.BXCallAllowed = true;
    },

    checkMode: function (propId, mode) {

        //if(typeof this.modes[propId] == 'undefined')
        //	this.modes[propId] = {};

        //if(typeof this.modes[propId] != 'undefined' && this.modes[propId][mode])
        //	return true;

        if (mode == 'altLocationChoosen') {

            if (this.checkAbility(propId, 'canHaveAltLocation')) {

                var input = this.getInputByPropId(this.properties[propId].altLocationPropId);
                var altPropId = this.properties[propId].altLocationPropId;

                if (input !== false && input.value.length > 0 && !input.disabled && this.properties[altPropId].valueSource != 'default') {

                    //this.modes[propId][mode] = true;
                    return true;
                }
            }
        }

        return false;
    },

    checkAbility: function (propId, ability) {

        if (typeof this.properties[propId] == 'undefined')
            this.properties[propId] = {};

        if (typeof this.properties[propId].abilities == 'undefined')
            this.properties[propId].abilities = {};

        if (typeof this.properties[propId].abilities != 'undefined' && this.properties[propId].abilities[ability])
            return true;

        if (ability == 'canHaveAltLocation') {

            if (this.properties[propId].type == 'LOCATION') {

                // try to find corresponding alternate location prop
                if (typeof this.properties[propId].altLocationPropId != 'undefined' && typeof this.properties[this.properties[propId].altLocationPropId]) {

                    var altLocPropId = this.properties[propId].altLocationPropId;

                    if (typeof this.properties[propId].control != 'undefined' && this.properties[propId].control.getSysCode() == 'slst') {

                        if (this.getInputByPropId(altLocPropId) !== false) {
                            this.properties[propId].abilities[ability] = true;
                            return true;
                        }
                    }
                }
            }

        }

        return false;
    },

    getInputByPropId: function (propId) {
        if (typeof this.properties[propId].input != 'undefined')
            return this.properties[propId].input;

        var row = this.getRowByPropId(propId);
        if (BX.type.isElementNode(row)) {
            var input = row.querySelector('input[type="text"]');
            if (BX.type.isElementNode(input)) {
                this.properties[propId].input = input;
                return input;
            }
        }

        return false;
    },

    getRowByPropId: function (propId) {

        if (typeof this.properties[propId].row != 'undefined')
            return this.properties[propId].row;

        var row = this.controls.scope.querySelector('[data-property-id-row="' + propId + '"]');
        if (BX.type.isElementNode(row)) {
            this.properties[propId].row = row;
            return row;
        }

        return false;
    },

    getAltLocPropByRealLocProp: function (propId) {
        if (typeof this.properties[propId].altLocationPropId != 'undefined')
            return this.properties[this.properties[propId].altLocationPropId];

        return false;
    },

    toggleProperty: function (propId, way, dontModifyRow) {

        var prop = this.properties[propId];

        if (typeof prop.row == 'undefined')
            prop.row = this.getRowByPropId(propId);

        if (typeof prop.input == 'undefined')
            prop.input = this.getInputByPropId(propId);

        if (!way) {
            if (!dontModifyRow)
                BX.hide(prop.row);
            prop.input.disabled = true;
        } else {
            if (!dontModifyRow)
                BX.show(prop.row);
            prop.input.disabled = false;
        }
    },

    submitFormProxy: function (item, control) {
        var propId = false;
        for (var k in this.properties) {
            if (typeof this.properties[k].control != 'undefined' && this.properties[k].control == control) {
                propId = k;
                break;
            }
        }

        if (item != 'other') {

            if (this.BXCallAllowed) {

                // drop mode "other"
                if (propId != false) {
                    if (this.checkAbility(propId, 'canHaveAltLocation')) {

                        if (typeof this.modes[propId] == 'undefined')
                            this.modes[propId] = {};

                        this.modes[propId]['altLocationChoosen'] = false;

                        var altLocProp = this.getAltLocPropByRealLocProp(propId);
                        if (altLocProp !== false) {

                            this.toggleProperty(altLocProp.id, false);
                        }
                    }
                }

                this.BXCallAllowed = false;
                submitForm();
            }

        } else { // only for sale.location.selector.steps

            if (this.checkAbility(propId, 'canHaveAltLocation')) {

                var adapter = control.getAdapterAtPosition(control.getStackSize() - 2);
                if (adapter !== null) {
                    var value = adapter.getValue();
                    control.setTargetInputValue(value);

                    // set mode "other"
                    if (typeof this.modes[propId] == 'undefined')
                        this.modes[propId] = {};

                    this.modes[propId]['altLocationChoosen'] = true;

                    var altLocProp = this.getAltLocPropByRealLocProp(propId);
                    if (altLocProp !== false) {

                        this.toggleProperty(altLocProp.id, true, true);
                    }

                    this.BXCallAllowed = false;
                    submitForm();
                }
            }
        }
    },

    getPreviousAdapterSelectedNode: function (control, adapter) {

        var index = adapter.getIndex();
        var prevAdapter = control.getAdapterAtPosition(index - 1);

        if (typeof prevAdapter !== 'undefined' && prevAdapter != null) {
            var prevValue = prevAdapter.getControl().getValue();

            if (typeof prevValue != 'undefined') {
                var node = control.getNodeByValue(prevValue);

                if (typeof node != 'undefined')
                    return node;

                return false;
            }
        }

        return false;
    },
    getLocationByZip: function (value, successCallback, notFoundCallback) {
        if (typeof this.indexCache[value] != 'undefined') {
            successCallback.apply(this, [this.indexCache[value]]);
            return;
        }

        window.spinnerCity(true);

        var ctx = this;

        BX.ajax({
            url: this.options.source,
            method: 'post',
            dataType: 'json',
            async: true,
            processData: true,
            emulateOnload: true,
            start: true,
            data: {'ACT': 'GET_LOC_BY_ZIP', 'ZIP': value},
            //cache: true,
            onsuccess: function (result) {

                //try{

                window.spinnerCity(false);
                if (result.result) {

                    ctx.indexCache[value] = result.data.ID;

                    successCallback.apply(ctx, [result.data.ID]);

                } else
                    notFoundCallback.call(ctx);

                //}catch(e){console.dir(e);}

            },
            onfailure: function (type, e) {

                window.spinnerCity(false);
                // on error do nothing
            }

        });
    },
    submitForm: function (val) {
        setTimeout(function () {
            BX.Sale.OrderAjaxComponent.sendRequest()
        }, 20);

        if (this.BXFormPosting === true)
            return true;

        this.BXFormPosting = true;

        if (!IPOLSDEK_pvz?.pvzId) {
            $(".js-form__control[data-prop='ADDRESS_SDEK']").val("")
        }

        $(".checkout-loading-overlay").show();
        var orderForm = BX('ORDER_FORM');

        if (val !== 'Y') {
            BX('confirmorder').value = 'N';
        } else {
            $(".js-form__location.is-required").blur();
            $(".js-form__email").blur();
            $(".js-form__phone").blur();
            $(".js-form__control.is-required:input:visible")
                .not(".js-form__phone")
                .not(".js-form__email")
                .not(".js-form__location")
                .blur();

            this.addressValidate();

            if ($(".is-invalid:not(.is-invalid-soft)").length) {
                const offsetError = $(".is-invalid:not(.is-invalid-soft)").first().offset();

                if (offsetError?.top) {
                    $('html, body').animate({
                        scrollTop: parseInt(offsetError?.top - 100)
                    }, 500);
                }

                this.BXFormPosting = false;
                $(".checkout-loading-overlay").hide();
                return;
            }
        }

        var addressField = BX.saleOrderAjax.address.getInput();
        if ($(addressField).length) {
            $("[name='" + this.address.fieldName + "']").val(addressField.val())
        }

        window.spinnerCity(true);


        if (this.isLocationProEnabled) {
            BX.saleOrderAjax.cleanUp();
        }

        if (val === 'Y') {
            this.submitWithCheckQuantity(orderForm);
        } else {
            BX.ajax.submit(orderForm, BX.delegate(this.ajaxResult, this));
        }

        return true;
    },
    submitWithCheckQuantity: function (orderForm) {
        var component = this;
        var $summaryBlock = $(document).find(".js-summary-block");

        $.ajax({
            method: "POST",
            url: "/ajax/checkBasket.php",
            data: {
                siteId: $summaryBlock.data("site-id")
            },
            dataType: "json",
            success: function (data) {
                if (data.availableBasket) {
                    BX.ajax.submit(orderForm, BX.delegate(component.ajaxResult, component));
                } else {
                    component.deactivateUnavailableProducts(data.unavailableProducts);
                    window.spinnerCity(false);
                    component.BXFormPosting = false;
                    $(".checkout-loading-overlay").hide();
                }
            },
            error: function (e) {
                window.spinnerCity(false);
                component.BXFormPosting = false;
                $(".checkout-loading-overlay").hide();
            }
        });
    },
    deactivateUnavailableProducts: function (productsId) {
        if (!Array.isArray(productsId)) {
            productsId = [];
        }

        productsId.forEach(function (productId) {
            var summaryItem = $(document).find(".js-summary-item[data-product-id='" + productId + "']");
            if (summaryItem.length) {
                summaryItem.find(".js-remove-unavailable").show();
                summaryItem.find(".js-unavailable-error").show();
            }
        });
    },
    ajaxResult: function (res) {
        var orderForm = BX('ORDER_FORM');
        try {
            // if json came, it obviously a successfull order submit

            var json = JSON.parse(res);
            window.spinnerCity(false);

            if (json.error) {
                this.BXFormPosting = false;
                return;
            } else if (json.redirect) {
                window.top.location.href = json.redirect;
            }
        } catch (e) {
            // json parse failed, so it is a simple chunk of html

            this.BXFormPosting = false;
            if (window.hasOwnProperty("$") && $.isReady) {
                var $obContent = $("<div></div>").append($(res));
                $(".js-form_block").html($obContent.find(".js-form_block").html());
                $(".js-basket_block").html($obContent.find(".js-basket_block").html());
                BX.saleOrderAjax.setErrorForCountryField();

                if (this.isLocationProEnabled) {
                    BX.saleOrderAjax.initDeferredControl();
                }

                this.setAddressSuggestions();
                this.addressValidate(true);
            }
        }

        window.spinnerCity(false);
        $(".checkout-loading-overlay").hide();
        $(document).trigger("set_validators");
        BX.onCustomEvent(orderForm, 'onAjaxSuccess');
    },
    setErrorForCountryField: function () {
        if (!$.isReady) {
            return;
        }

        var countryContainer = $(document).find(".js-location_container");
        var countryField = countryContainer.find(".form-control.js-form__control");
        if (countryContainer.length) {
            var errorBlock = countryContainer.find(".invalid-feedback");
            if (!$(countryField).siblings(".invalid-feedback").length) {
                $(countryField).after(errorBlock);
            }
        }
    },
    addressValidate: function (skipEmptyCheck = false) {
        const addressField = this.address.getInput();
        const selectedAddress = this.address.selected?.data;
        const isSng = $('.region-select--russia').hasClass('region-selected');

        if (!isSng || !addressField?.length) {
            return;
        }

        const addressFieldValue = addressField.val()?.trim();

        $(addressField).removeClass('is-invalid');
        $(addressField).removeClass('is-invalid-soft');

        if (!selectedAddress) {
            if (!addressFieldValue) {
                if (!skipEmptyCheck) {
                    $(addressField).siblings('.invalid-feedback').text(this.address.errors.EMPTY);
                    $(addressField).addClass('is-invalid');
                }
            } else {
                const previousValue = this.address.previousValue;

                if (previousValue && addressFieldValue === previousValue?.trim()) {
                    addressField.suggestions().getSuggestions(previousValue)
                        .done(function (suggestions) {
                            if (!suggestions?.length) {
                                addressField.val('');
                            }
                        });
                } else {
                    $(addressField).siblings('.invalid-feedback').text(this.address.errors.NOT_SELECTED);
                    $(addressField).addClass('is-invalid');
                }
            }

        } else {
            if (!selectedAddress?.house) {
                $(addressField).siblings('.invalid-feedback').text(this.address.errors.MISSED_HOUSE);
                $(addressField).addClass('is-invalid');
            } else if (!selectedAddress?.flat) {
                $(addressField).siblings('.invalid-feedback').text(this.address.errors.MISSED_FLAT);
                $(addressField).addClass('is-invalid').addClass('is-invalid-soft');
            }
        }
    },
    setAddressSuggestions: function () {
        const addressField = this.address.getInput();
        const that = this;
        const isSng = $('.region-select--russia').hasClass('region-selected');

        if ($(addressField).length && isSng) {
            $(addressField).on('blur', () => BX.saleOrderAjax.addressValidate())

            $(addressField).suggestions({
                token: "5c06d83d97114db05fb5b63f3d767b6a0b857edf",
                type: "ADDRESS",
                language: BX.Sale.OrderAjaxComponent.siteId === 'en' ? 'en' : 'ru',
                constraints: {
                    locations: [
                        {country_iso_code: "BY"},
                        {country_iso_code: "KZ"},
                        {country_iso_code: "RU"},
                    ]
                },
                onSelect: function (suggestion) {
                    that.address.selected = suggestion;
                    that.addressValidate();

                    if (!that.address.getInput()?.hasClass('is-invalid') || that.address.getInput()?.hasClass('is-invalid-soft')) {
                        BX.saleOrderAjax.submitForm();
                    }
                },
                onSelectNothing: function () {
                    that.address.selected = null;
                    that.addressValidate();
                },
                onSearchStart: function (params) {
                    let city = document.querySelector('.js-form__location').value;

                    if (city && params.query) {
                        params.query = `${city} ${params.query}`;
                    }
                }
            });

            BX.saleOrderAjax.addressValidate(true)
        }
    }
};

$(function () {
    if (window.hasOwnProperty("$") && $.isReady) {
        BX.saleOrderAjax.setErrorForCountryField()
    }
    $(document).on("click", ".js-delivery-link", function (event) {
        var labelId = $(this).data("target-label");
        if (labelId) {
            $(document).find("#" + labelId).click()
        }

        if ($(document).find(".js-delivery-link").not(".collapsed").length <= 0) {
            $(document).find(".js-delivery-input").prop("checked", false)
        }
    })

    $(document).on("click", ".js-date_slot_link", function (event) {
        event.preventDefault();
        var labelId = $(this).data("target-label");
        if (labelId) {
            $(document).find("#" + labelId).click()
        }

        if ($(document).find(".js-delivery-link").not(".collapsed").length <= 0) {
            $(document).find(".js-delivery-input").prop("checked", false)
        }
    })

    $(document).on("click", ".js-time_slot_link", function (event) {
        event.preventDefault();
        var labelId = $(this).data("target-label");
        if (labelId) {
            $(document).find("#" + labelId).click()
        }

        if ($(document).find(".js-delivery-link").not(".collapsed").length <= 0) {
            $(document).find(".js-delivery-input").prop("checked", false)
        }
    })

    $(document).on("click", ".js-pay_system-link", function (event) {
        var labelId = $(this).data("target-label");
        if (labelId) {
            $(document).find("#" + labelId).click()
        }

        if ($(document).find(".js-pay_system-link").not(".collapsed").length <= 0) {
            $(document).find(".js-pay_system-input").prop("checked", false)
        }
    })

    $(document).on("click", ".region-select--button", function (event) {
        $(".region-select--button").removeClass("region-selected");
        var clickedButton = $(event.target);
        clickedButton.addClass("region-selected");
        var value = clickedButton.hasClass("region-select--russia") ? "N" : "Y";
        $("[name='out_russia']").val(value);
        $(this).closest("form").find(".js-form__location__value").val("");
        BX.saleOrderAjax.submitForm();

        return false;
    });

    $(document).on("change", ".bx-ui-combobox-fake.js-form__location", function (event) {
        if ($(this).val().length <= 0) {
            $(this).closest(".js-location_container").find(".js-form__location__value").val("")
        }
    });

    var removeProductLock = false;
    $(document).on("click", ".js-remove-unavailable", function (e) {
        e.preventDefault();
        if (removeProductLock || $(".checkout-loading-overlay").is(":visible")) {
            return;
        }
        var fields = $(BX('ORDER_FORM')).serializeArray();
        var $summaryItem = $(this).closest(".js-summary-item");
        var $summaryBlock = $(this).closest(".js-summary-block");

        for (var i = 0; i < fields.length; i++) {
            if (fields[i].name === "confirmorder" && fields[i].value === "Y") {
                fields[i].value = "N";
            }
        }

        window.spinnerCity(true);
        $(".checkout-loading-overlay").show();
        removeProductLock = true;

        $.ajax({
            method: "POST",
            url: "/ajax/actions.php",
            dataType: "json",
            data: {
                sessid: BX.bitrix_sessid(),
                basketItemId: $summaryItem.data("basket-item-id"),
                siteId: $summaryBlock.data("site-id"),
                action: "removeFromBasket",
            },
            success: function (data) {
                if (data.success !== true) {
                    window.spinnerCity(false);
                    $(".checkout-loading-overlay").hide();
                    removeProductLock = false;
                    return;
                }

                $.ajax({
                    method: "POST",
                    data: fields,
                    success: function (data) {
                        try {
                            var parseData = JSON.parse(data);
                            if (parseData.redirect) {
                                document.location.href = parseData.redirect;
                            }
                            return;
                        } catch (e) {
                        }

                        if ($(data).find(".js-summary-block .js-summary-item").length > 0) {
                            $(document).find(".js-summary-block").html($(data).find(".js-summary-block").html());
                        }

                        window.spinnerCity(false);
                        $(".checkout-loading-overlay").hide();
                        removeProductLock = false;
                    },
                    error: function () {
                        window.spinnerCity(false);
                        $(".checkout-loading-overlay").hide();
                        removeProductLock = false;
                    }
                });
            },
            error: function () {
                window.spinnerCity(false);
                $(".checkout-loading-overlay").hide();
                removeProductLock = false;
            }
        });
    })


    BX.saleOrderAjax.setAddressSuggestions();

    $(document).on('change', '[name="subscribe"]', function () {
        $('[name="subscribe"]').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '[name="policy"]', function () {
        $('[name="policy"]').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '[name="personal"]', function () {
        $('[name="personal"]').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '[name="policy"]', function () {
        const submitButton = $(this).closest('.js-submit-block').find('button[type="submit"]')
        if (($(this).prop('checked')) && ($('[name="personal"]').prop('checked'))) {
            submitButton.prop('disabled', false)
        } else {
            submitButton.prop('disabled', true)
        }
    });

    $(document).on('change', '[name="personal"]', function () {
        const submitButton = $(this).closest('.js-submit-block').find('button[type="submit"]')
        if (($(this).prop('checked')) && ($('[name="policy"]').prop('checked'))) {
            submitButton.prop('disabled', false)
        } else {
            submitButton.prop('disabled', true)
        }
    });
});
