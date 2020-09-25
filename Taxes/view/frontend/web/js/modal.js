require([
  "jquery",
  "Magento_Ui/js/modal/modal",
  "Magento_Customer/js/customer-data",
], function ($, modal, customerData) {
  let getcookie = $("#getCookie").val();
  let cookie = getcookie ? false : true;
  document.getElementById("country").options[0].text = "Choose your country...";
  document.getElementById("state").options[0].text = "Choose your states...";

  var options = {
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
        click: function (data) {
          $("#country_error").hide();
          $("#region_error").hide();
          $("#currency_error").hide();
          if (!$("#country").val()) {
            let span_country = $("#country_error")
              .html("please select country")
              .show();
            console.log(span_country);
            return false;
          } else if (!$("#state").val()) {
            let span_region = $("#region_error")
              .html("please select region")
              .show();
            return false;
          } else if (!$("#currency-select").val()) {
            let span_currency = $("#currency_error")
              .html("please select currency")
              .show();
            return false;
          }
          // var $form = $("transiteo-form-validate");
          let form_data = jQuery("#transiteo-form-validate").serialize();
          let url = $("#getUrl").val();

          var thisPopup = this;
          // if (!$form.valid()) return false;
          jQuery.ajax({
            url: url,
            type: "POST",
            data: form_data,
            success: function (data) {
              // console.log(data);
              thisPopup.closeModal();
              console.log(data);
            },
            error: function (result) {
              console.log("no response !");
            },
          });
        },
      },
    ],
    opened: function ($Event) {
      $(".modal-header button.action-close", $Event.srcElement).hide();
      console.log("Visitor Country = " + $("#visitor_country").val());
    },
    keyEventHandlers: {
      escapeKey: function () {
        return;
      },
    },
  };

  $(document).ready(function () {
    var popup = modal(options, $("#transiteo-modal"));
    //$("#transiteo-modal").modal("openModal");

    $(document).on("click", "#click-me", function () {
      var popup = modal(options, $("#transiteo-modal"));
      $("#transiteo-modal").modal("openModal");
    });

    jQuery.ajax({
      url: "/transiteo/test/view",
      type: "GET",
      success: function (data) {
        if (!data["same_country"]) {
          var popup = modal(options, $("#transiteo-modal"));
          $("#transiteo-modal").modal("openModal");
        }
      },
      error: function (result) {
        console.log("no response !");
      },
    });

    console.log(customerData.get("geoip_country"));
  });
});
