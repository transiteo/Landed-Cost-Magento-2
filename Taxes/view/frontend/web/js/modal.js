require(["jquery", "Magento_Ui/js/modal/modal"], function ($, modal) {
  let getcookie = $('#getCookie').val();
  let cookie = getcookie ? false : true
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
        class: "",
        click: function (data) {
          let form_data = jQuery("#form-validate").serialize();
          let url = $("#getUrl").val();
          jQuery.ajax({
            url: url,
            type: 'POST',
            data: form_data,
            success: function (data) {
              // console.log(data);
              $("#popup-modal").modal("closeModal");
            },
            error: function (result) {
              console.log('no response !');
            }
          });
        },
      },
    ],
    opened: function ($Event) {
      $('.modal-header button.action-close', $Event.srcElement).hide();
    },
    keyEventHandlers: {
      escapeKey: function () { return; }
    }
  };

  var popup = modal(options, $("#popup-modal"));
  $("#click-me").on("click", function () {
    $("#popup-modal").modal("openModal");
  });
});
