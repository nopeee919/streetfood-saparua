$(document).ready(function () {
  var $inputSearch = $("#searchInput");
  var $dropdownSuggest = $("#searchDropdown");
  var delaySearch;
  function buatItemSuggestion(item) {
    var icon;
    if (item.type === "umkm") {
      icon = "🏪";
    } else {
      icon = "🍴";
    }
    var $item = $("<a>");
    $item.addClass("suggest-item");
    $item.addClass("suggest-" + item.type);
    $item.attr("href", item.url);
    $item.html(
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
    );
    return $item;
  }
  if ($inputSearch.length) {
    $inputSearch.on("input", function () {
      clearTimeout(delaySearch);
      var keyword = $(this).val().trim();
      if (keyword.length < 2) {
        $dropdownSuggest.hide();
        $dropdownSuggest.empty();
        return;
      }
      delaySearch = setTimeout(function () {
        $.ajax({
          url: "search_suggest.php",
          type: "POST",
          data: {
            q: keyword,
          },
          dataType: "json",
          success: function (data) {
            $dropdownSuggest.empty();
            if (!data.length) {
              $dropdownSuggest.hide();
              return;
            }
            data.forEach(function (item) {
              $dropdownSuggest.append(buatItemSuggestion(item));
            });
            $dropdownSuggest.show();
          },
        });
      }, 280);
    });
    $(document).on("click", function (e) {
      var klikDiSearch = $(e.target).closest(".search-wrapper").length;
      if (!klikDiSearch) {
        $dropdownSuggest.hide();
      }
    });
    $inputSearch.on("keydown", function (e) {
      var $items = $dropdownSuggest.find(".suggest-item");
      var $aktif = $items.filter(".kbd-active");
      if (e.key === "ArrowDown") {
        e.preventDefault();
        $aktif.removeClass("kbd-active");
        if ($aktif.length && $aktif.next(".suggest-item").length) {
          $aktif.next(".suggest-item").addClass("kbd-active");
        } else {
          $items.first().addClass("kbd-active");
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        $aktif.removeClass("kbd-active");
        if ($aktif.length && $aktif.prev(".suggest-item").length) {
          $aktif.prev(".suggest-item").addClass("kbd-active");
        } else {
          $items.last().addClass("kbd-active");
        }
      } else if (e.key === "Enter") {
        var $pilihan = $items.filter(".kbd-active");
        if ($pilihan.length) {
          e.preventDefault();
          window.location.href = $pilihan.attr("href");
        }
      } else if (e.key === "Escape") {
        $dropdownSuggest.hide();
      }
    });
  }

  if ($("#lightbox").length) {
    $("#lbNext").on("click", function () {
      lbIndex = (lbIndex + 1) % boothPhotos.length;
      updateLightbox();
    });
    $("#lbPrev").on("click", function () {
      lbIndex = (lbIndex - 1 + boothPhotos.length) % boothPhotos.length;
      updateLightbox();
    });
    $(document).on("keydown", function (e) {
      if (!$("#lightbox").is(":visible")) {
        return;
      }
      if (e.key === "ArrowRight") {
        $("#lbNext").click();
      }
      if (e.key === "ArrowLeft") {
        $("#lbPrev").click();
      }
      if (e.key === "Escape") {
        closeLightbox();
      }
    });
  }
});
