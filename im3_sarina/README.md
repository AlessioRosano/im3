# IM3 HS25 – PHP & Datenbanken

Willkommen im Code-Repository für _IM3 – PHP & Datenbanken_ an der FH Graubünden. Dieses Repository enthält Übungsdateien, Code-Alongs und Lösungen zu PHP, Datenbankzugriff, APIs und ETL-Themen.

---

## 📂 Inhalt des Repositories

| Ordner / Datei              | Zweck                                                                                                   |
| --------------------------- | ------------------------------------------------------------------------------------------------------- |
| `code-alongs/`              | Kapitelweise Übungen (Studierende bearbeiten die Dateien in Vorlesung).                                 |
| `code-alongs/.../solution/` | Musterlösungen zu den entsprechenden CodeAlongs.                                                        |
| `cheatsheets/`              | Kurzreferenzen (Syntax, Best Practices, Beispiele) zu PHP, Datenbankoperationen, JSON, etc.             |
| `etl-boilerplate/`          | Grundgerüst für ETL-Projekte (z. B. Extract / Transform / Load)                                         |
| `IM3.sql`                   | SQL-Script zum Erstellen der benötigten Datenbankstruktur / Tabelle „User“.                             |
| `config.php`                | Konfiguration der Datenbankverbindung (DSN, Benutzer, Passwort, Optionen). **Nicht** öffentlich teilen. |
| `load.php`                  | Skript, welches schreibende DB-Operationen übernimmt (Daten einfügen).                                  |
| `unload.php`                | Skript, das Daten aus der Datenbank abruft und ausgibt (z. B. als JSON).                                |
| `index.html` und `js/`      | Frontend-Interaktion & Beispiel-Formulare / JS-Code, um mit den APIs (load/unload) zu arbeiten.         |
| `solution/`                 | Vollständige Lösungen, kommentiert – zur Kontrolle & zum Vergleich nach den Vorlesungen.                |

---

## 🎯 Lernziele & Themenbereiche

Mit diesen Dateien und Übungen lernst Du:

- PHP-Grundlagen: Syntax, Variablen, Bedingungen, Schleifen
- Arbeiten mit Arrays & assoziativen Arrays
- Funktionen, Rückgabewerte, Parameter und Default-Werte
- Validierung und Sanitization von Benutzer-/URL-Parametern
- Datenbankoperationen mit PDO: SELECT, INSERT, Prepared Statements
- Umgang mit JSON in PHP: `json_encode`, `json_decode`
- Simple ETL-Strukturen: Daten aus externen Quellen holen, transformieren und speichern
- API-Endpunkte bauen: Anfrageparameter verarbeiten, Antworten als JSON bereitstellen

---

## 🛠 Installation & Vorbereitung

1. **Datenbank importieren**  
   Führe `IM3.sql` aus, um die Tabelle(n) zu erstellen (z. B. User-Tabelle).
   Eine aktueller Version findest du auf Moodle.

2. **Konfiguration**  
   Passe `config.php` an:
   ```php
   <?php
   $dsn = 'mysql:host=localhost;dbname=deine_db;charset=utf8mb4';
   $username = 'dein_user';
   $password = 'dein_passwort';
   $options = [
     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false
   ];
   ```
