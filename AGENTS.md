# Repository Overview

This project is a PHP-based weather web site. It retrieves data from a local `weewx` MySQL database and visualizes it with Highcharts and Highstock. Tables are styled with Tailwind utility classes for a lightweight, consistent look.
Record any additional project decisions or conventions in this file.

## Key Files
- `index.php` & `header.php` render the main dashboard and load scripts for live weather conditions.
- `dbconn.php` defines the MySQL connection used across scripts.
- `backend/getdata.php`, `backend/metric-data.php`, and `backend/range-data.php` expose weather data for charts.
- Graph pages such as `dynamic-graph.php`, `metric-graph.php`, `range-graph.php`, and `overview-graph.php` use the shared `WeatherCharts` Highcharts runtime to display time series.

## Development Notes
- PHP files generally use two-space indentation.
- There is no automated test suite; run `php -l <file>` on any modified PHP file to check syntax.
- Static assets like CSS and JavaScript libraries live in the project root or dedicated folders (`css`, etc.), with user-facing images stored in `frontend/images/` and shared JavaScript collected under `frontend/js/`.
- Tailwind CSS provides project-wide styling and Font Awesome supplies icons.
- Headings use bold **Roboto**, body text **Inter**, and buttons or highlights light **Source Sans Pro**.
- Sections should be wrapped in card components (`bg-white shadow rounded p-4`).
- Highcharts, Highstock, and their required modules are loaded from jsDelivr. Custom chart scripts should run after `DOMContentLoaded` so `Highcharts` and the shared `WeatherCharts` facade are available.
- Keep chart defaults, theme behavior, and responsive reflow support in `frontend/js/chart-theme.js` and `frontend/js/weather-charts.js`; page-level scripts should focus on data, units, labels, and chart intent.
- Live garden images are published over MQTT on `weather/vegimage`; browser consumers accept raw JPEG/PNG/WebP bytes or Base64-encoded image payloads.
- Astro day details should reuse the strict night-planning timeline and present hourly conditions as a labelled matrix rather than a raw data table.
- Keep Astro Planner as a prominent direct navigation destination. Annual Reports should link directly to the shared annual-report workspace because report type switching is available within those pages.
- Astro planner cards represent a complete observing night from local sunset through the following sunrise. Keep sky quality, twilight darkness, and moon visibility aligned to that shared timeline in `frontend/astro/planner.php`, and convert forecast `utcTime` values to Europe/London before display.
- Mark an astro “best imaging window” only where green sky/seeing, full astronomical darkness, and the moon below the horizon overlap; omit window bounds when no strict overlap exists.
- Seasonal year comparisons should use visible, accessible year toggles with quick recent-year presets; default to the latest three years and keep selector colours aligned with chart series.
- Keep `README.md` focused on setup and onboarding; place detailed architecture and development guidance under `docs/` and update it alongside behavior changes.

- Mobile navigation must stay flush with the viewport bottom, reserve matching content space, and include iPhone safe-area insets. Use dynamic viewport height for the mobile menu so Safari toolbar changes do not clip it.

## Verification
Before committing, run syntax checks for changed PHP files. Example:
```bash
php -l index.php
```

## Replacement platform decisions

- Production uses Nginx and a dedicated PHP 8.5-FPM pool running as the
  unprivileged `weather` identity.
- Historical data is stored in PostgreSQL. PHP connects over the local Unix
  socket using peer authentication; no application database password is stored.
- Node-RED on `data` consumes the existing retained `weather/loop` MQTT JSON and
  inserts five-minute archive rows over WireGuard using a dedicated SCRAM role.
- Archive timestamps remain Unix epochs. Compatibility SQL functions preserve
  the application's Europe/London interpretation while its queries are migrated.

- Render dashboard archive readings and their Europe/London observation time in
  the initial HTML; only report the MQTT connection as live after it connects.
  Start dashboard MQTT independently of deferred chart libraries.
- Overview charts use one `backend/overview-data.php` request, started before
  chart initialization. Keep archive range predicates on integer epoch bounds
  so PostgreSQL can seek using the dateTime index; avoid floating-point
  `UNIX_TIMESTAMP()` comparisons. Respect WeeWX `usUnits`: 16 stores wind in
  km/h, 17 in m/s, and 1 in mph.

- Display wind speed and gusts in km/h throughout the site. Use
  `frontend/lib/wind-units.php` for archive conversion before aggregation;
  never multiply METRIC (`usUnits=16`) wind by 3.6 again. Keep raw exports
  self-describing with `usUnits`. Wind rose boundaries are 3.6, 7.2 and
  10.8 km/h; calm wind is below 1.8 km/h.
