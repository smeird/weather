# Architecture and data flow

## Request lifecycle

The web server exposes `frontend/` as the public document root. A normal page
request includes `frontend/header.php`, which loads `bootstrap.php`, establishes
the connection from `dbconn.php`, retrieves the latest conditions, and renders
the shared document head and navigation. The page then adds its content and
includes `frontend/footer.php`.

`bootstrap.php` enables zlib response compression where PHP permits it.
`dbconn.php` creates the shared `$link` MySQLi connection and provides
`db_query()` for scripts that do not use prepared statements directly. Database
failures currently stop the request and return the MySQL error, so production
servers should avoid exposing PHP error output to visitors.

## Major components

### Pages

- `index.php` presents current station conditions and an overview chart.
- `dynamic-graph.php`, `metric-graph.php`, `range-graph.php`, and
  `overview-graph.php` provide interactive time-series views.
- `historical.php`, `seasonal.php`, `climate-analysis.php`, `records.php`, and
  the annual report pages summarize historical observations.
- `export.php` provides the interface for downloadable observation data.
- `picture.php` displays the MQTT-delivered garden camera feed.
- `astro/` contains the moon and astronomical views.

All paths above are relative to `frontend/`.

### Data endpoints

Browser-side code calls PHP endpoints under `frontend/backend/`:

| Endpoint | Consumer-facing role |
| --- | --- |
| `metric-data.php` | Metric time-series observations |
| `range-data.php` | Minimum/maximum range series and daily rain totals |
| `range-limits.php` | Available historical time bounds |
| `multidata.php` | Combined series for overview charts |
| `today-extremes.php` | Current-day summary values |
| `seasonal-data.php` | Monthly or seasonal aggregates |
| `climate-analysis.php` | Climate statistics used by the analysis page |
| `export-data.php` | Gzip-compressed JSON observation download |

Endpoints validate or constrain chart metric names before interpolating them
into SQL. New endpoints should follow the same allow-list approach, validate
time ranges, use prepared statements for values, set an explicit content type,
and return an appropriate HTTP status for invalid input.

## Chart runtime

Apache ECharts is loaded in the shared page shell. Page scripts wait for
`DOMContentLoaded`, fetch their data, and describe the desired series, units,
labels, and interaction. `frontend/js/weather-charts.js` owns shared behavior:

- default colors, typography, tooltips, legends, and responsive behavior;
- conversion of legacy Highcharts-style options into ECharts options;
- ordinary and stock-style chart constructors;
- resize and full-screen behavior; and
- common date, number, and color helpers.

Keep compatibility fixes and visual defaults in the runtime rather than copying
them into individual pages. Page-level code should contain only data retrieval
and chart intent.

## Styling and assets

Tailwind scans PHP and JavaScript under `frontend/` using `tailwind.config.js`.
`frontend/assets/tailwind-input.css` is the source stylesheet and
`frontend/assets/tailwind.css` is the generated, minified asset committed for
deployment. The shared visual language uses white, rounded, shadowed cards;
Roboto headings; Inter body text; and Source Sans Pro buttons or highlights.

`frontend/assets/hero-gradient.js` derives a weather state from live readings
and applies the matching gradient rules. User-facing raster and favicon assets
belong in `frontend/images/`; reusable browser code belongs in `frontend/js/`.

## Live MQTT data

The garden camera publishes on `weather/vegimage`. The browser consumer in
`frontend/js/garden-image.js` accepts raw JPEG, PNG, or WebP payloads and
Base64-encoded images. The dashboard and picture page establish browser-side
MQTT connections, so broker accessibility and credentials are deployment
concerns rather than PHP server requirements.

## Database assumptions

The application reads a WeeWX `archive` table whose `dateTime` column contains
Unix timestamps. Reports use common WeeWX columns such as `outTemp`,
`outHumidity`, `windSpeed`, `windGust`, `windDir`, `barometer`, `pressure`,
`rain`, `rainRate`, `dewpoint`, `heatindex`, `windchill`, `radiation`, and `UV`.

PHP sets the presentation timezone to `Europe/London` in shared and historical
views. Preserve the timestamp units expected by each boundary: database values
are seconds, while chart APIs generally exchange JavaScript timestamps in
milliseconds.
