# Architecture and data flow

## Request lifecycle

The web server exposes `frontend/` as the public document root. A normal page
request includes `frontend/header.php`, which loads `bootstrap.php`, establishes
the connection from `dbconn.php`, retrieves the latest conditions, and renders
the shared document head and navigation. The page then adds its content and
includes `frontend/footer.php`.

`bootstrap.php` enables zlib response compression where PHP permits it.
`dbconn.php` creates the shared `$link` PostgreSQL PDO connection and provides
`db_query()` for scripts that do not use prepared statements directly. Database
failures currently stop the request and return a generic database error, so production
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
| `overview-data.php` | All seven overview series from one indexed archive query |
| `multidata.php` | Legacy single-series endpoint |
| `today-extremes.php` | Current-day summary values |
| `seasonal-data.php` | Monthly or seasonal aggregates |
| `climate-analysis.php` | Climate statistics used by the analysis page |
| `export-data.php` | Gzip-compressed JSON observation download |

Endpoints validate or constrain chart metric names before interpolating them
into SQL. New endpoints should follow the same allow-list approach, validate
time ranges, use prepared statements for values, set an explicit content type,
and return an appropriate HTTP status for invalid input.

## Chart runtime

Highcharts, Highstock, and the required modules are loaded in the shared page
shell. Page scripts initialize charts after `DOMContentLoaded` and describe
the desired series, units, labels, and interaction. `frontend/js/chart-theme.js` and
`frontend/js/weather-charts.js` own shared behavior:

- default colors, typography, tooltips, legends, and responsive behavior;
- ordinary and stock-style Highcharts constructors;
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

## Dashboard startup

`frontend/lib/dashboard-data.php` reads the latest archive observation and
current-day/month totals for the initial dashboard HTML. The observation time
is shown in Europe/London, and connection status stays separate from these
archive readings. MQTT starts as soon as the dashboard markup and Paho library
are available, independently of deferred chart scripts. On connection failure,
the archive snapshot remains visible; a failed extremes refresh also preserves
the previous values. Optional MQTT theme and icon libraries load asynchronously.

The overview starts a single `backend/overview-data.php` request while the
chart libraries load. That endpoint seeks the last 86,400 seconds using integer
epoch parameters, reads all seven metrics together, and groups rainfall into
the latest 24 UTC-hour buckets (displayed in local time). Missing readings stay
null. Chart construction still waits for `DOMContentLoaded`; initial series
animation is disabled and loading, empty and failure messages are explicit.

Archive conversions respect WeeWX `usUnits`: US (1), METRIC (16), or
METRICWX (17). In particular, METRIC wind already uses km/h; only METRICWX
wind needs multiplication by 3.6. New archive range predicates must not cast
the indexed timestamp to floating point through `UNIX_TIMESTAMP()`.

## Wind units

All user-facing wind speeds and gusts use km/h. `frontend/lib/wind-units.php`
provides PHP and SQL conversions from the per-row WeeWX `usUnits` flag:
16 is already km/h, 17 is m/s (multiply by 3.6), and 1 is mph (multiply by
1.609344). SQL conversions happen before averaging, extrema or band counts;
unknown unit codes and missing values remain null. Do not infer archive units
from the station model or console display setting.

Charts, annual reports, records, wind rose, climate diagnostics and social
cards share this conversion. Wind rose boundaries preserve the physical
1/2/3 m/s bands as 3.6/7.2/10.8 km/h; the calm threshold is 1.8 km/h
(0.5 m/s). Climate wind chill averages the archived station wind-chill values,
matching the other views instead of recalculating with a second wind conversion.
Exports retain raw measurements and include `usUnits` on each row; the export
page explains those wind units. MQTT fields ending in `_kph` already carry
km/h and keep their existing names.

## Trend extrema labels

The Trend workspace uses a one-pixel primary line and opts into
`WeatherCharts.markExtremes()` to label the visible main metric's high and low.
The shared runtime recalculates labels on redraw, zoom, reset, resize and theme
changes. Range displays use their observed bounds, rather than the average
line, and comparison series do not affect the labelled extrema. Missing values
are ignored; flat or single-point views use one combined High / Low label.
Labels stay within the plot and do not introduce extra data series or axes.
