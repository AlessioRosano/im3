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
          <div class="d-grafik-daypicker-icon">
            <img src="./fussballfeld.png" alt="Fussballfeld" class="daypicker-image">
            </div>
          </div>
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
              <span>Es waren </span><b id="xyCount">XY</b><span> Personen am </span><b id="xyDate">JJJJ-MM-DD</b><span> unterwegs.</span>
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

<!-- Popup-Container -->
<div id="dDaypickerContainer" class="popup-overlay">
  <div class="d-daypicker">
    <!-- Input-Feld -->
    <div class="input-wrapper">
      <label for="popup-date-input" class="sr-only">Datum wählen</label>
      <input id="popup-date-input" type="text" placeholder="YYYY-MM-DD" class="date-input">
    </div>

    <!-- Monat anzeigen -->
    <!-- Monat anzeigen mit CSS-Pfeilen -->
<div class="calendar-header">
  <div class="month-nav arrow-left" id="prev-month"></div>
  <div id="calendar-month" class="calendar-month"></div>
  <div class="month-nav arrow-right" id="next-month"></div>
</div>

    <!-- Kalender -->
    <div id="calendar"></div>
  </div>
</div>


  <!-- Popup JS -->
<script>
const dButtonDate = document.getElementById("dButtonDate");
const popup = document.getElementById("dDaypickerContainer");
const dateInput = document.getElementById("popup-date-input");
const calendarMonthEl = document.getElementById('calendar-month');
const calendarEl = document.getElementById("calendar");
const prevMonthBtn = document.getElementById('prev-month');
const nextMonthBtn = document.getElementById('next-month');

// ====== TEXTVERKNÜPFUNG: "Es waren XY Personen am JJJJ-MM-DD unterwegs" ======
const xyCountEl = document.getElementById('xyCount');
const xyDateEl = document.getElementById('xyDate');


// Funktion: Daten für bestimmtes Datum laden (summiert automatisch alle Werte aus "summe")
async function fetchPeopleForDate(dateStr) {
  const url = `./im3_unload.php?from=${dateStr}&to=${dateStr}&limit=5000`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
  if (!res.ok) throw new Error('HTTP ' + res.status);

  const data = await res.json();
  console.log('Geladene Daten:', data); // 🔍 In der Browser-Konsole prüfen

  // Sicherstellen, dass ein Array zurückkommt
  if (!Array.isArray(data)) return 0;

  // Alle Werte aus "summe" zusammenzählen
  const total = data.reduce((acc, row) => {
    const val = Number(row.summe);
    return acc + (isNaN(val) ? 0 : val);
  }, 0);

  return total;
}

// Funktion: Text im Frontend aktualisieren
async function updateDateAndCount(dateStr) {
  xyDateEl.textContent = dateStr;
  xyCountEl.textContent = '...'; // lädt

  try {
    const total = await fetchPeopleForDate(dateStr);
    xyCountEl.textContent = new Intl.NumberFormat('de-CH').format(total);
  } catch (err) {
    console.error('Fehler beim Laden:', err);
    xyCountEl.textContent = '–';
  }
}

// Variable für aktuell angezeigten Monat
let currentCalendarDate = new Date();

// Öffnen / Schließen
dButtonDate.addEventListener("click", () => popup.style.display = "flex");
popup.addEventListener("click", e => { if(e.target === popup) popup.style.display = "none"; });

// Kalender erstellen
function renderCalendar(date = currentCalendarDate) {
  currentCalendarDate = date; // speichere aktuell angezeigten Monat
  calendarEl.innerHTML = "";
  const year = date.getFullYear();
  const month = date.getMonth();

  // Monatsname setzen
  const monthNames = ["Januar","Februar","März","April","Mai","Juni",
                      "Juli","August","September","Oktober","November","Dezember"];
  calendarMonthEl.textContent = monthNames[month] + ' ' + year;

  // erster Tag des Monats
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  // leere Felder für Anfang
  for(let i = 0; i < firstDay; i++) {
    const empty = document.createElement('div');
    calendarEl.appendChild(empty);
  }

  // Tage einfügen
  for(let d = 1; d <= daysInMonth; d++) {
    const dayEl = document.createElement('div');
    dayEl.textContent = d;
    dayEl.className = 'day';
    dayEl.addEventListener('click', () => {
    calendarEl.querySelectorAll('.day').forEach(e => e.classList.remove('selected'));
    dayEl.classList.add('selected');

   const selectedDate = new Date(year, month, d);
   const dateStr = selectedDate.toISOString().split('T')[0];

   dateInput.value = dateStr;
   dButtonDate.querySelector('b').textContent = dateStr;
   popup.style.display = 'none';

   // ⬇️ Aktualisiere jetzt Text und Personenanzahl
    updateDateAndCount(dateStr);
    });
    calendarEl.appendChild(dayEl);
  }
}

prevMonthBtn.addEventListener('click', () => {
  const newDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1);
  renderCalendar(newDate);
});

nextMonthBtn.addEventListener('click', () => {
  const newDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() + 1, 1);
  renderCalendar(newDate);
});

// Input klickbar: öffnet Pop-up
dateInput.addEventListener('click', () => popup.style.display = "flex");

dateInput.addEventListener('change', () => {
  const parts = dateInput.value.split('-'); // YYYY-MM-DD
  if(parts.length === 3) {
    const [y, m, d] = parts.map(Number);
    if(!isNaN(y) && !isNaN(m) && !isNaN(d)) {
      const newDate = new Date(y, m - 1, d);
      renderCalendar(newDate);

      // markiere gewählten Tag
      const dayElements = calendarEl.querySelectorAll('.day');
      dayElements.forEach(e => {
        e.classList.remove('selected');
        if(Number(e.textContent) === d) e.classList.add('selected');
      });
    }
  }
});

// Input per Enter aktualisieren
dateInput.addEventListener('keydown', e => {
  if(e.key === 'Enter') {
    const parts = dateInput.value.split('-'); // YYYY-MM-DD
    if(parts.length === 3) {
      const [y, m, d] = parts.map(Number);
      if(!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        const newDate = new Date(y, m - 1, d);
        renderCalendar(newDate);

        // markiere gewählten Tag
        const dayElements = calendarEl.querySelectorAll('.day');
        dayElements.forEach(el => {
          el.classList.remove('selected');
          if(Number(el.textContent) === d) el.classList.add('selected');
        });

        // Popup optional schließen
        popup.style.display = 'none';

        // Button-Text aktualisieren
        dButtonDate.querySelector('b').textContent = dateInput.value;
      } else {
        alert('Ungültiges Datum');
      }
    } else {
      alert('Datum muss das Format YYYY-MM-DD haben');
    }
  }
});

// Zeigt beim Laden der Seite automatisch die heutigen Daten an
document.addEventListener('DOMContentLoaded', () => {
  const today = new Date().toISOString().split('T')[0]; // z. B. 2025-10-14
  updateDateAndCount(today);
});
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