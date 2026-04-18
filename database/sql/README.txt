================================================================================
FULL DATABASE SCHEMA (ALL TABLES) — ready-made MySQL file
================================================================================

Static import (no PHP / mysqldump required):

  database/sql/opticedge_full_schema_mysql.sql

  - DROP + CREATE for all application tables (Laravel defaults + OpticEdge).
  - Inserts into `migrations` so `php artisan migrate` sees everything applied.
  - Import: mysql -u USER -p NEW_DATABASE < database/sql/opticedge_full_schema_mysql.sql

If anything drifts from future migrations, regenerate with:

================================================================================
FULL DATABASE SCHEMA — generated from your live DB (alternative)
================================================================================

Laravel does not ship one giant hand-written SQL file for the whole app. The
canonical schema is the migrations under database/migrations/.

To produce a SINGLE SQL file that contains EVERY table (CREATE statements, etc.)
as your live database looks right now:

  1) Point .env to the database you want to export (MySQL recommended for prod).

  2) Make sure migrations are applied:
       php artisan migrate

  3) Export schema (no data, except migrations table rows Laravel appends):
       php artisan db:export-schema-sql

     Output:
       database/sql/full_schema_mysql.sql   (MySQL / MariaDB)
       database/sql/full_schema_sqlite.sql  (SQLite)

     Requirements (MySQL): `mysqldump` must be on your PATH (MySQL client).

  Alternative (same result, default path under database/schema/):
       php artisan schema:dump --path=database/sql/full_schema_mysql.sql

  4) Import on another server (example):
       mysql -u USER -p DATABASE < database/sql/full_schema_mysql.sql

Then run seeders if you need data:
       php artisan db:seed

================================================================================
Payment-only manual script (older helper)
================================================================================

See also: manual_payment_options_and_payments.sql
(use only if you are fixing payment/FK issues piecemeal — not the full app).

================================================================================
