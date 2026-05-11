var pageReady = false;

/** China provincial-level regions (English), for province filter dropdown */
var CHINA_PROVINCES = [
  "Beijing",
  "Tianjin",
  "Shanghai",
  "Chongqing",
  "Hebei",
  "Shanxi",
  "Liaoning",
  "Jilin",
  "Heilongjiang",
  "Jiangsu",
  "Zhejiang",
  "Anhui",
  "Fujian",
  "Jiangxi",
  "Shandong",
  "Henan",
  "Hubei",
  "Hunan",
  "Guangdong",
  "Hainan",
  "Sichuan",
  "Guizhou",
  "Yunnan",
  "Shaanxi",
  "Gansu",
  "Qinghai",
  "Taiwan",
  "Inner Mongolia",
  "Guangxi",
  "Tibet",
  "Ningxia",
  "Xinjiang",
  "Hong Kong",
  "Macao",
];

function fillChinaProvinceSelect(idPrefix) {
  var selId = idPrefix + "province";
  var rows = [{ v: "", t: "Any" }];
  var i;
  for (i = 0; i < CHINA_PROVINCES.length; i++) {
    var name = CHINA_PROVINCES[i];
    rows.push({ v: name, t: name });
  }
  fillSelect(selId, rows);
}

function parseRoute() {
  var h = window.location.hash;
  if (h == "" || h == "#" || h == "#/" || h == "#/") {
    return { name: "discovery", q: "" };
  }
  if (h.indexOf("search") != -1) {
    var q = "";
    var pos = h.indexOf("q=");
    if (pos != -1) {
      q = decodeURIComponent(h.substring(pos + 2).split("&")[0]);
    }
    return { name: "results", q: q };
  }
  return { name: "discovery", q: "" };
}

function setTitle(name) {
  if (name == "results") {
    document.title = "Search Results · Used Cars";
  } else {
    document.title = "Used Car Search · Discover";
  }
}

function showView(name) {
  document.getElementById("view-discovery").hidden = name != "discovery";
  document.getElementById("view-results").hidden = name != "results";
  setTitle(name);
}

function hotPick(rowIndex, tagIndex) {
  var row = window.AppData.hotRows[rowIndex];
  var word = row.tags[tagIndex];
  var form = document.getElementById("discovery-search-form");
  var input = document.getElementById("search-input");
  if (form && input) {
    form.reset();
    input.value = word;
    form.submit();
  }
}
function renderHotTags() {
  var wrap = document.getElementById("hot-tag-rows");
  if (!wrap || !window.AppData) {
    return;
  }
  var html = "";
  var r;
  for (r = 0; r < AppData.hotRows.length; r++) {
    var row = AppData.hotRows[r];
    html += "<div class='section hot-row'>";
    html += "<div class='rank-meta tag-row-title'>" + row.label + "</div>";
    html += "<div class='hot-tags'>";
    var t;
    for (t = 0; t < row.tags.length; t++) {
      html +=
        "<button type='button' class='tag' onclick='hotPick(" +
        r +
        "," +
        t +
        ")'>" +
        row.tags[t] +
        "</button>";
    }
    html += "</div></div>";
  }
  wrap.innerHTML = html;
}

function renderRankList(listEl) {
  if (!listEl || !window.AppData) {
    return;
  }
  var n = 5;
  if (AppData.rankingPlaceholderRows > 0) {
    n = AppData.rankingPlaceholderRows;
  }
  listEl.innerHTML = "";
  var i;
  for (i = 0; i < n; i++) {
    var rank = i + 1;
    var numClass = "rank-num";
    if (rank <= 3) {
      numClass += " top";
    }
    listEl.innerHTML +=
      "<li class='rank-item rank-item-placeholder'>" +
      "<span class='" +
      numClass +
      "'>" +
      rank +
      "</span>" +
      "<div class='rank-thumb rank-thumb-placeholder'></div>" +
      "<div class='rank-body'>" +
      "<div class='rank-name rank-name-placeholder'>—</div>" +
      "<div class='rank-meta rank-meta-placeholder'>—</div>" +
      "</div></li>";
  }
}

function getAllListings() {
  if (!window.AppData || !AppData.listings) {
    return [];
  }
  return AppData.listings;
}

