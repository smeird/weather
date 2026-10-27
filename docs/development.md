# Development and verification

## Initial setup

Install PHP 8.2 or later, Composer, Node.js, and npm. Then install dependencies
from the repository root:

```bash
composer install
npm ci
```

Composer installs PHPStan for static analysis. npm installs Tailwind and its
forms, typography, and container-query plugins. Runtime PHP dependencies are
provided by the host PHP installation rather than Composer.

To exercise pages manually, provide `DB_HOST`, `DB_USER`, `DB_PASSWORD`, and
`DB_NAME`, then run PHP with `frontend/` as the document root:

```bash
DB_HOST=127.0.0.1 DB_USER=weather DB_PASSWORD=secret DB_NAME=weewx \
  php -S 127.0.0.1:8080 -t frontend
```

Do not treat example credentials as production defaults, and never commit real
credentials.

## Making changes

### PHP

- Use two-space indentation.
- Reuse `header.php` and `footer.php` for full pages.
- Load the shared database connection with a path based on `__DIR__` when
  possible, so includes do not depend on the process working directory.
- Validate query parameters before constructing SQL. Allow-list column names
  and use prepared statements for values.
- Set explicit response content types for API and download responses.

### Charts and browser JavaScript

- Run chart initialization after `DOMContentLoaded`.
- Put shared Highcharts defaults and theme behavior in
  `frontend/js/chart-theme.js`, and constructor/reflow behavior in
  `frontend/js/weather-charts.js`.
- Keep data URLs, units, series labels, and chart intent in the page script.
- Use `WeatherCharts.chart()` for standard charts and
  `WeatherCharts.stockChart()` for navigator/range-selector experiences.
- Ensure charts remain usable at narrow viewport widths and after container or
  full-screen resizes.

### CSS and visual design

- Prefer Tailwind utilities and wrap sections in `bg-white shadow rounded p-4`
  card components.
- Use bold Roboto for headings, Inter for body copy, and light Source Sans Pro
  for buttons or highlights.
- Edit `frontend/assets/tailwind-input.css`, then regenerate the committed
  stylesheet with `npm run build:css`.
- Place shared JavaScript in `frontend/js/` and user-facing images in
  `frontend/images/`.

### Mobile navigation

The shared header uses `viewport-fit=cover` and a bottom navigation bar on
viewports below 768px. `--mobile-nav-space` includes the control row and the
bottom safe area; keep body clearance, menu bounds, and backdrop bounds tied to
that value. The menu scrolls within the dynamic viewport height. Verify at
375px and 390px widths, short landscape heights, and desktop widths. On an
actual iPhone, also check Safari with the toolbar expanded/collapsed and the
home indicator visible.

## Verification

The checks below do not require database access:

```bash
# A single changed PHP file
php -l frontend/example.php

# Syntax-check all PHP files
make lint-php

# Syntax and configured level-0 static analysis
make lint

# Rebuild the production stylesheet after CSS or class changes
npm run build:css
```

There is no database-backed automated test suite. Without a local WeeWX
database, do not request PHP pages or API endpoints merely as a test: the shared
header and most endpoints connect during request handling. For documentation or
JavaScript-only changes, use targeted static checks and inspect the diff.

When a visible application change is made and the database is available, check
the affected page at desktop and mobile widths. Verify loading, empty, error,
and populated states where practical, and capture a screenshot for review.

## Continuous integration

GitHub Actions runs on pushes with PHP 8.2. It installs Composer dependencies
and invokes `make lint`, which syntax-checks every tracked PHP source outside
`vendor/` and runs PHPStan at level 0.

## Documentation maintenance

Update the documentation in the same change when altering environment
variables, public paths, build commands, database assumptions, endpoints, or
shared chart behavior. Record durable project conventions in `AGENTS.md`; keep
the README focused on onboarding and link to detailed material under `docs/`.

## Checking dashboard startup

On a database-backed environment, verify that the raw dashboard HTML contains
numeric `data-stat` readings and the archive observation timestamp before any
JavaScript runs. Confirm MQTT subsequently updates the readings and connection
badge, including zero readings. The overview and its full-screen view should
load seven series through one `backend/overview-data.php` request without the
previous two-second animation. Check null/empty data and failed requests leave
useful messages, and compare rainfall totals and wind units against the archive.

For query performance, use `EXPLAIN (ANALYZE, BUFFERS)` to confirm that integer
range bounds appear under `Index Cond`, rather than a floating-point filter
scanning the entire historical index. Measure public endpoint latency before
and after deployment.

## Checking wind units

Verify `weather_wind_kph()` and `weather_wind_sql()` with METRIC (16), US (1),
METRICWX (17), zero, null and unknown unit flags. Compare the detailed speed,
gust and comparison series with archive values at matching timestamps; check
both standard and aggregated modes. Annual and record gust peaks must agree
with `MAX(windGust)` for a METRIC archive. Wind rose bands must use 3.6, 7.2
and 10.8 km/h, and the climate calm fraction must be a decimal using 1.8 km/h.
Check that exported raw rows include `usUnits` and social cards render gusts
without a second conversion.

## Checking Trend extrema callouts

Verify the primary line remains one pixel wide on hover. Check high/low
callouts on line, min/max and average-range displays, then zoom into a smaller
period and reset. Range callouts use observed bounds; comparison data must not
change the labelled values. Check zero/negative, missing, flat and single-point
data, hidden primary series, mobile placement and theme/redraw behaviour.

## Daylight Station and Night Observatory

`frontend/assets/daylight-station.css` is loaded after the shared header's
legacy styles. It owns the new surface tokens and component overrides across
the dashboard, Trends, history and reports. Keep changes here rather than adding
another inline header theme. Existing font families remain unchanged. Dark mode
uses midnight surfaces and lavender actions; semantic chart and Astro colors
still identify their data consistently.

The dashboard shows current conditions and the archive observation time first,
then wind, rain and humidity, followed by a temperature-only preview and a
Tonight entry linking to Astro. Other readings follow these sections. The
overview endpoint still starts before chart initialization and its full-screen
view still offers all series. The preview uses the shared extrema helper.

Mobile navigation retains four labelled destinations and marks the current
section. The hidden mobile menu is inert; opening it moves focus inside, Tab
cycles through its controls and its trigger, and closing restores focus. Keep
the existing bottom safe-area reservation and dynamic viewport menu bounds.
Trends uses GET parameters for metric, range, display, comparison and date, so
browser Back restores the previous selection without a separate persistence
layer. Annual reports continues to open the shared report workspace directly.

Astro night selectors use the forecast dates already available to the page and
mark the selected URL. On small screens its timeline shows sunset, the actual
time midpoint and sunrise to avoid colliding hourly labels. The labelled matrix
remains horizontally scrollable with sticky row labels.

Validate at 320px, 390px and desktop widths in both themes: no page overflow,
visible focus, correct active destination, menu Escape/Tab behavior, metric
links, Trend range changes and Back, direct annual report access, and selected
Astro night navigation. Real iPhone Safari toolbar and safe-area behavior still
requires a device check. If a database is unavailable, use isolated temporary
copies of the templates with clearly identified fixture data; never add fake
observations or a database bypass to production code.
