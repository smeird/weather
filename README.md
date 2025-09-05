# Weather

This repository hosts a PHP-based weather website. Graphs are rendered with [Highcharts](https://www.highcharts.com/), and tables are styled with Tailwind utility classes for a clean, responsive layout. Tailwind CSS handles styling, Font Awesome provides icons, and the layout wraps sections in card components. Typography follows: headings in bold Roboto, body text in Inter, and buttons or highlights in light Source Sans Pro. Shared JavaScript libraries live under `frontend/js/`, and Highcharts assets are pulled from the official CDN.

## File Overview

- `AGENTS.md`: Development guidelines and conventions.
- `Makefile`: Build tasks for assets.
- `README.md`: This documentation file.
- `Smeird.pem`: SSL certificate placeholder.
- `frontend/astro/`: Astronomical pages such as moon phases.
- `browserconfig.xml`: Microsoft browser tile configuration.
- `cleandata.php`: Script that sanitizes raw weather data.
- `composer.json`: PHP dependency definitions.
- `css/`: Legacy stylesheet collection.
- `dbconn.php`: Defines the MySQL connection.
- `extremes.php`: Displays historical weather extremes.
- `footer.php`: Shared page footer.
- `frontend/`: Client-side assets for the newer layout.
- `full1.php`: Full weather report page.
- `backend/getdata.php`: Endpoint returning current conditions.
- `backend/metric-data.php`, `backend/range-data.php`: Data providers for charts.
- `google984c37b34dbda4e6.html`: Google site verification file.
- `metric-graph.php`, `range-graph.php`, `overview-graph.php`: Legacy graph pages.
- `header.php`: Shared header and navigation.
 - `index.php`: Main dashboard.
 - `iui/`: Mobile UI resources.
- `frontend/images/`: Site icons and other static images (`android-chrome-192x192.png`, `android-chrome-512x512.png`, `apple-touch-icon.png`, `favicon-16x16.png`, `favicon-32x32.png`, `favicon.ico`, `icon.png`, `mstile-150x150.png`, `safari-pinned-tab.svg`, and its `jpg/` subfolder).
 - `maxmin.php`: Daily max/min summaries.
- `backend/multidata.php`: Combined data view.
- `dynamic-graph.php`: Newer graph interface.
- `node_modules/`: Node.js dependencies.
- `package.json`: Node package manifest.
- `picture.php`: Generates image pages.
- `postcss.config.js`: PostCSS configuration.
- `proxy.pac`, `wpad.dat`: Proxy auto-configuration scripts.
- `records.php`: Tabular weather records.
- `reportrainyeartotals.php`, `reporttempyeartotals.php`, `reportwindyeartotals.php`: Yearly totals reports.
- `report.php`: General reporting page.
- `backend/schedule.php`: Cron-style scheduler.
- `sitemap.xml`: Sitemap for search engines.
- `frontend/images/snap.jpeg`: Example snapshot image.
- `frontend/graph-selector.php`: Graph selection form.
- `backend/winddata.php`: Wind data endpoint.
- `windrose.php`: Wind rose visualization.

## Planned reorganization

To adopt a modern layout with separate frontend and backend components, we will break the work into several steps:

1. **Done:** Database and API scripts now reside in `backend/`.
2. Group user-facing pages and assets under `frontend/`.
3. Extract shared JavaScript into `frontend/js/`.
4. Update templates and includes to reference the new locations.

Each step will be committed separately to minimize merge conflicts.

## Climate Analysis

### Temperature
- calculate monthly and annual mean, min, max
- compute seasonal averages (winter, spring, summer, autumn)
- identify annual extremes: hottest/coldest days, longest hot/cold spells
- generate degree days (HDD, CDD)
- build frequency distributions (e.g. nights below 0°C)

### Rainfall
- compute monthly and annual totals
- count rain days (≥0.1 mm), wet days (≥1 mm), heavy rain days (≥10 mm)
- identify longest dry and wet spells
- calculate rainfall intensity
- determine rainfall percentiles (e.g. 95th percentile daily rainfall)

### Humidity
- calculate monthly and annual averages
- track daily max and min humidity
- count days >90% or <30% humidity
- derive dew point trends

### Wind
- generate wind roses (direction + speed distribution)
- calculate monthly and annual average wind speeds
- identify gust statistics (monthly/yearly maxima)
- determine prevailing wind direction
- calculate calm frequency (wind <0.5 m/s)

### Derived Indices
- compute Heat Index (temperature + humidity)
- compute Wind Chill (temperature + wind)
- estimate potential evapotranspiration
- count storm days (defined by thresholds)

### Climatological Summaries
- produce monthly climate summaries (means, extremes, totals)
- compile annual climate report (tables, extremes, charts)
- calculate anomalies vs baseline (multi-year averages)
- perform trend analysis (linear regression over years)

### Extreme Value Statistics
- estimate rainfall return periods (e.g. 50 mm/day events)
- estimate return levels for maximum wind gusts
- define percentile-based rare event thresholds

### Visualisation and Reporting
- generate standard plots (line, bar, anomaly, cumulative rainfall)
- create wind roses and histograms
- output annual "climate report" with tables, charts, commentary
