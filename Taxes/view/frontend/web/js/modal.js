require([
  "jquery",
  "Magento_Ui/js/modal/modal",
  "Magento_Customer/js/customer-data",
], ($, modal, customerData) => {
  let isOpened = false;
  // reload of geoip_country section
  const sections = ["geoip_country"];
  customerData.invalidate(sections);
  customerData.reload(sections, true);

  const getcookie = $("#getCookie").val();
  console.log('cookieval = '+getcookie);

  const cookieExist = !!(getcookie != "" && getcookie != null);

  const cookie = !getcookie;
  document.getElementById("country").options[0].text = "Choose your country...";
  document.getElementById("state").options[0].text = "Choose your state...";

  const url_states = $("#getUrlStates").val();


  const options = {
    type: "popup",
    title: "",
    responsive: true,
    innerScroll: true,
    clickableOverlay: false,
    autoOpen: false,
    buttons: [
      {
        text: $.mage.__("Submit"),
        class: "button_submit",
        click(data) {

          console.log("test");

          $("#country_error").hide();
          $("#region_error").hide();
          $("#currency_error").hide();
          if (!$("#country").val()) {
            const span_country = $("#country_error")
              .html("please select country")
              .show();
            console.log(span_country);
            return false;
          }
          if (!$("#state").val()) {
            const span_region = $("#region_error")
              .html("please select region")
              .show();
            return false;
          }
          if (!$("#currency-select").val()) {
            const span_currency = $("#currency_error")
              .html("please select currency")
              .show();
            return false;
          }
          // var $form = $("transiteo-form-validate");
          const form_data = jQuery("#transiteo-form-validate").serialize();
          const url = $("#getUrl").val();

          const thisPopup = this;
          // if (!$form.valid()) return false;
          jQuery.ajax({
            url,
            type: "POST",
            data: form_data,
            success(data) {
              // console.log(data);
              thisPopup.closeModal();
              console.log(data);
            },
            error(result) {
              console.log("no response !");
            },
          });
        },
      },
    ],
    opened($Event) {
      $(".modal-header button.action-close", $Event.srcElement).hide();
      isOpened = true;
    },
    closed($Event) {
      isOpened = false;
    },
    keyEventHandlers: {
      escapeKey() {},
    },
  };

  $(document).ready(() => {
    // get visitor country based on geoip
    customerData.get("geoip_country").subscribe((value) => {

      console.log(`same country = ${value.same_country_as_website}`);
      console.log(`cookie exist = ${cookieExist}`);
      console.log(`isOpened = ${isOpened}`);

      if (!value.same_country_as_website && !cookieExist && !isOpened) {
        const popup = modal(options, $("#transiteo-modal"));
        const visitorCountry = value.visitor_country;
        $("#country").val(visitorCountry);

        reloadDistricts(function(data){
          $.each(data.items, function(index, value) {           
            $("#state").append('<option value="'+value.iso+'">'+value.label+'</option>');

          });
          
          $("#transiteo-modal").modal("openModal");
        });

      }
    });
    
    $(document).on("click", "#click-me", () => {
      const popup = modal(options, $("#transiteo-modal"));
      $("#transiteo-modal").modal("openModal");
    });

    if(cookieExist){
      var tabCountry = getcookie.split("_");
      console.log("cookie exist, country = "+tabCountry[0]);
      $("#country").val(tabCountry[0]);
      
      reloadDistricts(function(data){
        $.each(data.items, function(index, value) {           
          $("#state").append('<option value="'+value.iso+'">'+value.label+'</option>');

        });
        
        $("#country").val(tabCountry[0]);
        $("#currency-select").val(tabCountry[2]);
        
        $("#state").val(tabCountry[1]);
      });
    }


    /* jQuery.ajax({
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
                }) */
    
    function reloadDistricts(callback){  
     
      // clear of states dropdown
      $("#state")
      .find('option')
      .remove()
      .end()
      .append('<option value="">Choose your state...</option>');

      // fetch states with country id
      var param = "country=" + $("#country").val();
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

    $(document).on("change", "#country", function () {
      reloadDistricts(function(data){
        $.each(data.items, function(index, value) {           
          $("#state").append('<option value="'+value.iso+'">'+value.label+'</option>');
        });
      });
    });
  });
});
