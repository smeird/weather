# Wheathampstead Weather

Wheathampstead Weather is a PHP dashboard for observations stored by a local
[WeeWX](https://weewx.com/) weather station. It presents current conditions,
historical reports, climate summaries, data exports, and interactive charts.
Charts use [Highcharts and Highstock](https://www.highcharts.com/) through the
shared `WeatherCharts` facade, while the interface is built with Tailwind
CSS and Font Awesome.

## Requirements

- PHP 8.2 or later with the MySQLi and zlib extensions
- MySQL or MariaDB containing a WeeWX `archive` table
- A Highcharts licence appropriate to the production deployment
- Composer (for development linting)
- Node.js and npm (only when rebuilding Tailwind CSS)
- A web server whose document root is `frontend/`

The application expects the standard WeeWX fields used by its reports,
including `dateTime`, temperature, humidity, wind, pressure, rain, radiation,
and UV observations.

## Quick start

1. Install the development dependencies:

   ```bash
   composer install
   npm ci
   ```

2. Configure the database connection. The application reads these environment
   variables and otherwise falls back to the development defaults in
   `dbconn.php`:

   | Variable | Default | Purpose |
   | --- | --- | --- |
   | `DB_HOST` | `localhost` | MySQL server hostname |
   | `DB_USER` | `user` | MySQL username |
   | `DB_PASSWORD` | `Pass0!` | MySQL password |
   | `DB_NAME` | `db` | Default database |

   Some queries explicitly address `weewx.archive`, so the configured account
   must also be able to read that schema. Use deployment-specific credentials;
   do not commit secrets.

3. Start a local server with the public directory as its document root:

   ```bash
   DB_HOST=127.0.0.1 DB_USER=weather DB_PASSWORD=secret DB_NAME=weewx \
     php -S 127.0.0.1:8080 -t frontend
   ```

4. Open <http://127.0.0.1:8080/>.

Most pages query the database while rendering, so a reachable WeeWX database is
required to browse the application. The committed `frontend/assets/tailwind.css`
allows the site to run without an asset build.

## Common commands

```bash
# Check every PHP file and run the configured level-0 PHPStan analysis
make lint

# Check PHP syntax only (does not connect to the database)
make lint-php

# Rebuild the minified Tailwind stylesheet
npm run build:css
```

There is currently no database-backed automated test suite. Run `php -l` on
each changed PHP file and avoid invoking pages or endpoints as a test when a
WeeWX database is unavailable.

## Project layout

| Path | Responsibility |
| --- | --- |
| `frontend/index.php` | Main station dashboard |
| `frontend/header.php`, `frontend/footer.php` | Shared page shell and navigation |
| `frontend/backend/` | JSON and download endpoints used by dashboard pages |
| `frontend/js/weather-charts.js` | Shared Highcharts/Highstock constructors and responsive reflow support |
| `frontend/js/garden-image.js` | MQTT garden-camera image consumer |
| `frontend/assets/` | Tailwind source/output and weather-aware hero assets |
| `frontend/images/` | Favicons, station photographs, and other user-facing images |
| `frontend/astro/` | Moon and astronomy views |
| `dbconn.php` | Environment-driven MySQL connection and query helper |
| `bootstrap.php` | Shared response-compression setup |
| `climate_analysis.yml` | Climate-analysis capability catalogue |

## Documentation

- [Architecture and data flow](docs/architecture.md)
- [Development and verification guide](docs/development.md)
- [Contributor conventions](AGENTS.md)

## Security and deployment notes

- Serve only `frontend/` publicly. The repository root contains configuration
  and build files that should not be web-accessible.
- Terminate TLS in the web server or reverse proxy.
- Give the application database account read-only access unless a future
  feature explicitly requires writes.
- Browser-facing charts load Highcharts and other libraries from CDNs; production
  Content Security Policy and firewall rules must allow the configured sources.
- Live garden images are received over MQTT topic `weather/vegimage` and may be
  raw JPEG, PNG, or WebP bytes, or Base64-encoded image data.
