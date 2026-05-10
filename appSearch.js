$(document).ready(function () {
  var $input = $("#searchInput");
  var $dropdown = $("#searchDropdown");
  var delay;

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
    } else if (e.key === "Enter" && $dropdown.find(".kbd-active").length) {
      e.preventDefault();
      window.location.href = $dropdown.find(".kbd-active").attr("href");
    } else if (e.key === "Escape") $dropdown.hide();
  });
});
