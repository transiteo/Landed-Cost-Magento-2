require([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data'
], function ($, modal,customerData ) {

  'use strict';

  var isOpened = false;


    // reload of geoip_country section
    var sections = ['geoip_country'];
    customerData.invalidate(sections);
    customerData.reload(sections, true);

    let getcookie = $('#getCookie').val()
    console.log("cookieval = "+getcookie);

    let cookieExist = (getcookie != "" && getcookie != null) ? true : false

    var options = {
        type: 'popup',
        title: '',
        responsive: true,
        innerScroll: true,
        clickableOverlay: false,
        autoOpen: false,
        buttons: [
            {
                text: $.mage.__('Submit'),
                class: 'button_submit',
                click: function (data) {
                    var $form = $('#form-validate')
                    let form_data = jQuery('#form-validate').serialize()
                    let url = $('#getUrl').val()

                    var thisPopup = this

                    if (!$form.valid()) return false
                    jQuery.ajax({
                        url: url,
                        type: 'POST',
                        data: form_data,
                        success: function (data) {
                            // console.log(data);
                            thisPopup.closeModal()
                            console.log(data)
                        },
                        error: function (result) {
                            console.log('no response !')
                        },
                    })
                },
            },
        ],
        opened: function ($Event) {
            $('.modal-header button.action-close', $Event.srcElement).hide()
            isOpened = true;

        },
        closed: function ($Event) {
          isOpened = false;
        },
        keyEventHandlers: {
            escapeKey: function () {
                return
            },
        },
    }

    $(document).ready(function () {


        //get visitor country based on geoip
        customerData.get('geoip_country').subscribe(function(value) {

          console.log("same country = "+value.same_country_as_website);
          console.log("cookie exist = "+ cookieExist);
          console.log("isOpened = "+isOpened);

          if(!value.same_country_as_website && !cookieExist && !isOpened){
            var popup = modal(options, $('#transiteo-modal'));
            var visitorCountry = value.visitor_country;
            $("#country").val(visitorCountry);

            $("#transiteo-modal").modal("openModal");

          }
        });

        $(document).on('click', '#click-me', function () {
            var popup = modal(options, $('#transiteo-modal'))
            $('#transiteo-modal').modal('openModal')
        })

        /*jQuery.ajax({
            url: 'transiteo/test/view',
            type: 'GET',
            success: function (data) {

                if (!data['same_country'] && !cookieExist) {
                    var popup = modal(options, $('#transiteo-modal'))
                    $('#transiteo-modal').modal('openModal')
                }

            },
            error: function (result) {
                console.log('Error catching visitor ip adress in ajax')
            }
        })*/

      })

})
