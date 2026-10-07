[![GitHub — ABDEID-dev][github-shield]][github-url]
[![ABDEID License][license-shield]][license-url]

<p align="center">
  <a href="https://github.com/ABDEID-dev">
    <img src="public/assets/img/logo/LOGO.png" alt="ABDEID Logo" width="128" height="128">
  </a>

  <h2 align="center">ABDEID HRMS</h2>

  <p align="center">
    ABDEID — Human Resource Management System
    <br />
    <br />
    <a href="https://github.com/ABDEID-dev">Project Owner: ABDEID</a>
    ·
    <a href="https://hrms.abdeid.com">Project Website</a>
  </p>
</p>
<br />

**HRMS** is a web application tailored to streamline employee management and HR processes within organizations. The upstream HRMS is MIT-licensed; this distribution includes separately licensed additions. See the licensing policy below.

It optimizes organizational efficiency through clear hierarchy establishment, centralized employee records, streamlined attendance and leave management, precise salary processing, timely alerts, comprehensive HR reports, and efficient asset/device tracking.

This concise solution promotes effective workforce management and informed decision-making.

## Live Demo

Explore ABDEID HRMS through the [live demo](https://hrms.abdeid.com/).

### Demo Administrator Access

| Field | Value |
| --- | --- |
| Email | `hrms@abdeid.com` |
| Password | `112233445566` |

These credentials are provided for access to the hosted demo.

### Built With

- [Laravel](https://laravel.com)
- [Livewire](https://livewire.laravel.com)

## Features

- **Organizational Structure:** Establish a clear hierarchy with centers, departments, and positions.

- **Employee Information Management:** Maintain centralized and detailed records of employee information.

- **Process Automation:** Reduces administrative burdens on the department by handling routine tasks.

- **Attendance and Leave Tracking:** Track attendance, manage leave requests, and monitor employee availability.

- **Salary and Deduction Management:** Streamline salary and deduction processes, ensuring accuracy and compliance.

- **Alerts and Messaging System:** Send notifications for important dates and announcements, with integrated SMS and WhatsApp API support to deliver messages directly to employees.

- **Comprehensive HR Reports:** Generate detailed reports for insights into employee performance and attendance.

- **Asset and Device Management:** Includes an assets management module that is currently under development and will enable efficient tracking of organizational assets and devices assigned to employees.

- **Support localization:** Supports both English and Arabic languages, with full localization capabilities, including left-to-right (LTR) and right-to-left (RTL) text directions, to ensure usability and compliance with regional and cultural requirements.

## Screenshots

![Login screen](public/assets/screenshots/Login.png)

### Login

A welcoming sign-in interface with email and password fields, password visibility controls, and a Remember Me option for convenient account access.

![HR dashboard](public/assets/screenshots/Dashboard.png)

### Dashboard

A central overview of HR and financial activity, including active employees, payroll totals, expenses, leave balances, and daily attendance. Quick actions provide convenient access to attendance registration and new records.

![Employee portal](public/assets/screenshots/Employee%20Info.png)

### Employee Portal

A dedicated employee overview bringing together attendance, working hours, absences, salary figures, and recent deductions. Employees can also submit advance requests and communicate with management from the same interface.

![Employee messaging and announcements](public/assets/screenshots/SMS.png)

### Messaging & Announcements

A centralized communication workspace for employee messages and management announcements in Arabic and English. The interface also provides deduction message generation and a summary of messaging activity.

![Attendance records](public/assets/screenshots/Fingerprints.png)

### Attendance Management

An attendance workspace for reviewing employee check-in and check-out records by employee and date range. Filters highlight absences or incomplete attendance records, while manual entry and Excel import support record collection.

![Payroll deduction workflow](public/assets/screenshots/Discount.png)

### Payroll Deductions

A guided workflow that brings holidays, attendance records, and leave information together before the final review and submission step, helping HR teams prepare deductions through a structured process.

![Employee directory](public/assets/screenshots/User.png)

### Employee Directory

A searchable employee list displaying identifiers, names, mobile numbers, and account status. Administrators can locate employee records and add new employees from a single workspace.

![Confidential archive](public/assets/screenshots/filepass.png)

### Confidential Archive

A branch-based workspace for organizing confidential documents in folders protected by a numeric passcode. The interface provides branch selection and folder creation to keep archived materials organized.

## Getting Started

### Requirements

- PHP 8.1 or later.
- Composer.
- MySQL.

### Installation

1. Obtain the ABDEID HRMS source package from ABDEID and extract it. Owner profile: [ABDEID-dev](https://github.com/ABDEID-dev).

2. Navigate to the extracted project folder (replace `HRMS` with its actual folder name):

   ```bash
   cd HRMS

   ```

3. Install dependencies using Composer:

   ```bash
   composer install
   ```

4. Set up the database and necessary configurations:

   - Copy the `.env.example` to `.env` file in the root of your project.
   - Open the `.env` file in the root of your project.

   - Set the database connection details, including `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
   - Set the `APP_TIMEZONE` to 'Asia/Istanbul' or whatever timezone you like.

5. Run the key generate command:

   ```bash
   php artisan key:generate

   ```

6. Run the storage link command:

   ```bash
   php artisan storage:link
   ```

7. Run the migration command with the seed flag to add some fake data:

   ```bash
   php artisan migrate --seed
   ```

8. Run the development server:

   ```bash
   php artisan serve
   ```

9. Open your browser and go to http://localhost:8000 to see the application.

### Usage

10. Login:

    ```bash
    email: admin@demo.com
    password: admin
    ```

## Contribution

For bug reports, feature requests, or contributions, contact [ABDEID-dev on GitHub](https://github.com/ABDEID-dev) or use the phone number below.

## Contact

- **Application owner / Enterprise licensing:** ABDEID
- **Email:** [abdalllah.eiid@gmail.com](mailto:abdalllah.eiid@gmail.com)
- **Phone:** [+971551806906](tel:+971551806906)
- **GitHub:** [ABDEID-dev](https://github.com/ABDEID-dev)
- **Website:** [hrms.abdeid.com](https://hrms.abdeid.com)

## License — ABDEID

Copyright (c) 2026 **ABDEID**, for his original additions.

See [LICENSE](LICENSE) for the licensing scope and [NOTICE](NOTICE) for attribution.
The original HRMS remains under its existing [MIT license](LICENSE.md).
Upstream attribution: [HRMS by Amr Alsaleh](https://github.com/amralsaleeh/HRMS).
ABDEID's original additions, including Enterprise materials, are reserved
under [ABDEID Proprietary License](LICENSE-ENTERPRISE.md) unless explicitly released otherwise.
Only explicitly designated files may use [Apache 2.0](licenses/Apache-2.0.txt);
no files are designated as Apache-licensed merely by including its text here.

Apache 2.0 permits reuse, modification, and redistribution; it does not prevent
source access or require payment for Enterprise. Keep confidential Enterprise
source in a separate **private** GitHub / Hugging Face repository. A public
repository exposes its files regardless of the license. Before any public
release, identify the exact community files and exclude Enterprise source,
credentials, customer data, backups, and assets without redistribution rights.
Ignoring files does not remove files already tracked or present in Git history.

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->

[github-shield]: https://img.shields.io/badge/GitHub-ABDEID--dev-181717?style=flat-square&logo=github
[github-url]: https://github.com/ABDEID-dev
[license-shield]: https://img.shields.io/badge/license-ABDEID%20Proprietary-blue?style=flat-square
[license-url]: LICENSE
