$(document).ready(function () {
  var $input = $("#searchInput");
  var $dropdown = $("#searchDropdown");
  var delay;

  // Live suggestion saat mengetik
  $input.on("input", function () {
    clearTimeout(delay);
    var q = $(this).val().trim();

    if (q.length < 2) {
      $dropdown.hide().empty();
      return;
    }

    delay = setTimeout(function () {
      $.ajax({
        url: "search_suggest.php",
        type: "POST",
        data: { q: q },
        dataType: "json",
        success: function (data) {
          $dropdown.empty();
          if (data.length === 0) {
            $dropdown.hide();
            return;
          }
          data.forEach(function (item) {
            var icon = item.type === "umkm" ? "🏪" : "🍴";
            var $item = $("<a>")
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
              );
            $dropdown.append($item);
          });
          $dropdown.show();
        },
      });
    }, 280);
  });

  // Klik di luar = tutup dropdown
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".search-wrapper").length) {
      $dropdown.hide();
    }
  });

  // Navigasi keyboard (panah atas/bawah + enter)
  $input.on("keydown", function (e) {
    var $items = $dropdown.find(".suggest-item");
    var $active = $dropdown.find(".suggest-item.kbd-active");
    if (e.key === "ArrowDown") {
      e.preventDefault();
      if ($active.length === 0) {
        $items.first().addClass("kbd-active");
      } else {
        $active.removeClass("kbd-active");
        var $next = $active.next(".suggest-item");
        ($next.length ? $next : $items.first()).addClass("kbd-active");
      }
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if ($active.length === 0) {
        $items.last().addClass("kbd-active");
      } else {
        $active.removeClass("kbd-active");
        var $prev = $active.prev(".suggest-item");
        ($prev.length ? $prev : $items.last()).addClass("kbd-active");
      }
    } else if (e.key === "Enter") {
      var $kbdActive = $dropdown.find(".suggest-item.kbd-active");
      if ($kbdActive.length) {
        e.preventDefault();
        window.location.href = $kbdActive.attr("href");
      }
    } else if (e.key === "Escape") {
      $dropdown.hide();
    }
  });
});
