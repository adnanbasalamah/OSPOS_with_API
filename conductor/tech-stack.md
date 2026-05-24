# Technology Stack

## Backend
- **Language:** PHP 8.1+
- **Framework:** CodeIgniter 4
- **Runtime:** Apache with mod_rewrite / Nginx
- **API Auth:** JWT (firebase/php-jwt)
- **API Format:** JSON

## Database
- **Database Engine:** MySQL 5.7+ / MariaDB 10.3+
- **Driver:** MySQLi (MySQL Improved Extension)
- **Prefix:** `ospos_`

## Frontend
- **CSS Framework:** Bootstrap 3
- **Theme Engine:** Bootswatch (selectable themes)
- **JavaScript:** jQuery
- **UI Components:** Bootstrap Dialog, Bootstrap Select, DateRangePicker, Chartist.js

## Development & Quality
- **Testing:** PHPUnit
  - Integration testing via CIUnitTestCase + FeatureTestTrait
- **Static Analysis:** PHPStan, Psalm
- **Code Quality:** PHP CS Fixer, Rector
- **DevKit:** CodeIgniter 4 DevKit

## Deployment
- **Container:** Docker & Docker Compose
- **PHP Extension:** DOM, MBString, MySQLi, GD (for barcodes), ZIP

## Planned Additions
- *(none currently)*
