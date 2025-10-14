<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="initial-scale=1, width=device-width" />
  <title>Passanten St. Gallen</title>

  <!-- CSS (liegt im selben Ordner) -->
  <link rel="stylesheet" href="./im3_styles.css?v=11" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="d-home">
    <!-- HERO -->
    <section class="d-hero">
      <div class="d-text-hero">
        <b class="wie-viele-personen">Wie viele Personen gingen heute durch St.Gallen?</b>
        <div class="relativ-zur-stadiongrsse">relativ zur Stadiongrösse des Kybunparks</div>
      </div>

      <div class="d-grafik-hero">
        <div class="field-wrap">
          <img src="./kybunpark.png" alt="Fussballfeld Kybunpark" class="field-image" />
          <div id="balls-layer-oben" class="balls-layer-oben" aria-live="polite"></div>
        </div>

        <div class="personen-parent">
          <div class="personen" id="people-count">– PERSONEN</div>
          <div class="personen" id="percent-kybun">–%</div>
        </div>
      </div>
    </section>

    <!-- DATUMSBEREICH -->
    <section class="d-passantenanzahl-datum">
      <div class="features">
        <div class="d-grafik-daypicker-icon">
          <img src="./fussballfeld.png" alt="Fussballfeld" class="daypicker-image">
          <div id="balls-layer-unten" class="balls-layer-unten" aria-live="polite"></div>
        </div>

        <div class="d-text-daypicker">
          <div class="d-home-wie-viele-personen">
            Wie viele Personen gingen durch die Stadt St. Gallen am …
          </div>

          <div class="d-home-frame">
            <div class="d-button-date" id="dButtonDate" role="button" aria-haspopup="dialog" aria-controls="dDaypickerContainer">
              <b class="jjjj-mm-dd">JJJJ-MM-DD</b>
            </div>
          </div>

          <div class="es-waren-xy-personen-am-jjjj-m-wrapper">
            <div class="es-waren-xy-container">
              <span>Es waren </span><b id="xyCount">XY</b><span> Personen am </span><b id="xyDate">JJJJ-MM-DD</b><span> unterwegs.</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="d-footer">
      <div class="d-line-footer"></div>
      <div class="copywright-2025-sarina-container">
        <p>Copywright 2025</p>
        <p>Sarina Da Ros &amp; Alessio Rosano</p>
      </div>
    </footer>
  </div>

  <!-- POPUP: DATE PICKER -->
  <div id="dDaypickerContainer" class="popup-overlay" style="display:none">
    <div class="d-daypicker" role="dialog" aria-modal="true" aria-label="Datum wählen">
      <div class="input-wrapper">
        <label for="popup-date-input" class="sr-only">Datum wählen</label>
        <input id="popup-date-input" type="text" placeholder="YYYY-MM-DD" class="date-input">
      </div>

      <div class="calendar-header">
        <div class="month-nav arrow-left" id="prev-month" aria-label="Vorheriger Monat" role="button" tabindex="0"></div>
        <div id="calendar-month" class="calendar-month"></div>
        <div class="month-nav arrow-right" id="next-month" aria-label="Nächster Monat" role="button" tabindex="0"></div>
      </div>

      <div id="calendar" aria-label="Kalender"></div>
    </div>
  </div>

  <!-- ======= JS: Gemeinsame Hilfen ======= -->
  <script>
    const ENDPOINT = './im3_unload.php'; // JSON-Endpoint
    const KYBUN_CAPACITY = 19794;

    // 1 Ball = X Personen (Performance-Steuerung)
    const PEOPLE_PER_BALL_TODAY = 5;   // Hero (heute)
    const PEOPLE_PER_BALL_DATE  = 5;   // gewähltes Datum (unten)

    // DOM Refs
    const ballsLayerToday = document.getElementById('balls-layer-oben'); // Hero oben
    const ballsLayerDate  = document.getElementById('balls-layer-unten'); // Datum unten
    const peopleEl        = document.getElementById('people-count');
    const percentEl       = document.getElementById('percent-kybun');

    const dButtonDate     = document.getElementById("dButtonDate");
    const popup           = document.getElementById("dDaypickerContainer");
    const dateInput       = document.getElementById("popup-date-input");
    const calendarMonthEl = document.getElementById('calendar-month');
    const calendarEl      = document.getElementById("calendar");
    const prevMonthBtn    = document.getElementById('prev-month');
    const nextMonthBtn    = document.getElementById('next-month');

    const xyCountEl = document.getElementById('xyCount');
    const xyDateEl  = document.getElementById('xyDate');

    // --- Datumshilfen ---
    function formatLocalYMD(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }
    function addDaysYMD(ymd, days) {
      const [y, m, d] = ymd.split('-').map(Number);
      const dt = new Date(y, m - 1, d);
      dt.setDate(dt.getDate() + days);
      return formatLocalYMD(dt);
    }
    function todayRange() {
      const now = new Date();
      const start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const end = new Date(start.getTime() + 24*60*60*1000);
      return { from: formatLocalYMD(start), to: formatLocalYMD(end) };
    }

    // --- Daten laden (summiert "summe") ---
    async function fetchTotal(from, to) {
      const url = `${ENDPOINT}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&limit=5000`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if(!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      const rows = Array.isArray(data) ? data : (Array.isArray(data?.rows) ? data.rows : []);
      return rows.reduce((acc, r) => acc + (parseInt(r.summe, 10) || 0), 0);
    }

    // --- Ball-Rendering ---
    function randomInBox(width, height, pad=10) {
      const x = Math.random() * (width  - pad*2) + pad;
      const y = Math.random() * (height - pad*2) + pad;
      return { x, y };
    }

    function addBallsToLayer(layerEl, count) {
      const rect = layerEl.getBoundingClientRect();
      const w = rect.width, h = rect.height;

      for (let i=0; i<count; i++) {
        const el = document.createElement('div');
        el.className = 'ball';
        const p0 = randomInBox(w, h, 12);
        const p1 = randomInBox(w, h, 12);
        el.style.setProperty('--x0', `${p0.x}px`);
        el.style.setProperty('--y0', `${p0.y}px`);
        el.style.setProperty('--x1', `${p1.x}px`);
        el.style.setProperty('--y1', `${p1.y}px`);

        const dur = 6 + Math.random()*10;   // 6–16s
        const delay = Math.random()*-dur;   // verteilen
        el.style.animationDuration = `${dur}s`;
        el.style.animationDelay    = `${delay}s`;

        layerEl.appendChild(el);
      }
    }
    function resetLayer(layerEl) {
      layerEl.innerHTML = '';
    }

    // --- HEUTE (Hero) ---
    let currentBallsToday = 0;
    async function refreshToday() {
      try {
        const { from, to } = todayRange();
        const total = await fetchTotal(from, to);
        const wanted = Math.floor(total / PEOPLE_PER_BALL_TODAY);

        if (wanted < currentBallsToday) { // Mitternacht o.ä.
          resetLayer(ballsLayerToday);
          currentBallsToday = 0;
        }
        const diff = wanted - currentBallsToday;
        if (diff > 0) {
          addBallsToLayer(ballsLayerToday, diff);
          currentBallsToday += diff;
        }

        // Zähler
        peopleEl.textContent = new Intl.NumberFormat('de-CH').format(total) + ' PERSONEN';
        const pct = (total / KYBUN_CAPACITY) * 100;
        percentEl.textContent = (isFinite(pct) ? pct.toFixed(1) : '0.0') + '% STADIONAUSLASTUNG';
      } catch (err) {
        console.error('Heute-Update fehlgeschlagen:', err);
      }
    }

    // --- GEWÄHLTES DATUM (unten links + Text) ---
    let currentBallsDate = 0;
    async function updateDateAndCount(dateStr) {
      xyDateEl.textContent = dateStr;
      xyCountEl.textContent = '...';

      try {
        const from = dateStr;
        const to   = addDaysYMD(dateStr, 1);
        const total = await fetchTotal(from, to);

        // Text
        xyCountEl.textContent = new Intl.NumberFormat('de-CH').format(total);

        // Bälle: neu aufbauen (Datum wechselte => kein Delta nötig)
        resetLayer(ballsLayerDate);
        currentBallsDate = 0;
        const wanted = Math.floor(total / PEOPLE_PER_BALL_DATE);
        if (wanted > 0) {
          addBallsToLayer(ballsLayerDate, wanted);
          currentBallsDate = wanted;
        }
      } catch (err) {
        console.error('Datum-Update fehlgeschlagen:', err);
        xyCountEl.textContent = '–';
      }
    }

    // --- DATE PICKER LOGIK ---
    let currentCalendarDate = new Date();

    dButtonDate.addEventListener("click", () => popup.style.display = "flex");
    popup.addEventListener("click", e => { if(e.target === popup) popup.style.display = "none"; });

    function renderCalendar(date = currentCalendarDate) {
      currentCalendarDate = date;
      calendarEl.innerHTML = "";
      const year = date.getFullYear();
      const month = date.getMonth();

      const monthNames = ["Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember"];
      calendarMonthEl.textContent = monthNames[month] + ' ' + year;

      // Wochentag des 1. (0=So ... 6=Sa) -> wir wollen Mo-Start
      const firstDay = (new Date(year, month, 1).getDay() + 6) % 7; // Montag=0
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      for (let i=0; i<firstDay; i++) {
        const empty = document.createElement('div');
        calendarEl.appendChild(empty);
      }

      for (let d=1; d<=daysInMonth; d++) {
        const dayEl = document.createElement('div');
        dayEl.textContent = d;
        dayEl.className = 'day';
        dayEl.addEventListener('click', () => {
          calendarEl.querySelectorAll('.day').forEach(e => e.classList.remove('selected'));
          dayEl.classList.add('selected');

          const selectedDate = new Date(year, month, d);
          const dateStr = formatLocalYMD(selectedDate);

          dateInput.value = dateStr;
          dButtonDate.querySelector('b').textContent = dateStr;
          popup.style.display = 'none';

          updateDateAndCount(dateStr);
        });
        calendarEl.appendChild(dayEl);
      }
    }

    prevMonthBtn.addEventListener('click', () => {
      renderCalendar(new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1));
    });
    nextMonthBtn.addEventListener('click', () => {
      renderCalendar(new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() + 1, 1));
    });

    dateInput.addEventListener('click', () => popup.style.display = "flex");
    dateInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        const parts = dateInput.value.split('-'); // YYYY-MM-DD
        if (parts.length === 3) {
          const [y,m,d] = parts.map(Number);
          if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            const newDate = new Date(y, m-1, d);
            renderCalendar(newDate);

            const selectedYMD = formatLocalYMD(newDate);
            // markiere im Grid (grob)
            calendarEl.querySelectorAll('.day').forEach(el => {
              el.classList.toggle('selected', Number(el.textContent) === d);
            });

            popup.style.display = 'none';
            dButtonDate.querySelector('b').textContent = selectedYMD;
            updateDateAndCount(selectedYMD);
          } else {
            alert('Ungültiges Datum');
          }
        } else {
          alert('Datum muss das Format YYYY-MM-DD haben');
        }
      }
    });

    // Initial: heute laden (oben & unten)
    document.addEventListener('DOMContentLoaded', () => {
      renderCalendar(new Date());
      const today = formatLocalYMD(new Date());
      dButtonDate.querySelector('b').textContent = today;
      updateDateAndCount(today);
      refreshToday();
      // Hero zyklisch aktualisieren (heute)
      setInterval(refreshToday, 5 * 60 * 1000);
    });
  </script>

</body>
</html>