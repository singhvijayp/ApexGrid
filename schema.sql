-- Motorsports Management System
-- Database schema for MySQL 5.5+ (WAMP 2.4 64-bit) / MySQL 8.x / MariaDB 10.x
--
-- How to use:
-- 1) Create a database (e.g. apexgrid) in phpMyAdmin
-- 2) Select the database, then import this file
-- 3) Update includes/config.php with your DB credentials

-- Recommended charset/collation for modern apps
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- USERS
-- Stores application users (for login/registration).
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TEAMS
-- Represents motorsport teams (constructor teams).
CREATE TABLE IF NOT EXISTS teams (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  base_country VARCHAR(80) NULL,
  principal VARCHAR(120) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_teams_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CARS
-- Represents cars used by teams per season (a basic catalogue).
CREATE TABLE IF NOT EXISTS cars (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  team_id INT UNSIGNED NOT NULL,
  model VARCHAR(140) NOT NULL,
  manufacturer VARCHAR(120) NULL,
  season_year SMALLINT UNSIGNED NOT NULL,
  engine VARCHAR(120) NULL,
  horsepower INT UNSIGNED NULL,
  image_url VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cars_team_id (team_id),
  KEY idx_cars_season_year (season_year),
  CONSTRAINT fk_cars_team
    FOREIGN KEY (team_id) REFERENCES teams(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DRIVERS
-- Represents drivers who belong to a team.
CREATE TABLE IF NOT EXISTS drivers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  team_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  nationality VARCHAR(80) NULL,
  date_of_birth DATE NULL,
  driver_number SMALLINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_drivers_team_id (team_id),
  KEY idx_drivers_last_name (last_name),
  CONSTRAINT fk_drivers_team
    FOREIGN KEY (team_id) REFERENCES teams(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DRIVER_STATS
-- Stores aggregate season statistics for each driver (1 row per driver).
CREATE TABLE IF NOT EXISTS driver_stats (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  driver_id INT UNSIGNED NOT NULL,
  races INT UNSIGNED NOT NULL DEFAULT 0,
  wins INT UNSIGNED NOT NULL DEFAULT 0,
  podiums INT UNSIGNED NOT NULL DEFAULT 0,
  poles INT UNSIGNED NOT NULL DEFAULT 0,
  points INT UNSIGNED NOT NULL DEFAULT 0,
  championships INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_driver_stats_driver_id (driver_id),
  CONSTRAINT fk_driver_stats_driver
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CAR_STATS
-- Stores aggregate season statistics for each car (1 row per car).
CREATE TABLE IF NOT EXISTS car_stats (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  car_id INT UNSIGNED NOT NULL,
  races INT UNSIGNED NOT NULL DEFAULT 0,
  wins INT UNSIGNED NOT NULL DEFAULT 0,
  poles INT UNSIGNED NOT NULL DEFAULT 0,
  fastest_laps INT UNSIGNED NOT NULL DEFAULT 0,
  points INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_car_stats_car_id (car_id),
  CONSTRAINT fk_car_stats_car
    FOREIGN KEY (car_id) REFERENCES cars(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data (optional, safe to run repeatedly due to unique constraints + IGNORE)
INSERT IGNORE INTO teams (id, name, base_country, principal) VALUES
  (1, 'Apex Racing', 'UK', 'Jordan Miles'),
  (2, 'Velocity Works', 'Germany', 'Klara Neumann'),
  (3, 'Nimbus Motorsport', 'Italy', 'Marco Bianchi');

INSERT IGNORE INTO cars (id, team_id, model, manufacturer, season_year, engine, horsepower, image_url) VALUES
  (1, 1, 'AR-01', 'Apex', 2026, 'V6 Turbo Hybrid', 1000, 'https://picsum.photos/seed/apexcar/900/600'),
  (2, 2, 'VW-26', 'Velocity', 2026, 'V6 Turbo Hybrid', 1005, 'https://picsum.photos/seed/velocitycar/900/600'),
  (3, 3, 'NM-26', 'Nimbus', 2026, 'V6 Turbo Hybrid', 995, 'https://picsum.photos/seed/nimbuscar/900/600');

INSERT IGNORE INTO drivers (id, team_id, first_name, last_name, nationality, date_of_birth, driver_number) VALUES
  (1, 1, 'Asha', 'Khan', 'India', '1999-04-12', 7),
  (2, 2, 'Lukas', 'Schneider', 'Germany', '1997-09-03', 11),
  (3, 3, 'Giulia', 'Rossi', 'Italy', '2000-01-19', 22);

INSERT IGNORE INTO driver_stats (driver_id, races, wins, podiums, poles, points, championships) VALUES
  (1, 20, 5, 10, 3, 210, 0),
  (2, 20, 6, 11, 4, 225, 0),
  (3, 20, 2, 6, 1, 155, 0);

INSERT IGNORE INTO car_stats (car_id, races, wins, poles, fastest_laps, points) VALUES
  (1, 20, 5, 3, 4, 210),
  (2, 20, 6, 4, 5, 225),
  (3, 20, 2, 1, 2, 155);

