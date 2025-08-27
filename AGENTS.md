# Repository Overview

This project is a PHP-based weather web site. It retrieves data from a local `weewx` MySQL database and visualizes it with Highcharts.
Record any additional project decisions or conventions in this file.

## Key Files
- Front-end code lives under `frontend/`:
  - `index.php` is the main dashboard and uses templates in `frontend/includes/`.
  - Additional pages are stored in `frontend/pages/` and static assets in `frontend/assets/`.
- Back-end scripts and database helpers reside in `backend/` such as `dbconn.php` and `getdata.php`.
- Graph pages in `frontend/pages/` use Highcharts to display time series.

## Development Notes
- PHP files generally use two-space indentation.
- There is no automated test suite; run `php -l <file>` on any modified PHP file to check syntax.
- Static assets now reside in `frontend/assets/`.

## Verification
Before committing, run syntax checks for changed PHP files. Example:
```bash
php -l index.php
```