function escapeHtml(s) {
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function fillSelect(selectId, rows) {
  var sel = document.getElementById(selectId);
  if (!sel) {
    return;
  }
  var html = "";
  var i;
  for (i = 0; i < rows.length; i++) {
    var r = rows[i];
    html += "<option value=\"" + escapeHtml(r.v) + "\">" + escapeHtml(r.t) + "</option>";
  }
  sel.innerHTML = html;
}

function uniqueSortedStrings(values) {
  var seen = {};
  var out = [];
  var i;
  for (i = 0; i < values.length; i++) {
    var v = values[i];
    if (!seen[v]) {
      seen[v] = true;
      out.push(v);
    }
  }
  out.sort();
  return out;
}

function populateFilterFormOnce(formId, idPrefix) {
  var form = document.getElementById(formId);
  if (!form || form.getAttribute("data-populated") === "1") {
    return;
  }
  fillChinaProvinceSelect(idPrefix);
  var list = getAllListings();
  if (!list.length) {
    form.setAttribute("data-populated", "1");
    return;
  }
  var brands = uniqueSortedStrings(
    (function () {
      var b = [];
      var j;
      for (j = 0; j < list.length; j++) {
        b.push(list[j].brand);
      }
      return b;
    })()
  );
  var colors = uniqueSortedStrings(
    (function () {
      var c = [];
      var k;
      for (k = 0; k < list.length; k++) {
        c.push(list[k].color);
      }
      return c;
    })()
  );
  var minY = list[0].year;
  var maxY = list[0].year;
  var y;
  for (y = 1; y < list.length; y++) {
    if (list[y].year < minY) {
      minY = list[y].year;
    }
    if (list[y].year > maxY) {
      maxY = list[y].year;
    }
  }
  var brandOpts = [{ v: "", t: "Any" }];
  var bi;
  for (bi = 0; bi < brands.length; bi++) {
    brandOpts.push({ v: brands[bi], t: brands[bi] });
  }
  fillSelect(idPrefix + "brand", brandOpts);

  var yearOpts = [{ v: "", t: "Any" }];
  var yr;
  for (yr = minY; yr <= maxY; yr++) {
    yearOpts.push({ v: String(yr), t: String(yr) });
  }
  fillSelect(idPrefix + "year-from", yearOpts);
  fillSelect(idPrefix + "year-to", yearOpts);

  var colorOpts = [{ v: "", t: "Any" }];
  var ci;
  for (ci = 0; ci < colors.length; ci++) {
    colorOpts.push({ v: colors[ci], t: colors[ci] });
  }
  fillSelect(idPrefix + "color", colorOpts);

  function moneyRow(val, label) {
    return { v: val === "" ? "" : String(val), t: label };
  }
  var priceMinRows = [
    moneyRow("", "Any"),
    moneyRow("80000", "¥80,000"),
    moneyRow("120000", "¥120,000"),
    moneyRow("150000", "¥150,000"),
    moneyRow("200000", "¥200,000"),
    moneyRow("250000", "¥250,000"),
    moneyRow("300000", "¥300,000"),
  ];
  var priceMaxRows = [
    moneyRow("", "Any"),
    moneyRow("150000", "¥150,000"),
    moneyRow("200000", "¥200,000"),
    moneyRow("250000", "¥250,000"),
    moneyRow("300000", "¥300,000"),
    moneyRow("400000", "¥400,000"),
    moneyRow("500000", "¥500,000"),
    moneyRow("800000", "¥800,000+"),
  ];
  fillSelect(idPrefix + "price-min", priceMinRows);
  fillSelect(idPrefix + "price-max", priceMaxRows);

  form.setAttribute("data-populated", "1");
}

function populateResultFiltersOnce() {
  populateFilterFormOnce("results-main-form", "filter-");
}

function parseSelectInt(id) {
  var el = document.getElementById(id);
  if (!el || el.value === "") {
    return null;
  }
  var n = parseInt(el.value, 10);
  if (isNaN(n)) {
    return null;
  }
  return n;
}

function getFilterStateFromPrefix(idPrefix) {
  var brandEl = document.getElementById(idPrefix + "brand");
  var colorEl = document.getElementById(idPrefix + "color");
  var provinceEl = document.getElementById(idPrefix + "province");
  var yearFrom = parseSelectInt(idPrefix + "year-from");
  var yearTo = parseSelectInt(idPrefix + "year-to");
  var priceMin = parseSelectInt(idPrefix + "price-min");
  var priceMax = parseSelectInt(idPrefix + "price-max");
  if (yearFrom != null && yearTo != null && yearFrom > yearTo) {
    var ys = yearFrom;
    yearFrom = yearTo;
    yearTo = ys;
  }
  if (priceMin != null && priceMax != null && priceMin > priceMax) {
    var swap = priceMin;
    priceMin = priceMax;
    priceMax = swap;
  }
  return {
    brand: brandEl && brandEl.value ? brandEl.value : "",
    color: colorEl && colorEl.value ? colorEl.value : "",
    province: provinceEl && provinceEl.value ? provinceEl.value : "",
    yearFrom: yearFrom,
    yearTo: yearTo,
    priceMin: priceMin,
    priceMax: priceMax,
  };
}

function getFilterState() {
  return getFilterStateFromPrefix("filter-");
}

function closeDiscoveryFilterPanel() {
  var panel = document.getElementById("discovery-filter-panel");
  var btn = document.getElementById("discovery-filter-toggle");
  if (panel) {
    panel.hidden = true;
  }
  if (btn) {
    btn.setAttribute("aria-expanded", "false");
  }
}

function matchesKeyword(car, q) {
  var keyword = (q != null ? String(q) : "").trim().toLowerCase();
  if (!keyword) {
    return true;
  }
  var loc = car.location != null ? String(car.location) : "";
  var blob = (car.brand + " " + car.model + " " + car.color + " " + loc).toLowerCase();
  return blob.indexOf(keyword) != -1;
}

function matchesFilters(car, f) {
  if (f.brand && car.brand !== f.brand) {
    return false;
  }
  if (f.color && car.color !== f.color) {
    return false;
  }
  if (f.yearFrom != null && car.year < f.yearFrom) {
    return false;
  }
  if (f.yearTo != null && car.year > f.yearTo) {
    return false;
  }
  if (f.province) {
    var loc = car.location != null ? String(car.location) : "";
    if (loc.indexOf(f.province) === -1) {
      return false;
    }
  }
  if (f.priceMin != null && car.priceRmb < f.priceMin) {
    return false;
  }
  if (f.priceMax != null && car.priceRmb > f.priceMax) {
    return false;
  }
  return true;
}

function filterListings(list, q, f) {
  var out = [];
  var i;
  for (i = 0; i < list.length; i++) {
    var car = list[i];
    if (matchesKeyword(car, q) && matchesFilters(car, f)) {
      out.push(car);
    }
  }
  return out;
}

function formatPriceRmb(n) {
  return "¥" + Number(n).toLocaleString("en-US");
}

function renderListings(items) {
  var main = document.getElementById("listings");
  var countEl = document.getElementById("result-count");
  if (!main) {
    return;
  }
  main.innerHTML = "";
  if (countEl) {
    countEl.textContent =
      items.length + (items.length === 1 ? " listing" : " listings");
  }
  if (items.length === 0) {
    main.innerHTML = "<p class='rank-meta empty-tip'>No matching listings.</p>";
    return;
  }
  var html = "";
  var j;
  for (j = 0; j < items.length; j++) {
    var c = items[j];
    var title = escapeHtml(c.brand + " " + c.model);
    var locLine =
      c.location != null && String(c.location).trim() !== ""
        ? escapeHtml(String(c.location))
        : "—";
    html +=
      "<article class='listing-card'>" +
      "<div class='listing-card-head'>" +
      "<h3 class='listing-title'>" +
      title +
      "</h3>" +
      "<span class='listing-price'>" +
      formatPriceRmb(c.priceRmb) +
      "</span>" +
      "</div>" +
      "<p class='listing-meta'>" +
      escapeHtml(String(c.year)) +
      " · " +
      locLine +
      " · " +
      escapeHtml(c.color) +
      "</p>" +
      "</article>";
  }
  main.innerHTML = html;
}

function refreshResultsView() {
  var route = parseRoute();
  if (route.name != "results") {
    return;
  }
  var all = getAllListings();
  var filtered = filterListings(all, route.q, getFilterState());
  renderListings(filtered);
}

function wirePageOnce() {
  if (pageReady) {
    return;
  }
  pageReady = true;

  var discToggle = document.getElementById("discovery-filter-toggle");
  var discPanel = document.getElementById("discovery-filter-panel");
  if (discToggle && discPanel) {
    discToggle.addEventListener("click", function () {
      var open = discPanel.hidden;
      discPanel.hidden = !open;
      discToggle.setAttribute("aria-expanded", open ? "true" : "false");
      populateFilterFormOnce("discovery-search-form", "disc-filter-");
    });
  }
  var discClose = document.getElementById("discovery-filter-close");
  if (discClose) {
    discClose.addEventListener("click", function () {
      closeDiscoveryFilterPanel();
    });
  }

  document.querySelector("#view-discovery .back-btn").addEventListener("click", function () {
    window.location.href = "home.html";
  });

  document.querySelector("#view-results .back-btn").addEventListener("click", function () {
    window.location.href = "home.html";
  });

  var resultsMainForm = document.getElementById("results-main-form");
  if (resultsMainForm) {
    resultsMainForm.addEventListener("reset", function () {
      window.setTimeout(refreshResultsView, 0);
    });
  }
}

function onRoute() {
  wirePageOnce();

  var route = parseRoute();

  renderHotTags();
  renderRankList(document.getElementById("rank-sales"));
  renderRankList(document.getElementById("rank-drop"));
  renderRankList(document.getElementById("rank-hotsearch"));
  renderRankList(document.getElementById("rank-pop"));

  if (route.name == "results") {
    showView("results");
    document.getElementById("results-search-input").value = route.q;
    populateResultFiltersOnce();
    refreshResultsView();
  } else {
    showView("discovery");
    populateFilterFormOnce("discovery-search-form", "disc-filter-");
  }
}

window.addEventListener("hashchange", onRoute);

if (document.readyState == "loading") {
  document.addEventListener("DOMContentLoaded", onRoute);
} else {
  onRoute();
}
