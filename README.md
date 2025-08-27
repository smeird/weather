# Weather

This repository hosts a PHP-based weather website. The current structure places scripts in the project root.

## Planned reorganization

To adopt a modern layout with separate frontend and backend components, we will break the work into several steps:

1. Move database and API scripts into a new `backend/` directory.
2. Group user-facing pages and assets under `frontend/`.
3. Extract shared JavaScript into `frontend/js/`.
4. Update templates and includes to reference the new locations.

Each step will be committed separately to minimize merge conflicts.
