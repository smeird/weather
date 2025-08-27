# Weather

This repository hosts a PHP-based weather website. Graphs are rendered with [Highcharts](https://www.highcharts.com/), while interactive tables use [Tabulator](https://tabulator.info/) with its *Simple* theme and Tailwind utility classes. Tailwind CSS handles styling, Font Awesome provides icons, and the layout wraps sections in card components. Typography follows: headings in bold Roboto, body text in Inter, and buttons or highlights in light Source Sans Pro. Shared JavaScript libraries live under `frontend/js/`.

## Planned reorganization

To adopt a modern layout with separate frontend and backend components, we will break the work into several steps:

1. Move database and API scripts into a new `backend/` directory.
2. Group user-facing pages and assets under `frontend/`.
3. Extract shared JavaScript into `frontend/js/`.
4. Update templates and includes to reference the new locations.

Each step will be committed separately to minimize merge conflicts.
