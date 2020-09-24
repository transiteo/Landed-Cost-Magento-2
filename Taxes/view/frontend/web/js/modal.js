require(["jquery", "Magento_Ui/js/modal/modal"], function ($, modal) {
  let getcookie = $("#getCookie").val();
  let cookie = getcookie ? false : true;
  var options = {
    type: "popup",
    title: "MyShop",
    responsive: true,
    innerScroll: true,
    clickableOverlay: false,
    autoOpen: cookie,
    buttons: [
      {
        text: $.mage.__("Submit"),
        class: "button_submit",
        click: function (data) {
          var $form = $("#form-validate");
          let form_data = jQuery("#form-validate").serialize();
          let url = $("#getUrl").val();
          if (!$form.valid()) return false;
          jQuery.ajax({
            url: url,
            type: "POST",
            data: form_data,
            success: function (data) {
              console.log(data);
              $("#popup-modal").modal("closeModal");
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
    },
    keyEventHandlers: {
      escapeKey: function () {
        return;
      },
    },
  };

  var popup = modal(options, $("#popup-modal"));
  $("#click-me").on("click", function () {
    $("#popup-modal").modal("openModal");
  });
});
