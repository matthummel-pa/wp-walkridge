/* Walkridge Field Map — WordPress plugin version.
   Ported from the static concept area-map.js.
   Data endpoints, admin save, and tour links are wired to WP REST API / config. */
(function () {
  "use strict";

  var STORAGE = "hg-area-map-v1";
  var MAPS_STORAGE = "hg-maps-config-v2";
  var ADMIN_GATE = "hg-area-admin"; /* WP: also gated by data-diorama-mode="admin" presence */
  var PIN_COLOR = { ridge: "#e0be72", hill: "#7eb56a", hike: "#7eb56a", downtown: "#c9a06a", meet: "#d36a3a", monument: "#d4b56a", tour: "#d36a3a", building: "#c9a06a", area: "#7eb56a" };
  var ITIN_STORAGE = "hg-itinerary-v1";
  var DEFAULT_MAPS = {
    center: { lat: 39.812, lng: -77.236 },
    zoom: 13.4,
    rotation: -0.18
  };

  var DEFAULTS = {
    version: 1,
    places: [
      { id: "lincoln-square", title: "Lincoln Square", blurb: "The civic heart of downtown Gettysburg. The lantern walk uses a sample downtown meet at the flagpole — tour geography, not a live business address.", category: "downtown", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#after-dark" : "tours.html#after-dark"), tourLabel: "Ghosts of Gettysburg Lantern Walk", lat: 39.83092, lng: -77.23114, x: 68, z: 16, elev: 28, visible: true },
      { id: "david-wills-house", title: "David Wills House", blurb: "On the square, where Lincoln finished the Gettysburg Address the night before the cemetery dedication.", category: "downtown", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#after-dark" : "tours.html#after-dark"), tourLabel: "Ghosts of Gettysburg Lantern Walk", lat: 39.83055, lng: -77.23095, x: 71, z: 19, elev: 26, visible: true },
      { id: "sample-office", title: "Sample ticket office", blurb: "100 Sample Street — concept placeholder, not a live storefront. Day walking and bus tours in this demo check in here.", category: "meet", tourHref: (window.hgfmConfig && window.hgfmConfig.shopUrl || "book.html"), tourLabel: "Book a tour", lat: 39.8292, lng: -77.2314, x: 74, z: 24, elev: 24, visible: true },
      { id: "national-cemetery", title: "Soldiers' National Cemetery", blurb: "South of the square. The dedication ground of the Address, on the rise that became Cemetery Hill.", category: "ridge", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#historical" : "tours.html#historical"), tourLabel: "Battlefield Highlights Walking Tour", lat: 39.82155, lng: -77.23135, x: 62, z: 30, elev: 22, visible: true },
      { id: "mcpherson-ridge", title: "McPherson Ridge", blurb: "Northwest of town. First-day ground: the opening fight on July 1, walked on the Highlights tour.", category: "ridge", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#historical" : "tours.html#historical"), tourLabel: "Battlefield Highlights Walking Tour", lat: 39.8385, lng: -77.2508, x: 34, z: 22, elev: 18, visible: true },
      { id: "seminary-ridge", title: "Seminary Ridge", blurb: "The long western ridge. Confederate line after July 1, marked by the seminary cupola.", category: "ridge", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#historical" : "tours.html#historical"), tourLabel: "Battlefield Highlights Walking Tour", lat: 39.8198, lng: -77.2448, x: 24, z: 48, elev: 20, visible: true },
      { id: "cemetery-ridge", title: "Cemetery Ridge", blurb: "The Union fishhook. Walking tours cross this ridge to tie all three days into one story.", category: "ridge", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl ? window.hgfmConfig.toursUrl + "#historical" : "tours.html#historical"), tourLabel: "Battlefield Highlights Walking Tour", lat: 39.8138, lng: -77.2348, x: 54, z: 50, elev: 22, visible: true },
      { id: "high-water-mark", title: "High Water Mark", blurb: "The Copse of Trees on Cemetery Ridge — the farthest reach of Pickett's Charge on July 3.", category: "ridge", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl || "tours.html"), tourLabel: "Pickett's Charge Deluxe Bus Tour", lat: 39.81248, lng: -77.23555, x: 52, z: 56, elev: 24, visible: true },
      { id: "devils-den", title: "Devil's Den", blurb: "Jumbled boulders at the south end of the field. Close fighting on July 2, walked on the hike.", category: "hike", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl || "tours.html"), tourLabel: "Little Round Top & Devil's Den Hike", lat: 39.7915, lng: -77.2424, x: 46, z: 78, elev: 16, visible: true },
      { id: "little-round-top", title: "Little Round Top", blurb: "The rocky hill Union forces fought to hold on July 2. The hike climbs this ground.", category: "hill", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl || "tours.html"), tourLabel: "Little Round Top & Devil's Den Hike", lat: 39.7914, lng: -77.237, x: 58, z: 80, elev: 36, visible: true },
      { id: "big-round-top", title: "Big Round Top", blurb: "The wooded height just south of Little Round Top, commanding the southern end of the field.", category: "hill", tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl || "tours.html"), tourLabel: "Little Round Top & Devil's Den Hike", lat: 39.7872, lng: -77.2378, x: 66, z: 86, elev: 40, visible: true }
    ]
  };

  function clonePlaces(list) {
    return JSON.parse(JSON.stringify(list || []));
  }

  function readStored() {
    try {
      var raw = localStorage.getItem(STORAGE);
      if (!raw) return null;
      var parsed = JSON.parse(raw);
      if (parsed && Array.isArray(parsed.places)) return parsed;
    } catch (err) { /* demo storage may be blocked */ }
    return null;
  }

  function writeStored(places) {
    localStorage.setItem(STORAGE, JSON.stringify({ version: 1, places: places }));
  }

  function readMapsStored() {
    try {
      var raw = localStorage.getItem(MAPS_STORAGE);
      if (!raw) return null;
      var parsed = JSON.parse(raw);
      if (parsed && typeof parsed === "object") return parsed;
    } catch (err) { /* ignore */ }
    return null;
  }

  function writeMapsStored(cfg) {
    var copy = JSON.parse(JSON.stringify(cfg || {}));
    localStorage.setItem(MAPS_STORAGE, JSON.stringify(copy));
  }

  function mergeMaps(base, extra) {
    var out = JSON.parse(JSON.stringify(base || DEFAULT_MAPS));
    extra = extra || {};
    Object.keys(extra).forEach(function (k) {
      if (k === "center" && extra.center) {
        out.center = Object.assign({}, out.center, extra.center);
      } else if (extra[k] !== undefined && extra[k] !== null && extra[k] !== "") {
        out[k] = extra[k];
      }
    });
    return out;
  }

  function loadMapsConfig(done) {
    var stored = readMapsStored();
    fetch((window.hgfmConfig && window.hgfmConfig.endpoints && window.hgfmConfig.endpoints.mapsConfig) || "data/maps-config.json", { cache: "no-store", headers: { "X-WP-Nonce": (window.hgfmConfig && window.hgfmConfig.nonce) || "" } })
      .then(function (res) { return res.ok ? res.json() : {}; })
      .then(function (fileCfg) {
        var cfg = mergeMaps(DEFAULT_MAPS, fileCfg);
        cfg = mergeMaps(cfg, stored);
        done(cfg);
      })
      .catch(function () {
        var cfg = mergeMaps(DEFAULT_MAPS, stored);
        done(cfg);
      });
  }

  var MAP_PIN = "<svg class=\"map-pin-icon\" width=\"14\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" aria-hidden=\"true\"><path d=\"M12 22s7-7.2 7-12a7 7 0 10-14 0c0 4.8 7 12 7 12z\" stroke=\"currentColor\" stroke-width=\"2\"/><circle cx=\"12\" cy=\"10\" r=\"2.4\" fill=\"currentColor\"/></svg>";
  var placePool = [];

  function fillList(places, opts) {
    var list = document.querySelector("[data-diorama-list]");
    var visible = places.filter(function (p) {
      return p.visible !== false && p.category !== "monument";
    });
    if (!list) {
      placePool = places;
      return visible;
    }
    placePool = places;
    list.innerHTML = "";
    visible.forEach(function (place) {
      var li = document.createElement("li");
      li.className = "place-row";
      li.innerHTML =
        "<button type=\"button\" class=\"place-main\" data-id=\"" + esc(place.id) + "\" data-open-map=\"" + esc(place.id) + "\"><b>" + esc(place.title) + "</b><span>" + esc(catLabel(place.category)) + "</span></button>" +
        "<button type=\"button\" class=\"place-map-btn\" data-open-map=\"" + esc(place.id) + "\" title=\"View on map\" aria-label=\"View " + esc(place.title) + " on the map\">" + MAP_PIN + "</button>" +
        "<button type=\"button\" class=\"place-add-btn\" data-add-itinerary=\"" + esc(place.id) + "\">Add</button>";
      list.appendChild(li);
    });
    return visible;
  }

  function placeKinds(place) {
    if (place && Array.isArray(place.kinds) && place.kinds.length) return place.kinds;
    if (!place) return ["area"];
    if (place.category === "monument") return ["monument"];
    if (place.category === "meet") return ["tour", "building"];
    if (place.id === "david-wills-house") return ["building"];
    if (place.id === "seminary-ridge") return ["area", "building"];
    if (place.category === "downtown") return ["area", "tour"];
    return ["area"];
  }

  function primaryKind(place) {
    var kinds = placeKinds(place);
    var order = ["tour", "building", "area", "monument"];
    var i;
    for (i = 0; i < order.length; i++) {
      if (kinds.indexOf(order[i]) !== -1) return order[i];
    }
    return kinds[0] || "area";
  }

  function pinStyle(feature, selectedId, zoom) {
    var cat = feature.get("category");
    var kind = feature.get("kind") || (cat === "monument" ? "monument" : "area");
    var selected = feature.get("placeId") === selectedId;
    var color = PIN_COLOR[kind] || PIN_COLOR[cat] || "#e0be72";
    var monument = cat === "monument" || kind === "monument";
    var showLabel = selected || (!monument && zoom >= 14.5) || (monument && zoom >= 16.4);
    var style = {
      image: new ol.style.Circle({
        radius: selected ? 9 : (monument ? 5 : 7),
        fill: new ol.style.Fill({ color: color }),
        stroke: new ol.style.Stroke({ color: "#14100a", width: monument ? 1.25 : 2 })
      }),
      zIndex: selected ? 20 : (monument ? 8 : 12)
    };
    if (showLabel) {
      style.text = new ol.style.Text({
        text: String(feature.get("title") || ""),
        offsetY: monument ? -14 : -18,
        font: monument ? "500 10px \"IBM Plex Mono\", monospace" : "600 11px \"IBM Plex Mono\", monospace",
        fill: new ol.style.Fill({ color: "#f0d9a0" }),
        stroke: new ol.style.Stroke({ color: "#07111c", width: 4 })
      });
    }
    return new ol.style.Style(style);
  }

  function clusterStyle(feature, selectedId) {
    var members = feature.get("features") || [];
    if (members.length === 1) {
      return pinStyle(members[0], selectedId, 16);
    }
    var count = String(members.length);
    return new ol.style.Style({
      image: new ol.style.Circle({
        radius: 12 + Math.min(8, Math.log(members.length) * 3),
        fill: new ol.style.Fill({ color: "rgba(212,181,106,0.92)" }),
        stroke: new ol.style.Stroke({ color: "#14100a", width: 2 })
      }),
      text: new ol.style.Text({
        text: count,
        font: "700 11px \"IBM Plex Mono\", monospace",
        fill: new ol.style.Fill({ color: "#14100a" })
      }),
      zIndex: 6
    });
  }

  function buildingStyle(feature) {
    var layer = feature.get("layer") || feature.get("class") || "";
    if (layer && String(layer).indexOf("building") === -1 && layer !== "building") {
      return undefined;
    }
    return new ol.style.Style({
      fill: new ol.style.Fill({ color: "rgba(201,162,74,0.38)" }),
      stroke: new ol.style.Stroke({ color: "rgba(240,217,160,0.75)", width: 1 })
    });
  }

  function mountOpenLayers(stage, places, opts, cfg, done) {
    opts = opts || {};
    if (!(window.ol && ol.Map)) {
      var cssApi = mountCss(stage, places, opts);
      var banner = document.createElement("p");
      banner.className = "maps-key-banner";
      banner.textContent = "OpenLayers did not load. Check the ol.js script on this page.";
      stage.prepend(banner);
      if (done) done(cssApi);
      return;
    }

    stage.classList.remove("is-google");
    stage.classList.add("is-ol");
    stage.innerHTML = "<div class=\"ol-host\" data-ol-host></div>" +
      "<p class=\"dio-hint\">A gold circle with a number (for example 4) is a cluster — that many monuments sit close together at this zoom. Click it to zoom in. Close a popup to zoom back out.</p>";
    var host = stage.querySelector("[data-ol-host]");
    var detail = document.querySelector("[data-diorama-detail]");
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var selectedId = null;
    var view = new ol.View({
      center: ol.proj.fromLonLat([cfg.center.lng, cfg.center.lat]),
      zoom: cfg.zoom || 13.4,
      rotation: cfg.rotation || 0,
      constrainRotation: false
    });
    var pinSource = new ol.source.Vector();
    var monumentSource = new ol.source.Vector();
    var clusterSource = (ol.source.Cluster)
      ? new ol.source.Cluster({ distance: 42, minDistance: 18, source: monumentSource })
      : monumentSource;
    var pins = new ol.layer.Vector({
      source: pinSource,
      style: function (feature) {
        return pinStyle(feature, selectedId, view.getZoom());
      },
      zIndex: 40
    });
    var monumentLayer = new ol.layer.Vector({
      source: clusterSource,
      style: function (feature) {
        if (feature.get("features")) return clusterStyle(feature, selectedId);
        return pinStyle(feature, selectedId, view.getZoom());
      },
      zIndex: 30
    });

    var roads = new ol.layer.Tile({
      source: new ol.source.OSM({
        attributions: "© OpenStreetMap contributors"
      })
    });
    var satellite = new ol.layer.Tile({
      visible: false,
      source: new ol.source.XYZ({
        url: "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        attributions: "Satellite: Esri, Maxar, Earthstar Geographics, and the GIS User Community",
        maxZoom: 19
      })
    });

    var layers = [roads, satellite];
    var buildings = null;
    if (ol.layer.VectorTile && ol.format.MVT) {
      buildings = new ol.layer.VectorTile({
        declutter: true,
        source: new ol.source.VectorTile({
          format: new ol.format.MVT(),
          url: "https://tiles.openfreemap.org/planet/{z}/{x}/{y}.pbf",
          attributions: "© OpenStreetMap, © OpenFreeMap"
        }),
        style: function (feature) {
          var layer = feature.get("layer");
          if (layer !== "building" && layer !== "buildings") return undefined;
          return buildingStyle(feature);
        },
        minZoom: 13,
        opacity: 0.95
      });
      layers.push(buildings);
    }
    layers.push(monumentLayer, pins);

    var map = new ol.Map({
      target: host,
      layers: layers,
      view: view
    });

    stage._olmap = map;

    var basemap = document.createElement("div");
    basemap.className = "ol-basemap";
    basemap.setAttribute("role", "group");
    basemap.setAttribute("aria-label", "Basemap");
    basemap.innerHTML = "<button type=\"button\" data-basemap=\"map\" class=\"is-active\">Map</button><button type=\"button\" data-basemap=\"sat\">Satellite</button>";
    host.appendChild(basemap);

    var legend = document.createElement("div");
    legend.className = "ol-legend";
    legend.setAttribute("aria-label", "Map legend");
    legend.innerHTML =
      "<p><span class=\"leg-swatch leg-cluster\">4</span> Cluster: that many monuments overlap at this zoom. Click to zoom in.</p>" +
      "<p><span class=\"leg-swatch leg-monument\"></span> Monument</p>" +
      "<p><span class=\"leg-swatch leg-tour\"></span> Tour location</p>" +
      "<p><span class=\"leg-swatch leg-building\"></span> Significant building</p>" +
      "<p><span class=\"leg-swatch leg-area\"></span> Popular area</p>";
    host.appendChild(legend);

    var filterHost = document.createElement("div");
    filterHost.className = "ol-filters";
    filterHost.setAttribute("data-map-filters", "");
    filterHost.setAttribute("role", "group");
    filterHost.setAttribute("aria-label", "Show on the map");
    [
      ["monument", "Monuments"],
      ["tour", "Tour locations"],
      ["building", "Buildings"],
      ["area", "Popular areas"]
    ].forEach(function (pair) {
      var chip = document.createElement("button");
      chip.type = "button";
      chip.className = "is-on";
      chip.setAttribute("data-filter", pair[0]);
      chip.setAttribute("aria-pressed", "true");
      chip.textContent = pair[1];
      filterHost.appendChild(chip);
    });
    host.appendChild(filterHost);

    function setBasemap(mode) {
      var satOn = mode === "sat";
      roads.setVisible(!satOn);
      satellite.setVisible(satOn);
      if (buildings) buildings.setVisible(!satOn);
      basemap.querySelectorAll("[data-basemap]").forEach(function (btn) {
        btn.classList.toggle("is-active", btn.getAttribute("data-basemap") === mode);
      });
    }
    basemap.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-basemap]");
      if (!btn) return;
      e.preventDefault();
      e.stopPropagation();
      setBasemap(btn.getAttribute("data-basemap"));
    });

    var popupEl = document.createElement("div");
    popupEl.className = "ol-popup";
    popupEl.setAttribute("role", "dialog");
    popupEl.setAttribute("aria-live", "polite");
    popupEl.innerHTML = "<button type=\"button\" class=\"ol-popup-closer\" aria-label=\"Close\"></button><div class=\"ol-popup-body\"></div>";
    var popupBody = popupEl.querySelector(".ol-popup-body");
    var popupCloser = popupEl.querySelector(".ol-popup-closer");
    var overlay = new ol.Overlay({
      element: popupEl,
      positioning: "bottom-center",
      offset: [0, -18],
      stopEvent: true,
      autoPan: {
        animation: { duration: reduce ? 0 : 280 },
        margin: 36
      }
    });
    map.addOverlay(overlay);

    var fieldView = {
      center: ol.proj.fromLonLat([Number(cfg.center.lng), Number(cfg.center.lat)]),
      zoom: cfg.zoom || 13.4,
      rotation: cfg.rotation || 0
    };
    var zoomedIn = false;

    function restoreOverview() {
      if (!zoomedIn) return;
      zoomedIn = false;
      if (reduce) {
        view.setCenter(fieldView.center);
        view.setZoom(fieldView.zoom);
        view.setRotation(fieldView.rotation);
        return;
      }
      view.animate({
        center: fieldView.center,
        zoom: fieldView.zoom,
        rotation: fieldView.rotation,
        duration: 700
      });
    }

    function hidePopup(restore) {
      overlay.setPosition(undefined);
      popupEl.classList.remove("is-open");
      if (restore) restoreOverview();
    }

    function showPopup(place) {
      if (!place || !isFinite(Number(place.lat)) || !isFinite(Number(place.lng))) {
        hidePopup(false);
        return;
      }
      popupEl.setAttribute("aria-label", place.title);
      popupBody.innerHTML = placeCardHTML(place);
      popupEl.classList.add("is-open");
      overlay.setPosition(ol.proj.fromLonLat([Number(place.lng), Number(place.lat)]));
      if (overlay.panIntoView) overlay.panIntoView({ margin: 36 });
    }

    popupCloser.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      hidePopup(true);
    });

    var monuments = filterMonumentsAwayFromTours(opts.monuments || [], places);
    var allPlaces = places.concat(monuments);

    var select = function (id, fly) {
      var place = allPlaces.filter(function (p) { return p.id === id; })[0];
      if (!place) return;
      selectedId = id;
      pins.changed();
      monumentLayer.changed();
      highlight(document, id);
      fillDetail(detail, place);
      if (fly !== false) showPopup(place);
      else hidePopup(false);
      if (fly !== false && isFinite(Number(place.lat)) && isFinite(Number(place.lng))) {
        var center = ol.proj.fromLonLat([Number(place.lng), Number(place.lat)]);
        zoomedIn = true;
        var targetZoom = place.category === "monument" ? 17.2 : 15.8;
        if (reduce) {
          view.setCenter(center);
          view.setZoom(Math.max(view.getZoom(), targetZoom));
        } else {
          view.animate({ center: center, zoom: targetZoom, duration: 700 });
        }
      }
      if (opts.onSelect) opts.onSelect(place);
    };

    var filters = { monument: true, tour: true, building: true, area: true };

    function placeMatchesFilter(place) {
      var box = host.querySelector("[data-map-filters]");
      if (box) {
        box.querySelectorAll("[data-filter]").forEach(function (el) {
          var key = el.getAttribute("data-filter");
          if (el.tagName === "INPUT") filters[key] = el.checked;
          else filters[key] = el.getAttribute("aria-pressed") === "true";
        });
      }
      return placeKinds(place).some(function (kind) { return filters[kind]; });
    }

    function addMonumentFeatures(list) {
      monumentSource.clear();
      list.forEach(function (place) {
        if (!isFinite(Number(place.lat)) || !isFinite(Number(place.lng))) return;
        monumentSource.addFeature(new ol.Feature({
          geometry: new ol.geom.Point(ol.proj.fromLonLat([Number(place.lng), Number(place.lat)])),
          placeId: place.id,
          title: place.title,
          category: "monument",
          kind: "monument"
        }));
      });
    }

    function syncMarkers(nextPlaces) {
      places = nextPlaces;
      monuments = filterMonumentsAwayFromTours(opts.monuments || [], places);
      allPlaces = places.concat(monuments);
      stage._allPlaces = allPlaces;
      pinSource.clear();
      var visible = fillList(places, opts);
      visible.forEach(function (place) {
        if (!isFinite(Number(place.lat)) || !isFinite(Number(place.lng))) return;
        if (!placeMatchesFilter(place)) return;
        pinSource.addFeature(new ol.Feature({
          geometry: new ol.geom.Point(ol.proj.fromLonLat([Number(place.lng), Number(place.lat)])),
          placeId: place.id,
          title: place.title,
          category: place.category,
          kind: primaryKind(place)
        }));
      });
      addMonumentFeatures(filters.monument ? monuments : []);
      monumentLayer.setVisible(filters.monument);
    }

    var search = document.querySelector("[data-monument-search]");
    var searchHits = document.querySelector("[data-monument-hits]");
    if (search && searchHits && !search.dataset.bound) {
      search.dataset.bound = "1";
      search.addEventListener("input", function () {
        var q = search.value.trim().toLowerCase();
        searchHits.innerHTML = "";
        if (q.length < 2) return;
        monuments.filter(function (m) {
          return m.title.toLowerCase().indexOf(q) !== -1;
        }).slice(0, 12).forEach(function (m) {
          var row = document.createElement("div");
          row.className = "place-row";
          row.innerHTML =
            "<button type=\"button\" class=\"place-main\" data-id=\"" + esc(m.id) + "\" data-open-map=\"" + esc(m.id) + "\"><b>" + esc(m.title) + "</b><span>Monument</span></button>" +
            "<button type=\"button\" class=\"place-map-btn\" data-open-map=\"" + esc(m.id) + "\" title=\"View on map\" aria-label=\"View " + esc(m.title) + " on the map\">" + MAP_PIN + "</button>";
          searchHits.appendChild(row);
        });
      });
      searchHits.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-open-map]");
        if (btn) return;
        var legacy = e.target.closest("button[data-id]");
        if (legacy) openMapOverlay(legacy.getAttribute("data-id"));
      });
    }

    var filterBox = host.querySelector("[data-map-filters]");
    if (filterBox && !filterBox.dataset.bound) {
      filterBox.dataset.bound = "1";
      filterBox.addEventListener("click", function (e) {
        var btn = e.target.closest("button[data-filter]");
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        var on = btn.getAttribute("aria-pressed") !== "true";
        btn.setAttribute("aria-pressed", on ? "true" : "false");
        btn.classList.toggle("is-on", on);
        syncMarkers(places);
      });
      filterBox.addEventListener("change", function () {
        syncMarkers(places);
      });
    }

    syncMarkers(places);
    stage._hgSelect = select;
    stage._refreshPlaces = syncMarkers;

    map.on("singleclick", function (evt) {
      var hit = map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
        if (layer === pins || layer === monumentLayer) return feature;
        return null;
      });
      if (hit) {
        var cluster = hit.get("features");
        if (cluster && cluster.length > 1) {
          view.animate({
            center: hit.getGeometry().getCoordinates(),
            zoom: Math.min((view.getZoom() || 13) + 1.6, 18),
            duration: reduce ? 0 : 400
          });
          return;
        }
        var id = cluster && cluster[0] ? cluster[0].get("placeId") : hit.get("placeId");
        select(id);
        return;
      }
      hidePopup(!opts.onMapClick);
      if (opts.onMapClick) {
        var lonlat = ol.proj.toLonLat(evt.coordinate);
        opts.onMapClick({ lng: lonlat[0], lat: lonlat[1] });
      }
    });

    map.on("pointermove", function (evt) {
      var hit = map.hasFeatureAtPixel(evt.pixel, {
        layerFilter: function (layer) { return layer === pins || layer === monumentLayer; }
      });
      map.getTargetElement().style.cursor = hit ? "pointer" : "";
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") hidePopup(true);
    });

    if (!opts.skipAuto && places[0]) select(places[0].id, false);
    setTimeout(function () { map.updateSize(); }, 50);
    if (done) done({ select: select });
  }

  function hydratePlaces(list) {
    var byId = {};
    DEFAULTS.places.forEach(function (p) { byId[p.id] = p; });
    return (list || []).map(function (p) {
      return Object.assign({}, byId[p.id] || {}, p);
    });
  }

  function loadPlaces(done) {
    var stored = readStored();
    if (stored) {
      done(hydratePlaces(stored.places));
      return;
    }
    fetch((window.hgfmConfig && window.hgfmConfig.endpoints && window.hgfmConfig.endpoints.places) || "data/area-map.json", { cache: "no-store", headers: { "X-WP-Nonce": (window.hgfmConfig && window.hgfmConfig.nonce) || "" } })
      .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
      .then(function (data) {
        done(hydratePlaces((data && data.places) || DEFAULTS.places));
      })
      .catch(function () {
        done(clonePlaces(DEFAULTS.places));
      });
  }

  function loadMonuments(done) {
    fetch((window.hgfmConfig && window.hgfmConfig.endpoints && window.hgfmConfig.endpoints.monuments) || "data/monuments.json", { cache: "no-store", headers: { "X-WP-Nonce": (window.hgfmConfig && window.hgfmConfig.nonce) || "" } })
      .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
      .then(function (data) {
        done(Array.isArray(data && data.monuments) ? data.monuments : []);
      })
      .catch(function () { done([]); });
  }

  function filterMonumentsAwayFromTours(monuments, tours) {
    return (monuments || []).filter(function (m) {
      return !(tours || []).some(function (t) {
        return isFinite(Number(t.lat)) && metersApart(m, t) < 22;
      });
    });
  }

  function terrainHTML() {
    return [
      '<div class="diorama-world" data-dio-world>',
      '  <div class="dio-table" aria-hidden="true">',
      '    <div class="dio-leg dio-leg--nw"></div>',
      '    <div class="dio-leg dio-leg--ne"></div>',
      '    <div class="dio-leg dio-leg--sw"></div>',
      '    <div class="dio-leg dio-leg--se"></div>',
      '    <div class="dio-apron"></div>',
      '    <div class="dio-board">',
      '      <div class="dio-grass"></div>',
      '      <div class="dio-wheat"></div>',
      '      <div class="dio-road dio-road--emmitsburg"></div>',
      '      <div class="dio-road dio-road--taneytown"></div>',
      '      <div class="dio-road dio-road--baltimore"></div>',
      '      <div class="dio-patch" data-patch="mcpherson-ridge"></div>',
      '      <div class="dio-patch dio-ridge-west" data-patch="seminary-ridge"><span class="dio-cupola"></span></div>',
      '      <div class="dio-patch dio-ridge-east" data-patch="cemetery-ridge"></div>',
      '      <div class="dio-patch dio-copse" data-patch="high-water-mark"></div>',
      '      <div class="dio-patch dio-cemetery" data-patch="national-cemetery"></div>',
      '      <div class="dio-patch dio-rocks" data-patch="devils-den"><i></i><i></i><i></i><i></i></div>',
      '      <div class="dio-patch dio-hill dio-hill--lrt" data-patch="little-round-top"></div>',
      '      <div class="dio-patch dio-hill dio-hill--brt" data-patch="big-round-top"></div>',
      '      <div class="dio-town" data-patch="lincoln-square">',
      '        <span class="dio-bldg"></span><span class="dio-bldg tall"></span><span class="dio-bldg"></span>',
      '        <span class="dio-bldg wide"></span><span class="dio-flag" data-patch="david-wills-house"></span>',
      '      </div>',
      '      <div class="dio-office" data-patch="sample-office"></div>',
      '      <div class="dio-compass"><b>N</b></div>',
      '      <div class="dio-pins" data-diorama-pins></div>',
      '    </div>',
      '  </div>',
      '</div>',
      '<p class="dio-hint">Drag the table to turn it. Pins stay upright. Keyboard: use the place list.</p>'
    ].join("");
  }

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
    });
  }

  function catLabel(cat) {
    var map = { ridge: "Ridge", hill: "Hill", downtown: "Downtown", meet: "Meeting point", hike: "Hike ground", monument: "Monument" };
    return map[cat] || cat;
  }

  function formatCoords(lat, lng) {
    var n = Math.abs(Number(lat)).toFixed(5);
    var e = Math.abs(Number(lng)).toFixed(5);
    var ns = Number(lat) >= 0 ? "N" : "S";
    var ew = Number(lng) >= 0 ? "E" : "W";
    return n + "\u00b0 " + ns + ", " + e + "\u00b0 " + ew;
  }

  function metersApart(a, b) {
    var r = 6371000;
    var p1 = Number(a.lat) * Math.PI / 180;
    var p2 = Number(b.lat) * Math.PI / 180;
    var dp = (Number(b.lat) - Number(a.lat)) * Math.PI / 180;
    var dl = (Number(b.lng) - Number(a.lng)) * Math.PI / 180;
    var h = Math.sin(dp / 2) * Math.sin(dp / 2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl / 2) * Math.sin(dl / 2);
    return 2 * r * Math.asin(Math.sqrt(h));
  }

  function placeCardHTML(place, opts) {
    opts = opts || {};
    var html = "<span class=\"eyebrow\">" + esc(catLabel(place.category)) + "</span>" +
      "<h3>" + esc(place.title) + "</h3>";
    if (place.image) {
      html += "<figure class=\"ol-popup-photo\"><img src=\"" + esc(place.image) + "\" alt=\"" + esc(place.title) + "\" loading=\"lazy\">";
      if (place.imageCredit) {
        html += "<figcaption>" + (place.imagePage
          ? "<a href=\"" + esc(place.imagePage) + "\" target=\"_blank\" rel=\"noopener\">" + esc(place.imageCredit) + "</a>"
          : esc(place.imageCredit)) + "</figcaption>";
      }
      html += "</figure>";
    } else if (place.category === "monument") {
      html += "<p class=\"ol-popup-photo-missing\">No verified public-domain photograph located for this marker yet.</p>";
    }
    html += "<p class=\"ol-popup-coords\">" + esc(formatCoords(place.lat, place.lng)) + "</p>";
    if (place.blurb && !opts.coordsOnly) html += "<p>" + esc(place.blurb) + "</p>";
    if (place.tourHref) {
      html += "<p><a class=\"btn btn-primary btn-sm\" href=\"" + esc(place.tourHref) + "\">" + esc(place.tourLabel || "See the tour") + "</a></p>";
    }
    html += "<p><button type=\"button\" class=\"btn btn-ghost btn-sm\" data-add-itinerary=\"" + esc(place.id) + "\">Add to itinerary</button></p>";
    return html;
  }

  function readItinerary() {
    try {
      var raw = localStorage.getItem(ITIN_STORAGE);
      var parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (err) {
      return [];
    }
  }

  function writeItinerary(items) {
    localStorage.setItem(ITIN_STORAGE, JSON.stringify(items));
  }

  function itineraryStatus(msg) {
    var el = document.querySelector("[data-itinerary-status]");
    if (el) el.textContent = msg || "";
  }

  function itineraryStopStyle(feature) {
    var n = String(feature.get("order") || "");
    var title = String(feature.get("title") || "");
    return [
      new ol.style.Style({
        image: new ol.style.Circle({
          radius: 13,
          fill: new ol.style.Fill({ color: "rgba(224,190,114,0.95)" }),
          stroke: new ol.style.Stroke({ color: "#14100a", width: 2 })
        }),
        text: new ol.style.Text({
          text: n,
          font: "700 12px \"IBM Plex Mono\", monospace",
          fill: new ol.style.Fill({ color: "#14100a" })
        }),
        zIndex: 20
      }),
      new ol.style.Style({
        text: new ol.style.Text({
          text: title,
          offsetY: 22,
          font: "600 11px \"IBM Plex Mono\", monospace",
          fill: new ol.style.Fill({ color: "#f0d9a0" }),
          stroke: new ol.style.Stroke({ color: "#07111c", width: 4 })
        }),
        zIndex: 21
      })
    ];
  }

  function ensureItineraryMap(cfg) {
    var stage = document.querySelector("[data-itinerary-map]");
    if (!stage || !(window.ol && ol.Map)) return null;
    if (stage._olmap) return stage;
    cfg = cfg || DEFAULT_MAPS;
    stage.classList.add("is-ol");
    stage.innerHTML = "<div class=\"ol-host\" data-itin-ol-host></div>";
    var host = stage.querySelector("[data-itin-ol-host]");
    var source = new ol.source.Vector();
    var view = new ol.View({
      center: ol.proj.fromLonLat([cfg.center.lng, cfg.center.lat]),
      zoom: cfg.zoom || 13.4
    });
    var map = new ol.Map({
      target: host,
      layers: [
        new ol.layer.Tile({
          source: new ol.source.OSM({ attributions: "© OpenStreetMap contributors" })
        }),
        new ol.layer.Vector({
          source: source,
          style: itineraryStopStyle,
          zIndex: 20
        })
      ],
      view: view
    });
    stage._olmap = map;
    stage._itinSource = source;
    map.on("singleclick", function (evt) {
      var hit = map.forEachFeatureAtPixel(evt.pixel, function (feature) { return feature; });
      if (hit && hit.get("placeId")) openMapOverlay(hit.get("placeId"));
    });
    map.on("pointermove", function (evt) {
      var hit = map.hasFeatureAtPixel(evt.pixel);
      map.getTargetElement().style.cursor = hit ? "pointer" : "";
    });
    return stage;
  }

  function updateItineraryMap() {
    var section = document.querySelector("[data-itinerary-map-section]");
    var items = readItinerary();
    if (section) section.hidden = items.length === 0;
    if (!items.length) return;
    loadMapsConfig(function (cfg) {
      var stage = ensureItineraryMap(cfg);
      if (!stage || !stage._itinSource || !stage._olmap) return;
      var source = stage._itinSource;
      source.clear();
      items.forEach(function (stop, i) {
        if (!isFinite(Number(stop.lat)) || !isFinite(Number(stop.lng))) return;
        source.addFeature(new ol.Feature({
          geometry: new ol.geom.Point(ol.proj.fromLonLat([Number(stop.lng), Number(stop.lat)])),
          placeId: stop.id,
          title: stop.title,
          order: i + 1
        }));
      });
      var fit = function () {
        stage._olmap.updateSize();
        if (!source.getFeatures().length) return;
        var extent = source.getExtent();
        if (!extent || !isFinite(extent[0])) return;
        stage._olmap.getView().fit(extent, {
          padding: [56, 56, 56, 56],
          maxZoom: 15.2,
          duration: 450
        });
      };
      requestAnimationFrame(function () {
        fit();
        setTimeout(fit, 160);
      });
    });
  }

  function renderItinerary() {
    var list = document.querySelector("[data-itinerary-list]");
    var items = readItinerary();
    document.querySelectorAll("[data-itinerary-count]").forEach(function (count) {
      count.textContent = String(items.length);
    });
    if (!list) return;
    list.innerHTML = "";
    items.forEach(function (stop, i) {
      var li = document.createElement("li");
      li.innerHTML = "<b>" + esc(stop.title) + "</b><span>" + esc(formatCoords(stop.lat, stop.lng)) + "</span>" +
        "<button type=\"button\" class=\"place-map-btn\" data-open-map=\"" + esc(stop.id) + "\" title=\"View on map\" aria-label=\"View " + esc(stop.title) + " on the map\">" + MAP_PIN + "</button>" +
        "<button type=\"button\" class=\"itin-remove\" data-remove-itinerary=\"" + esc(stop.id) + "\" aria-label=\"Remove " + esc(stop.title) + "\">Remove</button>";
      li.style.order = String(i);
      list.appendChild(li);
    });
    var empty = document.querySelector("[data-itinerary-empty]");
    if (empty) empty.hidden = items.length > 0;
    updateItineraryMap();
  }

  function addToItinerary(place) {
    if (!place) return;
    var items = readItinerary();
    if (items.some(function (s) { return s.id === place.id; })) {
      itineraryStatus(place.title + " is already on your list.");
      renderItinerary();
      return;
    }
    if (items.length >= 20) {
      itineraryStatus("Twenty stops is the cap for this printed list.");
      return;
    }
    items.push({
      id: place.id,
      title: place.title,
      blurb: place.blurb || "",
      lat: place.lat,
      lng: place.lng,
      tourLabel: place.tourLabel || "",
      kinds: placeKinds(place)
    });
    writeItinerary(items);
    renderItinerary();
    itineraryStatus("Added " + place.title + ".");
    showMapTab("list");
  }

  function removeFromItinerary(id) {
    writeItinerary(readItinerary().filter(function (s) { return s.id !== id; }));
    renderItinerary();
    itineraryStatus("Removed.");
  }

  function itineraryPlainText(items) {
    var lines = [
      "Walkridge Battlefield Tours",
      "Gettysburg, PA field itinerary",
      "Sample ticket office: 100 Sample Street, Gettysburg, PA 17325 (concept)",
      "tours@walkridge.test · (717) 555-0100",
      ""
    ];
    items.forEach(function (stop, i) {
      lines.push((i + 1) + ". " + stop.title);
      if (isFinite(Number(stop.lat))) lines.push("   " + formatCoords(stop.lat, stop.lng));
      if (stop.blurb) lines.push("   " + stop.blurb);
      if (stop.tourLabel) lines.push("   Tour: " + stop.tourLabel);
      lines.push("");
    });
    lines.push("This itinerary was built with Walkridge Field Map for WordPress. Visit " + ((window.hgfmConfig && window.hgfmConfig.toursUrl) || "our tours page") + " to book.");
    return lines.join("\n");
  }

  function downloadItineraryPdf() {
    var items = readItinerary();
    if (!items.length) {
      itineraryStatus("Add a stop from a popup first.");
      return;
    }
    var jsPdfNs = window.jspdf;
    if (jsPdfNs && jsPdfNs.jsPDF) {
      var doc = new jsPdfNs.jsPDF({ unit: "pt", format: "letter" });
      var y = 48;
      var wrap = function (text, x, max) {
        var lines = doc.splitTextToSize(text, max);
        lines.forEach(function (line) {
          if (y > 740) {
            doc.addPage();
            y = 48;
          }
          doc.text(line, x, y);
          y += 14;
        });
      };
      doc.setFont("times", "bold");
      doc.setFontSize(16);
      wrap("Walkridge Battlefield Tours", 48, 514);
      doc.setFont("times", "normal");
      doc.setFontSize(11);
      wrap("Gettysburg, Pennsylvania — guest field itinerary", 48, 514);
      y += 6;
      wrap("Sample ticket office: 100 Sample Street, Gettysburg, PA 17325 (concept placeholder).", 48, 514);
      wrap("tours@walkridge.test · (717) 555-0100", 48, 514);
      y += 8;
      items.forEach(function (stop, i) {
        doc.setFont("times", "bold");
        wrap((i + 1) + ". " + stop.title, 48, 514);
        doc.setFont("times", "normal");
        if (isFinite(Number(stop.lat))) wrap(formatCoords(stop.lat, stop.lng), 64, 498);
        if (stop.blurb) wrap(stop.blurb, 64, 498);
        if (stop.tourLabel) wrap("Related tour: " + stop.tourLabel, 64, 498);
        y += 6;
      });
      doc.save("gettysburg-field-itinerary.pdf");
      itineraryStatus("Saved gettysburg-field-itinerary.pdf to this device.");
      return;
    }
    printItinerary();
  }

  function printItinerary() {
    var items = readItinerary();
    if (!items.length) {
      itineraryStatus("Add a stop from a popup first.");
      return;
    }
    var win = window.open("", "itinprint");
    if (!win) {
      itineraryStatus("Allow pop-ups to print, or use Save as PDF.");
      return;
    }
    win.document.write("<!DOCTYPE html><html><head><title>Gettysburg field itinerary</title>");
    win.document.write("<style>body{font:14px/1.45 Georgia,serif;padding:32px;color:#14100a} h1{font-size:22px} ol{padding-left:1.2rem} .coords{font-family:monospace;font-size:12px}</style></head><body>");
    win.document.write("<h1>Walkridge Battlefield Tours</h1><p>Gettysburg, PA field itinerary</p>");
    win.document.write("<p>Sample ticket office: 100 Sample Street, Gettysburg, PA 17325 (concept).<br>tours@walkridge.test · (717) 555-0100</p><ol>");
    items.forEach(function (stop) {
      win.document.write("<li><strong>" + esc(stop.title) + "</strong>");
      if (isFinite(Number(stop.lat))) win.document.write("<div class=\"coords\">" + esc(formatCoords(stop.lat, stop.lng)) + "</div>");
      if (stop.blurb) win.document.write("<p>" + esc(stop.blurb) + "</p>");
      win.document.write("</li>");
    });
    win.document.write("</ol></body></html>");
    win.document.close();
    win.focus();
    win.print();
  }

  function openMapOverlay(id) {
    var overlay = document.querySelector("[data-map-overlay]");
    var stage = document.querySelector("[data-diorama][data-diorama-mode='view']");
    if (stage && id) stage._pendingSelect = id;
    if (!overlay) {
      if (stage && stage._hgSelect && id) stage._hgSelect(id);
      return;
    }
    overlay.hidden = false;
    overlay.classList.add("is-open");
    document.body.classList.add("modal-locked");
    var run = function () {
      if (stage && stage._olmap) stage._olmap.updateSize();
      var pick = id || (stage && stage._pendingSelect);
      if (pick && stage && stage._hgSelect) {
        stage._hgSelect(pick);
        stage._pendingSelect = "";
      }
    };
    requestAnimationFrame(function () {
      run();
      setTimeout(run, 80);
      setTimeout(run, 280);
      setTimeout(run, 600);
    });
  }

  function closeMapOverlay() {
    var overlay = document.querySelector("[data-map-overlay]");
    if (!overlay) return;
    overlay.classList.remove("is-open");
    overlay.hidden = true;
    document.body.classList.remove("modal-locked");
  }

  function decorateMapPlaces() {
    document.querySelectorAll("[data-map-place]").forEach(function (el) {
      if (el.querySelector("[data-open-map]")) return;
      var id = el.getAttribute("data-map-place");
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "inline-map-btn";
      btn.setAttribute("data-open-map", id);
      btn.setAttribute("title", "View on map");
      btn.setAttribute("aria-label", "View " + String(el.textContent || "this place").trim() + " on the map");
      btn.innerHTML = MAP_PIN;
      el.appendChild(btn);
    });
    document.querySelectorAll(".inline-map-btn[data-open-map]:empty").forEach(function (btn) {
      btn.innerHTML = MAP_PIN;
    });
  }

  function bindMapOverlay() {
    var overlay = document.querySelector("[data-map-overlay]");
    if (!overlay || overlay.dataset.bound) return;
    overlay.dataset.bound = "1";
    var closer = overlay.querySelector("[data-map-overlay-close]");
    if (closer) closer.addEventListener("click", closeMapOverlay);
    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) closeMapOverlay();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape" || !overlay.classList.contains("is-open")) return;
      if (overlay.querySelector(".ol-popup.is-open")) return;
      closeMapOverlay();
    });
    document.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-open-map]");
      if (!btn) return;
      e.preventDefault();
      openMapOverlay(btn.getAttribute("data-open-map") || "");
    });
  }

  function showMapTab(name) {
    var root = document.querySelector("[data-map-under]");
    if (!root || !name) return;
    root.querySelectorAll("[data-map-tab]").forEach(function (t) {
      var on = t.getAttribute("data-map-tab") === name;
      t.classList.toggle("is-active", on);
      t.setAttribute("aria-selected", on ? "true" : "false");
    });
    root.querySelectorAll("[data-map-panel]").forEach(function (p) {
      p.hidden = p.getAttribute("data-map-panel") !== name;
    });
  }

  function bindMapTabs() {
    var root = document.querySelector("[data-map-under]");
    if (!root || root.dataset.tabBound) return;
    root.dataset.tabBound = "1";
    root.addEventListener("click", function (e) {
      var tab = e.target.closest("[data-map-tab]");
      if (tab) showMapTab(tab.getAttribute("data-map-tab"));
    });
  }

  function bindItineraryUi() {
    var root = document.querySelector("[data-itinerary]");
    if (!root || root.dataset.bound) return;
    root.dataset.bound = "1";
    renderItinerary();
    root.addEventListener("click", function (e) {
      var rm = e.target.closest("[data-remove-itinerary]");
      if (rm) {
        e.preventDefault();
        removeFromItinerary(rm.getAttribute("data-remove-itinerary"));
      }
    });
    if (!document.documentElement.dataset.itinAdd) {
      document.documentElement.dataset.itinAdd = "1";
      document.addEventListener("click", function (e) {
        var addBtn = e.target.closest("[data-add-itinerary]");
        if (!addBtn) return;
        e.preventDefault();
        var host = document.querySelector("[data-diorama]");
        var pool = (host && host._allPlaces) || placePool || [];
        var place = pool.filter(function (p) { return p.id === addBtn.getAttribute("data-add-itinerary"); })[0];
        if (place) addToItinerary(place);
      });
    }
    var pdfBtn = root.querySelector("[data-itinerary-pdf]");
    var printBtn = root.querySelector("[data-itinerary-print]");
    if (pdfBtn) pdfBtn.addEventListener("click", function (e) { e.preventDefault(); downloadItineraryPdf(); });
    if (printBtn) printBtn.addEventListener("click", function (e) { e.preventDefault(); printItinerary(); });
    var form = root.querySelector("[data-itinerary-mail]");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var items = readItinerary();
        if (!items.length) {
          itineraryStatus("Add at least one stop before requesting mail.");
          return;
        }
        var bodyField = form.querySelector("[name=itinerary]");
        if (bodyField) bodyField.value = itineraryPlainText(items);
        var payload = new URLSearchParams(new FormData(form)).toString();
        fetch("/", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: payload
        }).then(function (res) {
          if (!res.ok) throw new Error("mail-error");
          itineraryStatus("Mail request sent.");
          form.reset();
        }).catch(function () {
          var mail = "mailto:tours@walkridge.test?subject=" + encodeURIComponent("Field itinerary mail request") +
            "&body=" + encodeURIComponent(itineraryPlainText(items) + "\n\nName: " + (form.guest_name && form.guest_name.value) +
              "\nEmail: " + (form.email && form.email.value) +
              "\nAddress: " + (form.street && form.street.value) + ", " + (form.city && form.city.value) + " " + (form.region && form.region.value) + " " + (form.postal && form.postal.value));
          window.location.href = mail;
          itineraryStatus("Opened your email client with the itinerary pre-filled. Send it to tours@walkridge.test.");
        });
      });
    }
  }

  function setPose(world, yaw, pitch) {
    world.style.setProperty("--yaw", yaw + "deg");
    world.style.setProperty("--pitch", pitch + "deg");
    world.style.setProperty("--yaw-inv", (-yaw) + "deg");
    world.style.setProperty("--pitch-inv", (-pitch) + "deg");
  }

  function bindOrbit(stage) {
    if (stage.dataset.orbitBound) return;
    stage.dataset.orbitBound = "1";
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var yaw = -24;
    var pitch = reduce ? 72 : 60;
    var world = function () { return stage.querySelector("[data-dio-world]"); };
    stage._yaw = yaw;
    stage._pitch = pitch;
    stage._applyPose = function () {
      var w = world();
      if (w) setPose(w, stage._yaw, stage._pitch);
    };
    stage._applyPose();
    if (reduce) return;
    if (stage.getAttribute("data-diorama-mode") === "edit") return;

    var dragging = false;
    var sx = 0;
    var sy = 0;
    var startYaw = yaw;
    var startPitch = pitch;
    stage._yaw = yaw;
    stage._pitch = pitch;
    stage._applyPose = function () {
      var w = world();
      if (w) setPose(w, stage._yaw, stage._pitch);
    };
    stage._applyPose();

    stage.addEventListener("pointerdown", function (e) {
      if (e.target.closest(".dio-pin, button, a, input, select, textarea, [data-diorama-list]")) return;
      dragging = true;
      stage.classList.add("is-dragging");
      sx = e.clientX;
      sy = e.clientY;
      startYaw = yaw;
      startPitch = pitch;
      stage.setPointerCapture(e.pointerId);
    });
    stage.addEventListener("pointermove", function (e) {
      if (!dragging) return;
      yaw = startYaw + (e.clientX - sx) * 0.28;
      pitch = Math.max(42, Math.min(78, startPitch + (sy - e.clientY) * 0.18));
      stage._yaw = yaw;
      stage._pitch = pitch;
      stage._applyPose();
    });
    var stop = function () {
      dragging = false;
      stage.classList.remove("is-dragging");
    };
    stage.addEventListener("pointerup", stop);
    stage.addEventListener("pointercancel", stop);
  }

  function highlight(root, id) {
    root.querySelectorAll("[data-patch]").forEach(function (el) {
      el.classList.toggle("is-lit", el.getAttribute("data-patch") === id);
    });
    root.querySelectorAll(".dio-pin").forEach(function (el) {
      el.classList.toggle("is-active", el.getAttribute("data-id") === id);
    });
    root.querySelectorAll("[data-diorama-list] button").forEach(function (el) {
      el.classList.toggle("is-active", el.getAttribute("data-id") === id);
    });
  }

  function fillDetail(panel, place) {
    if (!panel) return;
    if (!place) {
      panel.innerHTML = "<p class=\"lede\">Click a monument for name, coordinates, and a public-domain photo when one is on file.</p>";
      return;
    }
    panel.innerHTML = placeCardHTML(place);
  }

  function mountCss(stage, places, opts) {
    opts = opts || {};
    stage.classList.remove("is-google");
    stage.classList.remove("is-ol");
    var mode = stage.getAttribute("data-diorama-mode") || "view";
    stage.innerHTML = terrainHTML();
    var world = stage.querySelector("[data-dio-world]");
    var pinBox = stage.querySelector("[data-diorama-pins]");
    var list = document.querySelector("[data-diorama-list]");
    var detail = document.querySelector("[data-diorama-detail]");
    var visible = places.filter(function (p) { return p.visible !== false; });

    pinBox.innerHTML = "";
    visible.forEach(function (place) {
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "dio-pin cat-" + place.category;
      btn.setAttribute("data-id", place.id);
      btn.style.setProperty("--x", place.x);
      btn.style.setProperty("--z", place.z);
      btn.style.setProperty("--elev", place.elev);
      btn.innerHTML = "<span class=\"dio-stem\"></span><span class=\"dio-head\"></span><span class=\"dio-label\">" + esc(place.title) + "</span>";
      btn.setAttribute("aria-label", place.title);
      pinBox.appendChild(btn);
    });

    if (list) {
      list.innerHTML = "";
      visible.forEach(function (place) {
        var li = document.createElement("li");
        var b = document.createElement("button");
        b.type = "button";
        b.setAttribute("data-id", place.id);
        b.innerHTML = "<b>" + esc(place.title) + "</b><span>" + esc(catLabel(place.category)) + "</span>";
        li.appendChild(b);
        list.appendChild(li);
      });
    }

    var select = function (id) {
      var place = places.filter(function (p) { return p.id === id; })[0];
      highlight(document, id);
      fillDetail(detail, place);
      if (opts.onSelect) opts.onSelect(place);
    };

    stage._hgSelect = select;
    if (!stage.dataset.selectBound) {
      stage.dataset.selectBound = "1";
      stage.addEventListener("click", function (e) {
        var pin = e.target.closest(".dio-pin");
        if (pin && stage._hgSelect) stage._hgSelect(pin.getAttribute("data-id"));
      });
    }
    if (list && !list.dataset.selectBound) {
      list.dataset.selectBound = "1";
      list.addEventListener("click", function (e) {
        var b = e.target.closest("button[data-id]");
        var host = document.querySelector("[data-diorama]");
        if (b && host && host._hgSelect) host._hgSelect(b.getAttribute("data-id"));
      });
    }

    bindOrbit(stage);
    if (stage._applyPose) stage._applyPose();
    else if (world) setPose(world, -24, 60);
    if (visible[0] && mode === "view" && !opts.skipAuto) select(visible[0].id);
    return { select: select, world: world };
  }

  function renderView(stage, places, opts, done) {
    opts = opts || {};
    var api = {
      select: function (id) {
        if (stage._hgSelect) stage._hgSelect(id);
        else stage._pendingSelect = id;
      }
    };
    loadMapsConfig(function (cfg) {
      var finish = function (real) {
        if (real && real.select) api.select = real.select;
        if (stage._pendingSelect && api.select) api.select(stage._pendingSelect);
        if (done) done(api, cfg);
      };
      if (stage._olmap && stage._refreshPlaces) {
          stage._refreshPlaces(places);
          finish({ select: stage._hgSelect });
          return;
        }
        mountOpenLayers(stage, places, opts, cfg, finish);
      });
    return api;
  }

  function slugify(str) {
    return String(str || "place")
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-|-$/g, "") || "place";
  }

  function uniqueId(base, places) {
    var id = base;
    var n = 2;
    var ids = places.map(function (p) { return p.id; });
    while (ids.indexOf(id) !== -1) {
      id = base + "-" + n;
      n += 1;
    }
    return id;
  }

  function initView() {
    var stage = document.querySelector("[data-diorama][data-diorama-mode='view']");
    if (!stage) return;
    loadPlaces(function (places) {
      loadMonuments(function (monuments) {
        bindItineraryUi();
        bindMapTabs();
        bindMapOverlay();
        decorateMapPlaces();
        renderView(stage, places, { monuments: monuments });
      });
    });
  }

  function initAdmin() {
    var gate = document.querySelector("[data-admin-gate]");
    var app = document.querySelector("[data-admin-app]");
    if (!gate || !app) return;

    var unlocked = sessionStorage.getItem(ADMIN_GATE) === "1";
    var show = function (on) {
      gate.hidden = on;
      app.hidden = !on;
    };
    show(unlocked);

    var enter = document.getElementById("adminEnter");
    if (enter) {
      enter.addEventListener("click", function () {
        sessionStorage.setItem(ADMIN_GATE, "1");
        show(true);
        setTimeout(function () {
          if (stage._olmap) stage._olmap.updateSize();
        }, 80);
      });
    }

    var stage = document.querySelector("[data-diorama][data-diorama-mode='edit']");
    var form = document.getElementById("placeForm");
    var status = document.querySelector("[data-admin-status]");
    var setStatus = function (msg) { if (status) status.textContent = msg || ""; };

    loadPlaces(function (places) {
        var selectedId = places[0] && places[0].id;
      var api;
      var mapsCfg;

      var onMapClick = function (pos) {
        var place = current();
        if (!place || pos.lat == null || pos.lng == null) return;
        place.lat = Number(pos.lat);
        place.lng = Number(pos.lng);
        fillForm(place);
        if (stage._refreshPlaces) stage._refreshPlaces(places);
        setStatus("Moved “" + place.title + "” to " + Number(place.lat).toFixed(5) + ", " + Number(place.lng).toFixed(5) + ". Save to keep it.");
      };

      var paint = function () {
        loadMonuments(function (monuments) {
          api = renderView(stage, places, {
            skipAuto: true,
            monuments: monuments,
            onSelect: function (place) {
              if (!places.some(function (p) { return p.id === place.id; })) return;
              selectedId = place.id;
              fillForm(place);
              renderRows();
            },
            onMapClick: onMapClick
          }, function (real, cfg) {
            if (real) api = real;
            mapsCfg = cfg;
            fillMapsForm(cfg);
            if (selectedId && api && api.select) api.select(selectedId, false);
            renderRows();
          });
        });
      };

      function current() {
        return places.filter(function (p) { return p.id === selectedId; })[0];
      }

      function fillForm(place) {
        if (!form || !place) return;
        form.title.value = place.title;
        form.blurb.value = place.blurb;
        form.category.value = place.category;
        form.tourHref.value = place.tourHref || "";
        form.tourLabel.value = place.tourLabel || "";
        if (form.lat) form.lat.value = place.lat;
        if (form.lng) form.lng.value = place.lng;
        if (form.x) form.x.value = place.x;
        if (form.z) form.z.value = place.z;
        if (form.elev) form.elev.value = place.elev;
        form.visible.checked = place.visible !== false;
      }

      function fillMapsForm(cfg) {
        var mf = document.getElementById("mapsConfigForm");
        if (!mf || !cfg) return;
        if (mf.zoom) mf.zoom.value = cfg.zoom;
        if (mf.rotation) mf.rotation.value = Math.round((cfg.rotation || 0) * 180 / Math.PI);
      }

      function readMapsForm() {
        var mf = document.getElementById("mapsConfigForm");
        if (!mf) return mapsCfg;
        var rotDeg = Number(mf.rotation && mf.rotation.value);
        mapsCfg = mergeMaps(mapsCfg || DEFAULT_MAPS, {
          zoom: Number(mf.zoom && mf.zoom.value),
          rotation: isFinite(rotDeg) ? rotDeg * Math.PI / 180 : 0
        });
        if (stage._olmap) {
          var view = stage._olmap.getView();
          var center = ol.proj.toLonLat(view.getCenter());
          mapsCfg.center = { lat: center[1], lng: center[0] };
          mapsCfg.zoom = view.getZoom();
          mapsCfg.rotation = view.getRotation();
        }
        return mapsCfg;
      }

      function readForm() {
        var place = current();
        if (!place) return;
        place.title = form.title.value.trim() || place.title;
        place.blurb = form.blurb.value.trim();
        place.category = form.category.value;
        place.tourHref = form.tourHref.value.trim();
        place.tourLabel = form.tourLabel.value.trim();
        if (form.lat) place.lat = Number(form.lat.value);
        if (form.lng) place.lng = Number(form.lng.value);
        if (form.x) place.x = Number(form.x.value);
        if (form.z) place.z = Number(form.z.value);
        if (form.elev) place.elev = Number(form.elev.value);
        place.visible = form.visible.checked;
      }

      function renderRows() {
        var tbody = document.querySelector("[data-place-rows]");
        if (!tbody) return;
        tbody.innerHTML = "";
        places.forEach(function (place) {
          var tr = document.createElement("tr");
          if (place.id === selectedId) tr.className = "is-selected";
          tr.innerHTML =
            "<td><button type=\"button\" data-pick=\"" + esc(place.id) + "\">" + esc(place.title) + "</button></td>" +
            "<td>" + esc(catLabel(place.category)) + "</td>" +
            "<td>" + (place.lat ? Number(place.lat).toFixed(4) : "—") + ", " + (place.lng ? Number(place.lng).toFixed(4) : "—") + "</td>" +
            "<td>" + (place.visible !== false ? "On" : "Off") + "</td>";
          tbody.appendChild(tr);
        });
      }

      document.addEventListener("click", function (e) {
        var pick = e.target.closest("[data-pick]");
        if (pick) {
          selectedId = pick.getAttribute("data-pick");
          api.select(selectedId);
          fillForm(current());
          renderRows();
        }
      });

      stage.addEventListener("click", function (e) {
        if (!e.target.closest(".dio-board")) return;
        if (e.target.closest(".dio-pin")) return;
        var board = stage.querySelector(".dio-board");
        var rect = board.getBoundingClientRect();
        /* Approximate placement from screen click; owner can nudge with number fields. */
        var x = ((e.clientX - rect.left) / rect.width) * 100;
        var z = ((e.clientY - rect.top) / rect.height) * 100;
        var place = current();
        if (!place) return;
        place.x = Math.max(4, Math.min(96, x));
        place.z = Math.max(4, Math.min(96, z));
        fillForm(place);
        paint();
        setStatus("Moved “" + place.title + "” on the table. Save to keep it.");
      });

      form.addEventListener("input", function () {
        readForm();
        if (stage._refreshPlaces) stage._refreshPlaces(places);
        renderRows();
      });
      form.addEventListener("change", function () {
        readForm();
        if (stage._refreshPlaces) stage._refreshPlaces(places);
        else paint();
        renderRows();
      });

      document.getElementById("saveMap").addEventListener("click", function () {
        readForm();
        var mapsCfgToSave = readMapsForm();
        // WP REST API save (falls back to localStorage if not in WP context)
        var placesUrl   = window.hgfmConfig && window.hgfmConfig.endpoints && window.hgfmConfig.endpoints.savePlaces;
        var mapsUrl     = window.hgfmConfig && window.hgfmConfig.endpoints && window.hgfmConfig.endpoints.saveMaps;
        var wpNonce     = window.hgfmConfig && window.hgfmConfig.nonce;
        if (placesUrl && mapsUrl) {
          Promise.all([
            fetch(placesUrl, { method: "PUT", headers: { "Content-Type": "application/json", "X-WP-Nonce": wpNonce || "" }, body: JSON.stringify({ places: places }) }),
            fetch(mapsUrl,   { method: "PUT", headers: { "Content-Type": "application/json", "X-WP-Nonce": wpNonce || "" }, body: JSON.stringify(mapsCfgToSave) })
          ]).then(function (responses) {
            if (responses.every(function (r) { return r.ok; })) {
              setStatus("Saved to WordPress. The Area page will use these pins.");
            } else {
              setStatus("Save failed. Check your login and try again.");
            }
          }).catch(function () { setStatus("Network error — could not save."); });
        } else {
          // Fallback for non-WP context
          writeStored(places);
          writeMapsStored(mapsCfgToSave);
          setStatus("Saved on this browser.");
        }
        if (stage._refreshPlaces) stage._refreshPlaces(places);
      });

      document.getElementById("resetMap").addEventListener("click", function () {
        localStorage.removeItem(STORAGE);
        places = clonePlaces(DEFAULTS.places);
        selectedId = places[0].id;
        setStatus("Reset to the default field. Save if you want this to stick.");
        paint();
        fillForm(current());
      });

      document.getElementById("addPlace").addEventListener("click", function () {
        var id = uniqueId(slugify("new-place"), places);
        var place = {
          id: id,
          title: "New place",
          blurb: "Short, specific copy for this pin.",
          category: "ridge",
          tourHref: (window.hgfmConfig && window.hgfmConfig.toursUrl || "tours.html"),
          tourLabel: "See tours",
          lat: 39.83,
          lng: -77.231,
          x: 50,
          z: 50,
          elev: 20,
          visible: true
        };
        places.push(place);
        selectedId = id;
        paint();
        fillForm(place);
        setStatus("Added a pin. Rename it, place it, then save.");
      });

      document.getElementById("deletePlace").addEventListener("click", function () {
        if (places.length < 2) {
          setStatus("Keep at least one place on the table.");
          return;
        }
        places = places.filter(function (p) { return p.id !== selectedId; });
        selectedId = places[0].id;
        paint();
        fillForm(current());
        setStatus("Removed that pin. Save to keep the change.");
      });

      document.getElementById("exportMap").addEventListener("click", function () {
        readForm();
        var blob = new Blob([JSON.stringify({ version: 1, places: places }, null, 2)], { type: "application/json" });
        var a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "area-map.json";
        a.click();
        setStatus("Downloaded JSON. In Sage, this maps to an ACF export / theme option.");
      });

      paint();
      fillForm(current());
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      initView();
      initAdmin();
    });
  } else {
    initView();
    initAdmin();
  }
})();
