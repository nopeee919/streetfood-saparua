var boothPhotos = window.boothPhotos || [];
var lbIndex = 0;

function openLightbox(i) {
  lbIndex = i;
  updateLightbox();

  $("#lightbox").fadeIn(180);
  $("body").css("overflow", "hidden");
}

function closeLightbox() {
  $("#lightbox").fadeOut(150);
  $("body").css("overflow", "");
}

function updateLightbox() {
  var p = boothPhotos[lbIndex];

  $("#lbImg").attr("src", p.url_foto);
  $("#lbCaption").text(p.keterangan);
  $("#lbCurrent").text(lbIndex + 1);
}
