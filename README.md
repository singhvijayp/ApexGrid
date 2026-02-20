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

Compatible with **WAMP 2.4 64-bit** (PHP 5.4+) and **WAMP 3.3 32-bit** (PHP 7.x).

1) **Copy project** into your web root:

- **WAMP 2.4 64-bit:** `C:\wamp64\www\apexgrid`
- **WAMP 3.3 32-bit:** `C:\wamp\www\apexgrid`

2) **Create the database**

- Open phpMyAdmin (usually `http://localhost/phpmyadmin/`)
- Create a database named: `apexgrid` (or the name you set in `DB_NAME` in config)

3) **Import the schema**

- Select the database you created (e.g. `apexgrid`)
- Import `schema.sql` from this project folder

4) **Configure DB credentials**

Edit:

- `includes/config.php`

Make sure these match your MySQL settings:

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

5) **Open the app**

Visit (adjust host and path if you use a different folder):

- `http://localhost/apexgrid/`  
  (Ensure `BASE_PATH` in `includes/config.php` matches, e.g. `'/apexgrid'` if the app is in a subfolder.)

### Notes

- This project uses **PDO prepared statements** to prevent SQL injection.
- Passwords are stored using `password_hash()` and checked with `password_verify()`.
- The SQL file inserts some **starter data** (teams/cars/drivers/stats) so the dashboard isn’t empty.

