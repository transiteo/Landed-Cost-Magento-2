/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

require([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "Magento_Customer/js/customer-data",
    'mage/translate'
], ($, modal, customerData, $t) => {
    let isOpened = false;
    // reload of geoip_country section
    const sections = ["geoip_country"];
    try {
        customerData.initStorage();
    } catch (e) {
        console.log("Version of magento is not 2.4.1");
    }
    customerData.invalidate(sections);
    customerData.reload(sections, true);

    document.getElementById("country").removeAttribute('id');

    const url_states = $(".getUrlStates").val();

    const options = {
        id: 'Transiteo_LandedCost_modal',
        type: "popup",
        title: "",
        class: "transiteo-modal",
        responsive: true,
        innerScroll: true,
        clickableOverlay: false,
        autoOpen: false,
        buttons: [
            {
                text: $.mage.__("Submit"),
                class: "transiteo_modal_button button_submit",
                click(data) {
                    $(".modal-content .country_error").hide();
                    $(".modal-content .region_error").hide();
                    $(".modal-content .currency_error").hide();
                    if (!$("select[name='country_id']").val()) {
                        const span_country = $(".country_error")
                            .html($t("please select country"))
                            .show();

                        return false;
                    }
                    if (!$(".modal-content .state").val()) {
                        const span_region = $(".region_error")
                            .html($t("please select region"))
                            .show();
                        return false;
                    }
                    if (!$(".modal-content .currency-select").val()) {
                        const span_currency = $(".currency_error")
                            .html($t("please select currency"))
                            .show();
                        return false;
                    }
                     //var $form = $("transiteo-form-validate");
                    const form_data = jQuery(".modal-content .transiteo-form-validate").serialize();
                    const url = $(".getUrl").val();

                    const thisPopup = this;

                    //save cookie
                    //if (!$form.valid()) return false;
                    jQuery.ajax({
                        url,
                        type: "POST",
                        data: form_data,
                        success(data) {
                            thisPopup.closeModal();
                            changeCurrency();
                        },
                        error(result) {
                            console.log("no response !");
                        },
                    });


                },
            },
        ],
        opened($Event) {
            $(".modal-header button.action-close", $Event.target).hide();
            isOpened = true;
            $(".modal-content select[name='country_id']").on("change",function () {
                reloadDistricts(function (data) {
                    const state = $(".modal-content .state")
                    state[0].disabled = true;
                    const documentFragment = document.createDocumentFragment();
                    for (let item of data.items) {
                        const option = document.createElement('option');
                        option.value = item.iso;
                        option.text = item.iso + ' ' + item.label;
                        documentFragment.appendChild(option);
                    }
                    state[0].appendChild(documentFragment);
                    state[0].disabled = false;
                });
            });
        },
        closed($Event) {
            isOpened = false;
        },
        keyEventHandlers: {
            escapeKey() {
            },
        },
    };
    const popup = $("#transiteo-modal");
    modal(options, popup);


    $(document).ready(() => {
        // get visitor country based on geoip
        customerData.get("geoip_country").subscribe((value) => {
            if (!value.same_country_as_website && !cookieExists() && !isOpened) {
                const visitorCountry = value.visitor_country;
                $("select[name='country_id']").val(visitorCountry);
            }
        });

        //check if cookie exists and get value
        if (cookieExists()) {
            let tabCountry = getCookie().split("_");
            console.log("cookie exist, country = " + tabCountry[0]);
            $("select[name='country_id']").val(tabCountry[0]);
            reloadDistricts(function (data) {
                const state = $(".modal-content .state")
                state[0].disabled = true;
                const documentFragment = document.createDocumentFragment();
                for (let item of data.items) {
                    const option = document.createElement('option');
                    option.value = item.iso;
                    option.text = item.iso + ' ' + item.label;
                    documentFragment.appendChild(option);
                }
                state[0].appendChild(documentFragment);
                state[0].disabled = false;
                $(".modal-content .state").val(tabCountry[1]);
            });
            $(".modal-content .currency-select").val(tabCountry[2]);

        }else{
            popup.modal('openModal');
            console.log("No Cookie : Opening Modal")
            reloadDistricts(function (data) {
                const state = $(".modal-content .state")
                state[0].disabled = true;
                const documentFragment = document.createDocumentFragment();
                for (let item of data.items) {
                    const option = document.createElement('option');
                    option.value = item.iso;
                    option.text = item.iso + ' ' + item.label;
                    documentFragment.appendChild(option);
                }
                state[0].appendChild(documentFragment);
                state[0].disabled = false;
            });
        }
    });

    $("#transiteo_modal_button_show").on("click", function () {
        popup.modal('openModal');
    });

    /**
     * Get Cookie
     * @returns {string|null}
     */
    function getCookie() {
        return readCookie('transiteo-popup-info');
    }

    /**
     * Read Cookie
     * @param name
     * @returns {string|null}
     */
    function readCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function cookieExists() {
        return !!(getCookie() != "" && getCookie() != null);
    }

    function changeCurrency() {
        //change currency
        var currency = $(".modal-content .currency-select").val();
        currency = currency.replace(';', '');
        const currencyUrl = $(".modal-content .getCurrencyUrl").val();
        var param = "currency=" + currency;
        const url = currencyUrl + '?' + param;
        console.log(url);
        window.location.href = url;
    }

    function reloadDistricts(callback) {
        // clear of states dropdown
        $(".modal-content .state").text("");
        $(".modal-content .state").html('<option value="">Choose your state...</option>');

        // fetch states with country id
        var param = "country=" + $(".modal-content select[name='country_id']").val();
        $.ajax({
            url: url_states,
            data: param,
            type: "POST",
            dataType: "json",
            success(data) {
                callback(data);
            },
            error(data) {
                console.log("no response !");
            }
        });
    }


});
