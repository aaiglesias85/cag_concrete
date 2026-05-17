/**
 * Widget "Measurements" del dashboard home.
 *
 * - Mapa Google Maps con burbujas por County (centroide del County).
 * - Panel lateral con empleados (color, %, acordeón con tabla de proyectos).
 * - Filtros locales propios (project + fecha desde/hasta) independientes del filtro global.
 *
 * Datos iniciales: ventana global window.HomeMeasurementsData (inyectada por twig).
 * Endpoint refresh: window.HomeMeasurementsUrl (POST con project_id, fechaInicial, fechaFin).
 */
var MeasurementsWidget = (function () {
   var data = null;              // payload actual {counties, employees, projects, total_projects}
   var map = null;               // google.maps.Map
   var markers = [];             // burbujas activas en el mapa
   var infoWindow = null;
   var activeEmployeeId = null;  // empleado actualmente filtrado en el mapa
   var expandedEmployees = {};   // empleado_id -> bool (acordeón abierto)

   var DEFAULT_CENTER = { lat: 32.9866, lng: -83.6487 }; // ~centro Georgia
   var DEFAULT_ZOOM = 7;
   var NEUTRAL_COLOR = "#3699FF"; // azul Metronic para vista sin filtro

   // Color determinístico por nombre (mismo nombre = mismo color siempre).
   var colorForName = function (name) {
      var s = String(name || "").trim().toLowerCase();
      if (!s) return "#888888";
      var h = 0;
      for (var i = 0; i < s.length; i++) {
         h = (h * 31 + s.charCodeAt(i)) | 0;
      }
      var hue = Math.abs(h) % 360;
      return "hsl(" + hue + ", 65%, 50%)";
   };

   // -------------------------------------------------------------------------
   // Inicialización
   // -------------------------------------------------------------------------
   var init = function () {
      var root = document.getElementById("measurements-widget");
      if (!root) {
         return; // el widget no está visible en este dashboard
      }
      data = window.HomeMeasurementsData || emptyPayload();

      initUnifiedFilter();
      bindEvents();

      // Esperar a que Google Maps cargue antes de renderizar
      waitForGoogleMaps(function () {
         renderMap();
         renderFromData();
      });
   };

   var emptyPayload = function () {
      return {
         counties: [],
         employees: [],
         projects: [],
         total_projects: 0,
         range: { inicial: "", final: "" },
         project_id: "",
      };
   };

   var waitForGoogleMaps = function (cb) {
      if (typeof google !== "undefined" && google.maps && google.maps.Map) {
         cb();
         return;
      }
      var tries = 0;
      var t = setInterval(function () {
         tries++;
         if (typeof google !== "undefined" && google.maps && google.maps.Map) {
            clearInterval(t);
            cb();
         } else if (tries >= 40) {
            clearInterval(t);
            var summary = document.getElementById("measurements-summary");
            if (summary) {
               summary.textContent = "Google Maps could not be loaded.";
            }
         }
      }, 500);
   };

   // -------------------------------------------------------------------------
   // Filtros locales (mismo patrón que widget Project Breakdown)
   // -------------------------------------------------------------------------
   var activePeriod = "current_month";

   var fmtDate = function (d) {
      var m = String(d.getMonth() + 1).padStart(2, "0");
      var day = String(d.getDate()).padStart(2, "0");
      return m + "/" + day + "/" + d.getFullYear();
   };

   var getMonthBounds = function (offset) {
      var now = new Date();
      var first = new Date(now.getFullYear(), now.getMonth() + offset, 1);
      var last = new Date(now.getFullYear(), now.getMonth() + offset + 1, 0);
      return {
         fi: fmtDate(first),
         ff: fmtDate(last),
         label: first.toLocaleDateString("en-US", { month: "short", year: "numeric" }),
      };
   };

   var updateFilterLabel = function (periodLabel, projText) {
      var $label = $("#measurements-filter-label");
      var $btn = $("#measurements-filter-btn");
      $label.text(periodLabel + (projText ? " · " + projText : ""));
      var active = !!projText || periodLabel !== "Current month";
      $btn.toggleClass("btn-light", !active);
      $btn.toggleClass("btn-light-primary", active);
   };

   var initUnifiedFilter = function () {
      var $periodSel = $("#measurements-period-select");
      var $customRow = $("#measurements-custom-range");

      // Mostrar/ocultar rango custom
      $periodSel.off("change.measurements").on("change.measurements", function () {
         $customRow.css("display", this.value === "custom" ? "" : "none");
      });

      // Select2 AJAX para proyectos (mismo endpoint que Project Breakdown)
      if ($.fn && $.fn.select2) {
         $("#measurements-filter-project-select").select2({
            placeholder: "Search project…",
            allowClear: true,
            width: "100%",
            dropdownParent: $("#measurements-filter-menu"),
            ajax: {
               url: "project/listarOrdenados",
               dataType: "json",
               delay: 250,
               data: function (p) { return { search: p.term }; },
               processResults: function (d) {
                  return {
                     results: $.map(d.projects || [], function (i) {
                        return { id: i.project_id, text: (i.number || "") + " - " + (i.description || "") };
                     }),
                  };
               },
               cache: true,
            },
            minimumInputLength: 3,
         });
      }

      // Inicializar label desde el rango actual del payload
      if (data && data.range && data.range.inicial && data.range.final) {
         updateFilterLabel("Current month", "");
      }
   };

   var bindEvents = function () {
      // Apply
      $(document).off("click", "#measurements-btn-apply");
      $(document).on("click", "#measurements-btn-apply", function () {
         var period = $("#measurements-period-select").val() || "current_month";
         var fi = "";
         var ff = "";
         var periodLabel = "Current month";

         if (period === "all") {
            periodLabel = "All time";
         } else if (period === "current_month") {
            var cm = getMonthBounds(0);
            fi = cm.fi; ff = cm.ff; periodLabel = cm.label;
         } else if (period === "last_month") {
            var lm = getMonthBounds(-1);
            fi = lm.fi; ff = lm.ff; periodLabel = lm.label;
         } else if (period === "custom") {
            var fiIso = $("#measurements-date-from").val();
            var ffIso = $("#measurements-date-to").val();
            if (fiIso && ffIso) {
               var fp = fiIso.split("-"); fi = fp[1] + "/" + fp[2] + "/" + fp[0];
               var tp = ffIso.split("-"); ff = tp[1] + "/" + tp[2] + "/" + tp[0];
               periodLabel = fi + " – " + ff;
            } else {
               return; // fechas incompletas
            }
         }

         // Capturar proyecto antes de que KTMenu cierre el dropdown
         var $proj = $("#measurements-filter-project-select");
         var projectId = $proj.val() || "";
         var projText = "";
         if (projectId) {
            var sel = $proj.select2("data");
            if (sel && sel[0]) {
               projText = (sel[0].text || "").split(" - ")[0].trim();
            }
         }

         activePeriod = period;
         updateFilterLabel(periodLabel, projText);
         reloadFromServer(projectId, fi, ff);
      });

      // Reset
      $(document).off("click", "#measurements-btn-reset");
      $(document).on("click", "#measurements-btn-reset", function () {
         $("#measurements-period-select").val("current_month");
         $("#measurements-custom-range").css("display", "none");
         $("#measurements-filter-project-select").val(null).trigger("change");
         var cm = getMonthBounds(0);
         activePeriod = "current_month";
         updateFilterLabel(cm.label, "");
         reloadFromServer("", cm.fi, cm.ff);
      });

      $(document).off("click", "#measurements-clear-employee-filter");
      $(document).on("click", "#measurements-clear-employee-filter", function () {
         setActiveEmployee(null);
      });

      $(document).off("click", "#measurements-employees-list .employee-row-header");
      $(document).on("click", "#measurements-employees-list .employee-row-header", function (e) {
         var empId = parseInt($(this).data("employee-id"), 10);
         if (!empId) return;

         // Si clickearon el badge del color, sólo filtran el mapa (sin abrir acordeón)
         if ($(e.target).closest(".employee-color-toggle").length) {
            setActiveEmployee(activeEmployeeId === empId ? null : empId);
            return;
         }

         // Toggle acordeón
         expandedEmployees[empId] = !expandedEmployees[empId];
         var $body = $(this).closest(".employee-row").find(".employee-body");
         $body.toggleClass("d-none", !expandedEmployees[empId]);
         $(this).find(".employee-chevron").toggleClass("rotated", !!expandedEmployees[empId]);
      });
   };

   var reloadFromServer = function (projectId, fIni, fFin) {
      var formData = new URLSearchParams();
      formData.set("project_id", projectId || "");
      formData.set("fechaInicial", fIni || "");
      formData.set("fechaFin", fFin || "");

      if (typeof BlockUtil !== "undefined") {
         BlockUtil.block("#measurements-widget");
      }

      axios
         .post(window.HomeMeasurementsUrl, formData, { responseType: "json" })
         .then(function (res) {
            if (res.data && res.data.success && res.data.data) {
               data = res.data.data;
               activeEmployeeId = null;
               expandedEmployees = {};
               renderFromData();
            } else {
               toastr.error((res.data && res.data.error) || "Could not load measurements", "");
            }
         })
         .catch(function (err) {
            if (typeof MyUtil !== "undefined" && MyUtil.catchErrorAxios) {
               MyUtil.catchErrorAxios(err);
            } else {
               console.error(err);
            }
         })
         .then(function () {
            if (typeof BlockUtil !== "undefined") {
               BlockUtil.unblock("#measurements-widget");
            }
         });
   };

   // -------------------------------------------------------------------------
   // Mapa
   // -------------------------------------------------------------------------
   var renderMap = function () {
      var mapEl = document.getElementById("measurements-map");
      if (!mapEl || map) return;

      map = new google.maps.Map(mapEl, {
         center: DEFAULT_CENTER,
         zoom: DEFAULT_ZOOM,
         mapTypeControl: false,
         streetViewControl: false,
         fullscreenControl: true,
      });
      infoWindow = new google.maps.InfoWindow();
   };

   var clearMarkers = function () {
      markers.forEach(function (m) {
         m.setMap(null);
      });
      markers = [];
      if (infoWindow) infoWindow.close();
   };

   var renderMarkers = function () {
      clearMarkers();
      if (!map || !data) return;

      var view = computeMapView(); // [{county_id, name, lat, lng, count, color}]
      if (view.length === 0) {
         return;
      }

      var bounds = new google.maps.LatLngBounds();

      view.forEach(function (item) {
         if (item.lat == null || item.lng == null) return;

         var radius = scaleRadius(item.count);
         var icon = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: item.color,
            fillOpacity: 0.75,
            strokeColor: "#ffffff",
            strokeWeight: 2,
            scale: radius,
         };

         var marker = new google.maps.Marker({
            position: { lat: item.lat, lng: item.lng },
            map: map,
            icon: icon,
            label: {
               text: String(item.count),
               color: "#ffffff",
               fontSize: "11px",
               fontWeight: "bold",
            },
            title: item.name + (item.city ? " (" + item.city + ")" : ""),
         });

         marker.addListener("click", function () {
            var content =
               '<div style="min-width: 180px;">' +
               '<div class="fw-bold mb-1">' + escapeHtml(item.name) +
                  (item.city ? " <span class=\"text-muted\">(" + escapeHtml(item.city) + ")</span>" : "") +
               "</div>" +
               '<div class="text-muted small">' + (item.state_code ? item.state_code + " · " : "") +
                  item.count + " project" + (item.count === 1 ? "" : "s") +
               "</div>" +
               "</div>";
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
         });

         markers.push(marker);
         bounds.extend(marker.getPosition());
      });

      if (markers.length > 0) {
         map.fitBounds(bounds, 40);
         // Si solo hay un marker, no acercar tanto
         var listener = google.maps.event.addListenerOnce(map, "idle", function () {
            if (map.getZoom() > 11) {
               map.setZoom(11);
            }
         });
         setTimeout(function () {
            google.maps.event.removeListener(listener);
         }, 2000);
      }
   };

   /**
    * Construye la lista de burbujas a pintar según el empleado activo.
    *  - Sin empleado: burbuja neutra con el total de proyectos por County.
    *  - Con empleado: burbujas del color del empleado SÓLO en sus counties.
    */
   var computeMapView = function () {
      if (!data || !data.counties) return [];

      // Index de counties por id para tomar lat/lng/state_code
      var byId = {};
      data.counties.forEach(function (c) {
         byId[c.id] = c;
      });

      if (!activeEmployeeId) {
         return data.counties.map(function (c) {
            return {
               county_id: c.id,
               name: c.name,
               city: c.city,
               state_code: c.state_code,
               lat: c.lat,
               lng: c.lng,
               count: c.projects_count,
               color: NEUTRAL_COLOR,
            };
         });
      }

      var emp = (data.employees || []).find(function (e) {
         return e.id === activeEmployeeId;
      });
      if (!emp) return [];

      return (emp.counties || [])
         .map(function (ec) {
            var c = byId[ec.id];
            if (!c) return null;
            return {
               county_id: c.id,
               name: c.name,
               city: c.city,
               state_code: c.state_code,
               lat: c.lat,
               lng: c.lng,
               count: ec.count,
               color: colorForName(emp.name),
            };
         })
         .filter(Boolean);
   };

   var scaleRadius = function (count) {
      // 1 → 12 px, 50+ → ~28 px
      var r = 10 + Math.sqrt(Math.max(1, count)) * 3;
      return Math.min(28, Math.max(10, r));
   };

   var setActiveEmployee = function (empId) {
      activeEmployeeId = empId;
      renderMarkers();
      renderActiveFilterChip();
      // resaltar fila activa
      $("#measurements-employees-list .employee-row").removeClass("active");
      if (empId) {
         $('#measurements-employees-list .employee-row[data-employee-id="' + empId + '"]').addClass("active");
      }
   };

   var renderActiveFilterChip = function () {
      var $chip = $("#measurements-active-filter");
      if (!activeEmployeeId) {
         $chip.hide();
         return;
      }
      var emp = (data.employees || []).find(function (e) {
         return e.id === activeEmployeeId;
      });
      if (!emp) {
         $chip.hide();
         return;
      }
      $("#measurements-active-filter-name").text(emp.name);
      $chip.show();
   };

   // -------------------------------------------------------------------------
   // Panel empleados
   // -------------------------------------------------------------------------
   var renderEmployees = function () {
      var $list = $("#measurements-employees-list");
      $list.empty();

      $("#measurements-total-projects").text(data.total_projects || 0);

      if (!data.employees || data.employees.length === 0) {
         $list.append(
            '<div class="text-muted text-center py-5 fs-7">No employees in selected period.</div>'
         );
         return;
      }

      data.employees.forEach(function (emp) {
         $list.append(buildEmployeeRow(emp));
      });
   };

   var buildEmployeeRow = function (emp) {
      var color = colorForName(emp.name);
      var rowsHtml = (emp.rows || [])
         .map(function (r) {
            return (
               "<tr>" +
               '<td class="text-nowrap">' + escapeHtml(r.date) + "</td>" +
               "<td>" + escapeHtml(r.project_number || ("#" + r.project_id)) + "</td>" +
               "<td>" + escapeHtml(r.county || "—") + "</td>" +
               "</tr>"
            );
         })
         .join("");

      var bodyHtml =
         '<div class="employee-body d-none">' +
         '<table class="table table-sm table-row-bordered align-middle mt-2 mb-1 fs-8">' +
         '<thead><tr class="text-muted fw-semibold">' +
         "<th>Date</th><th>Project</th><th>County</th>" +
         "</tr></thead>" +
         "<tbody>" + (rowsHtml || '<tr><td colspan="3" class="text-muted">No rows</td></tr>') + "</tbody>" +
         "</table>" +
         "</div>";

      return (
         '<div class="employee-row border rounded p-2" data-employee-id="' + emp.id + '">' +
         '<div class="employee-row-header d-flex align-items-center justify-content-between" style="cursor: pointer;" data-employee-id="' + emp.id + '">' +
            '<div class="d-flex align-items-center gap-2 min-w-0">' +
               '<span class="employee-color-toggle" title="Filter map by this employee" ' +
                  'style="display:inline-block; width:14px; height:14px; border-radius:50%; background:' + color + '; flex-shrink:0; cursor:pointer; border:2px solid #fff; box-shadow:0 0 0 1px rgba(0,0,0,.15);"></span>' +
               '<span class="fw-semibold text-truncate" title="' + escapeHtml(emp.name) + '">' + escapeHtml(emp.name) + "</span>" +
            "</div>" +
            '<div class="d-flex align-items-center gap-2 flex-shrink-0">' +
               '<span class="badge badge-light-primary fs-8">' + emp.percentage + "%</span>" +
               '<i class="ki-duotone ki-down employee-chevron fs-3 text-muted"><span class="path1"></span><span class="path2"></span></i>' +
            "</div>" +
         "</div>" +
         bodyHtml +
         "</div>"
      );
   };

   // -------------------------------------------------------------------------
   // Helpers
   // -------------------------------------------------------------------------
   var renderFromData = function () {
      renderEmployees();
      renderMarkers();
      renderSummary();
      renderActiveFilterChip();
   };

   var renderSummary = function () {
      var total = data.total_projects || 0;
      var countiesCount = (data.counties || []).filter(function (c) {
         return c.lat != null && c.lng != null;
      }).length;
      var msg =
         total + " project" + (total === 1 ? "" : "s") + " · " +
         countiesCount + " county" + (countiesCount === 1 ? "" : "ies") + " on map";
      $("#measurements-summary").text(msg);

      // Diagnóstico cuando hay proyectos pero faltan burbujas o empleados.
      var dbg = data._debug || null;
      var $diag = $("#measurements-diagnostic");
      if (!dbg) {
         $diag.empty().hide();
         return;
      }
      var problems = [];
      if (dbg.projects_in_period > 0 && dbg.projects_with_county === 0) {
         problems.push(
            "None of the " + dbg.projects_in_period + " projects in this period have a county assigned " +
            "(set them in /admin/project)."
         );
      } else if (dbg.projects_with_county < dbg.projects_in_period) {
         problems.push(
            (dbg.projects_in_period - dbg.projects_with_county) +
            " of " + dbg.projects_in_period + " projects don't have a county assigned."
         );
      }
      if (dbg.counties_without_coords > 0) {
         problems.push(
            dbg.counties_without_coords + " county/ies in use don't have map coordinates " +
            "(add them in /admin/county)."
         );
      }
      if (dbg.field_measurements_total === 0) {
         problems.push(
            "There are no active users with the 'Field Measurements' profile. " +
            "Create them in /admin/profiles + assign the profile to a user."
         );
      } else if ((data.employees || []).length === 0 && dbg.projects_in_period > 0) {
         problems.push(
            "No Field Measurements user matched any DataTracking's 'measured_by' field " +
            "(the name in measured_by must match exactly: first name + last name)."
         );
      }

      if (problems.length === 0) {
         $diag.empty().hide();
         return;
      }
      var html =
         '<div class="alert alert-warning py-2 px-3 mb-0 mt-2 fs-8">' +
         '<i class="ki-duotone ki-information-2 fs-3 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>' +
         "<strong>Heads up:</strong> " +
         "<ul class=\"mb-0 ps-4\">" +
         problems.map(function (p) { return "<li>" + escapeHtml(p) + "</li>"; }).join("") +
         "</ul>" +
         "</div>";
      $diag.html(html).show();
   };

   var escapeHtml = function (s) {
      if (s == null) return "";
      return String(s)
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#39;");
   };

   return {
      init: init,
   };
})();

jQuery(function () {
   MeasurementsWidget.init();
});

// CSS mínimo para rotación del chevron
(function () {
   var style = document.createElement("style");
   style.textContent =
      ".employee-row.active { background-color: rgba(54,153,255,.08); border-color: #3699FF !important; }" +
      ".employee-chevron { transition: transform .15s; }" +
      ".employee-chevron.rotated { transform: rotate(180deg); }";
   document.head.appendChild(style);
})();
