<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="initial-scale=1, width=device-width" />
  <title>Passanten St. Gallen</title>

  <!-- CSS liegt im selben Ordner -->
  <link rel="stylesheet" href="./im3_styles.css?v=10" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="d-home">
    <div class="d-hero">
      <div class="d-text-hero">
        <b class="wie-viele-personen">Wie viele Personen gingen heute durch St.Gallen?</b>
        <div class="relativ-zur-stadiongrsse">relativ zur Stadiongrösse des Kybunparks</div>
      </div>

      <div class="d-grafik-hero">
        <!-- Spielfeld mit Ball-Layer -->
        <div class="field-wrap">
          <img src="./kybunpark.png" alt="Fussballfeld Kybunpark" class="field-image" />
          <div id="balls-layer" class="balls-layer" aria-live="polite"></div>
        </div>

        <!-- Live-Zähler -->
        <div class="personen-parent">
          <div class="personen" id="people-count">– PERSONEN</div>
          <div class="personen" id="percent-kybun">–%</div>
        </div>
      </div>
    </div>

    <!-- Datum/Text (optional – lässt sich später verdrahten) -->
    <div class="d-passantenanzahl-datum">
      <div class="features">
        <div class="frame">
          <div class="d-grafik-daypicker-icon"></div>
          <div class="d-text-daypicker">
            <div class="d-home-wie-viele-personen">
              Wie viele Personen gingen durch die Stadt St. Gallen am …
            </div>
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

    <!-- Footer -->
    <div class="d-footer">
      <div class="d-line-footer"></div>
      <div class="copywright-2025-sarina-container">
        <p class="sarina-da-ros">Copywright 2025</p>
        <p class="sarina-da-ros">Sarina Da Ros & Alessio Rosano</p>
      </div>
    </div>
  </div>

  <!-- Popup-Container (einfach belassen) -->
  <div id="dDaypickerContainer" class="popup-overlay" style="display:none">
    <div class="d-daypicker">
      <div class="date-wrapper"><b class="date">YYYY/MM/DD</b></div>
      <div class="date-parent">
        <div class="d-daypicker-date">
          <div class="selectordate"><div class="date2">Januar 2025</div></div>
        </div>
        <!-- Hier könnte dein Kalender-Markup stehen -->
      </div>
    </div>
  </div>

  <!-- Popup JS -->
  <script>
    const dButtonDate = document.getElementById("dButtonDate");
    if (dButtonDate) {
      dButtonDate.addEventListener("click", function () {
        const popup = document.getElementById("dDaypickerContainer");
        if (!popup) return;
        const s = popup.style;
        s.display = "flex"; s.zIndex = 100; s.backgroundColor = "rgba(0,0,0,.25)";
        s.alignItems = "center"; s.justifyContent = "center";
        popup.setAttribute("closable", "");
        popup.addEventListener("click", function (e) {
          if (e.target === popup && popup.hasAttribute("closable")) s.display = "none";
        });
      });
    }
  </script>

  <!-- Ball-Logik + Tageszähler -->
  <script>
  (function () {
    // === Einstellungen ===
    const ENDPOINT = './im3_unload.php';   // gleicher Ordner
    const PEOPLE_PER_BALL = 5;             // 1 Ball = 5 Personen (Performance)
    const KYBUN_CAPACITY = 19794;          // Stadionkapazität
    const REFRESH_MS = 5 * 60 * 1000;      // alle 5 Minuten

    const ballsLayer   = document.getElementById('balls-layer');
    const peopleEl     = document.getElementById('people-count');
    const percentEl    = document.getElementById('percent-kybun');

    let currentBalls = 0;

    function isoDate(d) {
      const y = d.getFullYear();
      const m = String(d.getMonth()+1).padStart(2,'0');
      const day = String(d.getDate()).padStart(2,'0');
      return `${y}-${m}-${day}`;
    }
    function todayRange() {
      const now = new Date();
      const start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const end = new Date(start.getTime() + 24*60*60*1000);
      return { from: isoDate(start), to: isoDate(end) };
    }
    async function fetchToday() {
      const {from, to} = todayRange();
      const url = `${ENDPOINT}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&limit=5000`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    }

    function randomInBox(width, height, pad=10) {
      const x = Math.random() * (width  - pad*2) + pad;
      const y = Math.random() * (height - pad*2) + pad;
      return { x, y };
    }

    function addBalls(n) {
      const rect = ballsLayer.getBoundingClientRect();
      const w = rect.width, h = rect.height;

      for (let i = 0; i < n; i++) {
        const el = document.createElement('div');
        el.className = 'ball';

        const p0 = randomInBox(w, h, 12);
        const p1 = randomInBox(w, h, 12);

        el.style.setProperty('--x0', `${p0.x}px`);
        el.style.setProperty('--y0', `${p0.y}px`);
        el.style.setProperty('--x1', `${p1.x}px`);
        el.style.setProperty('--y1', `${p1.y}px`);

        const dur = 6 + Math.random()*10;       // 6–16s
        const delay = Math.random() * -dur;     // verteilt Starts
        el.style.animationDuration = `${dur}s`;
        el.style.animationDelay    = `${delay}s`;

        ballsLayer.appendChild(el);
      }
      currentBalls += n;
    }

    function resetBalls() {
      ballsLayer.innerHTML = '';
      currentBalls = 0;
    }

    function updateCounters(total) {
      peopleEl.textContent  = new Intl.NumberFormat('de-CH').format(total) + ' PERSONEN';
      const pct = (total / KYBUN_CAPACITY) * 100;
      percentEl.textContent = (isFinite(pct) ? pct.toFixed(1) : '0.0') + '%';
    }

    async function refresh() {
      try {
        const data = await fetchToday(); // erwartet Array von Rows mit "summe"
        const total = Array.isArray(data)
          ? data.reduce((acc, r) => acc + (parseInt(r.summe, 10) || 0), 0)
          : 0;

        const wanted = Math.floor(total / PEOPLE_PER_BALL);

        // Mitternacht oder geringere Zahl -> komplett neu
        if (wanted < currentBalls) resetBalls();

        const diff = wanted - currentBalls;
        if (diff > 0) addBalls(diff);

        updateCounters(total);
      } catch (err) {
        console.error('Update fehlgeschlagen:', err);
      }
    }

    window.addEventListener('load', refresh);
    setInterval(refresh, REFRESH_MS);
  })();
  </script>

</body>
</html>