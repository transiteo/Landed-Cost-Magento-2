require(["jquery", "Magento_Ui/js/modal/modal"], function ($, modal) {
  let getcookie = $('#getCookie').val();
  let cookie = getcookie ? false : true
  
  var options = {
    type: "popup",
    title: "MyShop",
    responsive: true,
    innerScroll: true,
    clickableOverlay: false,
    autoOpen: false,
    buttons: [
      {
        text: $.mage.__("Submit"),
        class: "button_submit",
        click: function (data) {
          var $form = $("#form-validate");
          let form_data = jQuery("#form-validate").serialize();
          let url = $("#getUrl").val();

          var thisPopup = this;

          if (!$form.valid()) return false;
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
      $('.modal-header button.action-close', $Event.srcElement).hide();
      console.log("Visitor Country = "+$('#visitor_country').val());
    },
    keyEventHandlers: {
      escapeKey: function () {
        return;
      },
    },
  };

  $(document).ready(function(){
    var popup = modal(options, $("#popup-modal"));
    //$("#popup-modal").modal("openModal");
    
    $(document).on("click", "#click-me", function () {
        var popup = modal(options, $("#popup-modal"));
        $("#popup-modal").modal("openModal");
    });

   jQuery.ajax({
      url: 'http://localhost/ati5/transiteo/test/view',
      type: 'GET',
      success: function (data) {

        if(!data['same_country']){
          var popup = modal(options, $("#popup-modal"));
          $("#popup-modal").modal("openModal");
        }

      },
      error: function (result) {
        console.log('no response !');
      }
    });

    //console.log(this.geoip_country);


  });

  
});
