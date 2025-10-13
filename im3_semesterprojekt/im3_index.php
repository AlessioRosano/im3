<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="initial-scale=1, width=device-width" />

  <!-- CSS: passe den Pfad an deine Struktur an -->
  <link rel="stylesheet" href="/im3_semesterprojekt/im3_styles.css" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />
</head>
<body>

  <div class="d-home">
    <div class="d-hero">
      <div class="d-text-hero">
        <b class="wie-viele-personen">Wie viele Personen gingen heute durch St. Gallen?</b>
        <div class="relativ-zur-stadiongrsse">relativ zur Stadiongrösse des Kybunparks</div>
      </div>

      <div class="d-grafik-hero">
        <div class="component-1">
          <!-- OPTIONAL: Ersetze src durch dein Fussballfeld-Bild -->
          <img class="component-1-item" src="/img/kybunpark.png" alt="Fussballfeld" />
          <div class="kybunpark">Kybunpark</div>
        </div>

        <!-- Live-Zähler -->
        <div class="personen-parent">
          <div class="personen" id="countText">– PERSONEN</div>
          <div class="personen" id="percentText">–%</div>
        </div>

        <!-- Dynamische Bälle (JS füllt diesen Container) -->
        <div class="d-random-balls" id="ballContainer"></div>
      </div>
    </div>

    <div class="d-passantenanzahl-datum">
      <div class="features">
        <div class="frame">
          <img class="d-grafik-daypicker-icon" alt="" />
          <div class="d-text-daypicker">
            <div class="d-home-wie-viele-personen">Wie viele Personen gingen durch die Stadt St. Gallen am…</div>
            <div class="d-home-frame">
              <div class="d-button-date" id="dButtonDate">
                <b class="jjjj-mm-dd">JJJJ-MM-DD</b>
              </div>
            </div>
            <div class="es-waren-xy-personen-am-jjjj-m-wrapper">
              <div class="es-waren-xy-container">
                <span>Es waren </span><b>XY</b><span> Personen am </span><b>JJJJ-MM-DD</b><span> unterwegs.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-footer">
      <div class="d-line-footer"></div>
      <div class="copywright-2025-sarina-container">
        <p class="sarina-da-ros">Copywright 2025</p>
        <p class="sarina-da-ros">Sarina Da Ros &amp; Alessio Rosano</p>
      </div>
    </div>
  </div>

  <!-- Popup Daypicker (dein ursprüngliches Markup beibehalten) -->
  <div id="dDaypickerContainer" class="popup-overlay" style="display:none">
    <div class="d-daypicker">
      <div class="date-wrapper"><b class="date">YYYY/MM/DD</b></div>
      <div class="date-parent">
        <div class="d-daypicker-date">
          <img class="navigate-left-icon" alt="" />
          <div class="selectordate"><div class="date2">Januar 2025</div></div>
          <img class="navigate-right-icon" alt="" />
        </div>
        <div class="calender">
          <div class="row">
            <div class="date-day"><b class="date3">MO</b></div>
            <div class="date-day"><b class="date4">DI</b></div>
            <div class="date-day"><b class="date5">MI</b></div>
            <div class="date-day"><b class="date6">DO</b></div>
            <div class="date-day"><b class="date5">FR</b></div>
            <div class="date-day"><b class="date8">SA</b></div>
            <div class="date-day"><b class="date9">SO</b></div>
          </div>
          <!-- (Rest deines Daypicker-Rasters) -->
        </div>
      </div>
    </div>
  </div>

  <!-- Popup-Open -->
  <script>
    (function () {
      var btn = document.getElementById("dButtonDate");
      if (!btn) return;
      btn.addEventListener("click", function () {
        var popup = document.getElementById("dDaypickerContainer");
        if (!popup) return;
        var st = popup.style;
        st.display = "flex";
        st.zIndex = 100;
        st.backgroundColor = "rgba(0,0,0,.25)";
        st.alignItems = "center";
        st.justifyContent = "center";
        popup.setAttribute("closable", "");
        var onClick = popup.onClick || function (e) {
          if (e.target === popup && popup.hasAttribute("closable")) {
            st.display = "none";
          }
        };
        popup.addEventListener("click", onClick);
      });
    })();
  </script>

  <!-- Fussball-Loop: holt Tagessumme aus im3_unload.php und rendert Bälle -->
  <script>
    (() => {
      // === Einstellungen ===
      const API_URL = 'im3_unload.php?limit=5000';   // gleiches Verzeichnis wie index.php
      const PERSONS_PER_BALL = 20;                   // 1 Ball ≈ 20 Personen (Performance)
      const KYBUN_CAPACITY = 19694;                  // Stadionkapazität
      const REFRESH_MS = 5 * 60 * 1000;              // alle 5 Minuten neu laden
      const MAX_BALLS = 500;                         // Sicherheitslimit

      const fieldEl   = document.getElementById('ballContainer');
      const countEl   = document.getElementById('countText');
      const percentEl = document.getElementById('percentText');

      if (!fieldEl) return;

      const todayStr = () => {
        const d = new Date();
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
      };

      const ch = (n) => n.toLocaleString('de-CH');

      function box() {
        return { w: fieldEl.clientWidth, h: fieldEl.clientHeight, pad: 6, ball: 14 };
      }

      function placeRandom(node) {
        const { w, h, pad, ball } = box();
        const x = pad + Math.random() * (w - pad * 2 - ball);
        const y = pad + Math.random() * (h - pad * 2 - ball);
        node.style.left = x + 'px';
        node.style.top  = y + 'px';
      }

      function queueMove(node) {
        const hop = () => {
          placeRandom(node);
          const t = 5000 + Math.random() * 5000; // 5–10s
          node.__timer = setTimeout(hop, t);
        };
        node.__timer = setTimeout(hop, 1500 + Math.random() * 1500);
      }

      function clearTimers() {
        fieldEl.querySelectorAll('.ball').forEach(b => b.__timer && clearTimeout(b.__timer));
      }

      function ensureBallCount(target) {
        const cur = fieldEl.querySelectorAll('.ball').length;

        // add
        for (let i = cur; i < target; i++) {
          const b = document.createElement('div');
          b.className = 'ball move';
          b.style.animationDuration = (5 + Math.random() * 4) + 's';
          b.style.animationDelay = (Math.random() * 2) + 's';
          placeRandom(b);
          fieldEl.appendChild(b);
          queueMove(b);
        }

        // remove
        if (cur > target) {
          const extra = cur - target;
          const balls = Array.from(fieldEl.querySelectorAll('.ball'));
          for (let i = 0; i < extra; i++) balls[balls.length - 1 - i]?.remove();
        }
      }

      async function loadAndRender() {
        try {
          const res = await fetch(API_URL, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const data = await res.json();

          // Die API kann {ok:true, rows:[...]} liefern ODER direkt ein Array (je nach Version)
          const rows = Array.isArray(data) ? data : (Array.isArray(data.rows) ? data.rows : []);
          const tStr = todayStr();

          const todayRows = rows.filter(r => r.datum_tag === tStr);
          const total = todayRows.reduce((acc, r) => acc + (parseInt(r.summe || 0, 10) || 0), 0);

          // Zähler
          if (countEl) countEl.textContent = `${ch(total)} PERSONEN`;
          if (percentEl) {
            const pct = KYBUN_CAPACITY ? Math.min(100, (total / KYBUN_CAPACITY) * 100) : 0;
            percentEl.textContent = `${pct.toFixed(1)}%`;
          }

          // Bälle
          const wanted = Math.min(MAX_BALLS, Math.max(0, Math.floor(total / PERSONS_PER_BALL)));
          clearTimers();
          ensureBallCount(wanted);

        } catch (e) {
          console.warn('Fehler beim Laden/Rendern:', e);
        }
      }

      // Start + Auto-Refresh
      window.addEventListener('load', loadAndRender);
      setInterval(loadAndRender, REFRESH_MS);
    })();
  </script>

</body>
</html>