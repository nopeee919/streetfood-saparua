$(document).ready(function () {
  // Lightbox nav
  $("#lbNext").on("click", function () {
    lbIndex = (lbIndex + 1) % boothPhotos.length;
    updateLightbox();
  });
  $("#lbPrev").on("click", function () {
    lbIndex = (lbIndex - 1 + boothPhotos.length) % boothPhotos.length;
    updateLightbox();
  });
  $(document).on("keydown", function (e) {
    if ($("#lightbox").is(":visible")) {
      if (e.key === "ArrowRight") $("#lbNext").click();
      if (e.key === "ArrowLeft") $("#lbPrev").click();
      if (e.key === "Escape") closeLightbox();
    }
  });

  var $input = $("#searchInput");
  var $dropdown = $("#searchDropdown");
  var searchDelay;

  $input.on("input", function () {
    clearTimeout(searchDelay);
    var q = $(this).val().trim();
    if (q.length < 2) {
      $dropdown.hide().empty();
      return;
    }

    searchDelay = setTimeout(function () {
      $.ajax({
        url: "search_suggest.php",
        type: "POST",
        data: { q: q },
        dataType: "json",
        success: function (data) {
          $dropdown.empty();
          if (!data.length) {
            $dropdown.hide();
            return;
          }

          data.forEach(function (item) {
            var icon = item.type === "umkm" ? "🏪" : "🍴";
            $dropdown.append(
              $("<a>")
                .addClass("suggest-item suggest-" + item.type)
                .attr("href", item.url)
                .html(
                  '<span class="suggest-icon">' +
                    icon +
                    "</span>" +
                    '<span class="suggest-text">' +
                    '<span class="suggest-label">' +
                    $("<span>").text(item.label).html() +
                    "</span>" +
                    '<span class="suggest-sub">' +
                    $("<span>").text(item.sub).html() +
                    "</span>" +
                    "</span>" +
                    '<span class="suggest-badge suggest-badge-' +
                    item.type +
                    '">' +
                    (item.type === "umkm" ? "UMKM" : "Menu") +
                    "</span>",
                ),
            );
          });
          $dropdown.show();
        },
      });
    }, 280);
  });

  $(document).on("click", function (e) {
    if (!$(e.target).closest(".search-wrapper").length) $dropdown.hide();
  });

  $input.on("keydown", function (e) {
    var $items = $dropdown.find(".suggest-item"),
      $active = $dropdown.find(".suggest-item.kbd-active");
    if (e.key === "ArrowDown") {
      e.preventDefault();
      if (!$active.length) $items.first().addClass("kbd-active");
      else {
        $active.removeClass("kbd-active");
        ($active.next(".suggest-item").length
          ? $active.next(".suggest-item")
          : $items.first()
        ).addClass("kbd-active");
      }
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if (!$active.length) $items.last().addClass("kbd-active");
      else {
        $active.removeClass("kbd-active");
        ($active.prev(".suggest-item").length
          ? $active.prev(".suggest-item")
          : $items.last()
        ).addClass("kbd-active");
      }
    } else if (e.key === "Enter" && $active.length) {
      e.preventDefault();
      window.location.href = $active.attr("href");
    } else if (e.key === "Escape") $dropdown.hide();
  });
});
