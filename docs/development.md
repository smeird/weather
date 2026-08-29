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
