(function ($) {
  var frame

  $("#wr-logo-pick").on("click", function (event) {
    event.preventDefault()
    if (frame) {
      frame.open()
      return
    }
    frame = wp.media({
      title: "Choose logo",
      button: { text: "Use this logo" },
      multiple: false,
    })
    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON()
      $("#wr-logo-id").val(attachment.id)
      $("#wr-logo-preview").attr("src", attachment.url).removeAttr("hidden")
    })
    frame.open()
  })
})(jQuery)
