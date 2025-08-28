# Repository Overview

This project is a PHP-based weather web site. It retrieves data from a local `weewx` MySQL database and visualizes it with Highcharts for graphs. Tables are styled with Tailwind utility classes for a lightweight, consistent look.
Record any additional project decisions or conventions in this file.

## Key Files
- `index.php` & `header.php` render the main dashboard and load scripts for live weather conditions.
- `dbconn.php` defines the MySQL connection used across scripts.
- `backend/getdata.php`, `backend/getgraphdata.php`, and similar endpoints expose weather data for charts.
- Graph pages such as `newgraph.php` and `graph*.php` use Highcharts to display time series.

## Development Notes
- PHP files generally use two-space indentation.
- There is no automated test suite; run `php -l <file>` on any modified PHP file to check syntax.
- Static assets like CSS, images, and JavaScript libraries live in the project root or dedicated folders (`css`, `jpg`, etc.), with shared JavaScript collected under `frontend/js/`.
- Tailwind CSS provides project-wide styling and Font Awesome supplies icons.
- Headings use bold **Roboto**, body text **Inter**, and buttons or highlights light **Source Sans Pro**.
- Sections should be wrapped in card components (`bg-white shadow rounded p-4`).

## Verification
Before committing, run syntax checks for changed PHP files. Example:
```bash
php -l index.php
```
