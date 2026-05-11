var boothPhotos = [];
var lbIndex = 0;

$(".booth-thumb").each(function () {
  boothPhotos.push({
    url_foto: $(this).find("img").attr("src"),
    keterangan: $(this).find(".booth-thumb-caption").text(),
  });
});

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

  if (!p) return;

  $("#lbImg").attr("src", p.url_foto);
  $("#lbCaption").text(p.keterangan || "");
  $("#lbCurrent").text(lbIndex + 1);
}

$("#lbPrev").on("click", function () {
  lbIndex--;

  if (lbIndex < 0) {
    lbIndex = boothPhotos.length - 1;
  }

  updateLightbox();
});

$("#lbNext").on("click", function () {
  lbIndex++;

  if (lbIndex >= boothPhotos.length) {
    lbIndex = 0;
  }

  updateLightbox();
});
