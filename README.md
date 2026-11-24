# Esprit-PIDEV-2A4--2026-CityFix
This project was developed as part of the PIDEV – 2nd Year Engineering Program at Esprit School of Engineering – Tunisia (Academic Year 2026–2027).
# CITYFIX — Smart Citizen Reporting Platform

![Project](https://img.shields.io/badge/Project-Academic-blue)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4)
![Database](https://img.shields.io/badge/Database-MariaDB-orange)
![Architecture](https://img.shields.io/badge/Architecture-MVC-green)

## 1. Project Overview

**CITYFIX** is a smart city platform designed to improve communication between citizens and local authorities.

The platform allows citizens to report urban problems such as:

- Potholes and damaged roads.
- Broken streetlights.
- Waste accumulation.
- Damaged public infrastructure.

Citizens can submit reports containing descriptions, photos and location information. Municipal administrators can manage users, monitor reports and coordinate the resolution process.

The main objective is to contribute to cleaner, safer and more responsive cities through digital transformation.

## 2. Project Objectives

- Facilitate communication between citizens and municipalities.
- Make urban problem reporting easier and faster.
- Improve the monitoring of reported problems.
- Support transparent and efficient municipal management.
- Contribute to sustainable urban development.

## 3. Features

### Current Development — Module 1: User Management

The first developed module focuses on user authentication, account management and role-based access control.

**Front Office:**
- User registration.
- User login and logout.
- Password recovery and reset.
- Citizen profile display and management.
- Password modification.

**Back Office:**
- Administrative dashboard.
- User listing and search.
- User filtering and pagination.
- User creation, consultation, modification and deletion.
- Account activation and deactivation.
- Role management.
- Role-based permissions.

### Planned Modules

The following modules are planned as part of the complete CITYFIX platform:

- Citizen urban problem reporting.
- Management and tracking of reports.
- Municipal services and interventions.
- Administrative document management.

*These modules are under development or planned and should not be considered completed features.*

## 4. Technology Stack

### Frontend
- HTML5
- CSS3
- JavaScript
- Responsive user interface
- Local fonts and SVG assets

### Backend
- PHP 8.2
- MySQL / MariaDB
- PDO
- PHPMailer 6.9.3

### Development Tools
- Visual Studio Code
- XAMPP
- Git
- GitHub
- phpMyAdmin

## 5. Architecture

CITYFIX follows a custom MVC-inspired architecture to separate application responsibilities.

```text
CityFix/
│
├── Controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── HomeController.php
│   ├── RoleController.php
│   └── UserController.php
│
├── Models/
│   ├── UserModel.php
│   ├── RoleModel.php
│   └── PermissionModel.php
│
├── Views/
│   ├── admin/
│   ├── includes/
│   ├── layout.php
│   └── layout_admin.php
│
├── config/
│   ├── app.php
│   └── database.php
│
├── database/
│   ├── cityfix.sql
│   └── demo_data.sql
│
├── public/
│   └── assets/
│       ├── css/
│       ├── fonts/
│       ├── img/
│       └── js/
│
├── storage/
├── tests/
├── vendor/
├── index.php
├── composer.json
└── README.md
```

### Main Components

- **Controllers:** Handle application requests, business operations and database interactions.
- **Models:** Represent application entities and data structures.
- **Views:** Display application pages and user interfaces.
- **Config:** Contains application and database configuration.
- **Database:** Contains SQL scripts used to initialize and populate the database.
- **Public:** Contains CSS, JavaScript, fonts and image assets.

The root `index.php` acts as the main entry point and routes requests to the appropriate controllers.

## 6. Database

The current database includes the following main tables:

| Table | Description |
|---|---|
| `users` | Stores user accounts and information |
| `roles` | Defines application roles |
| `permissions` | Defines available permissions |
| `role_permission` | Associates roles with permissions |

The application currently uses three main roles:

- Citizen
- Agent
- Administrator

## 7. Security

The user management module includes several security mechanisms:

- Password hashing and verification.
- PDO prepared statements.
- Session management and expiration.
- Session ID regeneration after authentication.
- CSRF protection.
- Output escaping to reduce XSS risks.
- Server-side role and permission checks.
- Password reset tokens.
- Security-related HTTP headers.
- Protection against deleting or disabling the last active administrator.

## 8. Installation and Getting Started

### Prerequisites

Install the following tools:

- XAMPP with PHP 8.2.
- MySQL or MariaDB.
- A web browser.

### Step 1 — Clone the Repository

Open a terminal inside the XAMPP `htdocs` directory:

```bash
git clone https://github.com/youssefsmeti/Esprit-PIDEV-2A4--2026-CityFix.git
```

### Step 2 — Configure the Database

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Open phpMyAdmin:

   `http://localhost/phpmyadmin`

3. Create a database named `cityfix`.
4. Import the SQL file:

   `database/cityfix.sql`

5. If you need demonstration accounts, optionally import:

   `database/demo_data.sql`

**Important:** The `cityfix.sql` script contains table deletion instructions (`DROP TABLE IF EXISTS`). Importing it into an existing database can delete existing data. Use it for a fresh installation or after backing up your database.

### Step 3 — Configure the Application

Check the database settings in:

`config/database.php`

Configure the database host, database name, username and password according to your local XAMPP installation.

### Step 4 — Run the Application

Open the following URL:

`http://localhost/Esprit-PIDEV-2A4--2026-CityFix/`

If you rename the project directory, adapt the URL accordingly.

## 9. Demonstration Accounts

The SQL scripts provide local demonstration accounts for testing.

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@cityfix.tn` | `Admin@2026` |
| Agent | `agent@cityfix.tn` | `Agent@2026` |
| Citizen | `citoyen@cityfix.tn` | `Citoyen@2026` |

**Security notice:** These credentials are intended exclusively for local academic demonstrations. Change all default passwords before any real deployment. Never reuse these credentials in production.

## 10. Tests

Automated test documentation and test scripts are available in the `tests/` directory.

The project includes:

- `tests/TESTS.md` — Test documentation.
- `tests/http_tests.py` — HTTP test script.
- `tests/resultats_tests_auto_2026-11-23.txt` — Recorded automated test results.

The recorded test report indicates **69/69 automated tests passed** on November 23, 2026.

This result refers to the tests documented in that report and does not imply that every planned CITYFIX module has been completed.

## 11. Contributors

Developed by a team of five engineering students.

| Contributor | Responsibility |
|---|---|
| Member 1 | User Management |
| Member 2 | To be completed |
| Member 3 | To be completed |
| Member 4 | To be completed |
| Member 5 | To be completed |

*Contributor names and final module assignments will be updated as the team progresses.*

## 12. Academic Context

This project was developed as part of the **PIDEV – 2nd Year Engineering Program** at **Esprit School of Engineering – Tunisia**, during the **Academic Year 2026–2027**.

It is part of an academic project focused on digital transformation, economic innovation and smart cities.

## 13. Acknowledgments

We would like to thank our professors and Esprit School of Engineering for their guidance and support throughout this project.

---

**Project:** CITYFIX  
**Repository:** [GitHub — Esprit-PIDEV-2A4--2026-CityFix](https://github.com/youssefsmeti/Esprit-PIDEV-2A4--2026-CityFix)  
**Academic Year:** 2026–2027
