## Motorsports Management System (PHP + MySQL)

This is a simple, working **motorsports management system** built with:

- **PHP** for frontend + backend rendering (no `.html` files)
- **MySQL** for the database
- **Plain CSS** (`assets/style.css`) for a clean UI

### Features

- **Authentication**: registration, login, logout (sessions + password hashing)
- **Dashboard**: KPI overview + car catalogue preview cards
- **Modules**:
  - **Teams**: create/list/delete
  - **Cars**: catalogue cards + create/delete (requires a team)
  - **Drivers**: create/list/delete (requires a team)
  - **Stats**: view + edit driver stats and car stats

### Folder structure

- `index.php`: redirects to login or dashboard
- `register.php`, `login.php`, `logout.php`
- `dashboard.php`
- `teams.php`, `cars.php`, `drivers.php`, `stats.php`
- `includes/`: config + DB + auth + shared layout helpers
- `assets/style.css`: UI styling
- `schema.sql`: database schema + starter seed data

### Setup (WAMP + phpMyAdmin)

1) **Copy project** into your web root (you already have it here):

- `C:\wamp\www\apexgrid\cursor`

2) **Create the database**

- Open phpMyAdmin (usually `http://localhost/phpmyadmin/`)
- Create a database named: `motorsports_mgmt`

3) **Import the schema**

- Select `gmt`
- Import `schema.sql` from this project folder

4) **Configure DB credentials**

Edit:

- `includes/config.php`

Make sure these match your MySQL settings:

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

5) **Open the app**

Visit:

- `http://localhost/apexgrid/cursor/`

### Notes

- This project uses **PDO prepared statements** to prevent SQL injection.
- Passwords are stored using `password_hash()` and checked with `password_verify()`.
- The SQL file inserts some **starter data** (teams/cars/drivers/stats) so the dashboard isn’t empty.

