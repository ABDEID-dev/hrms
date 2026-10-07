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

Screenshots captured from the hosted demo on October 7, 2026. Expand each group to explore its sections. Some screens show empty states because no transactions or requests have been recorded.

![Login screen](public/assets/screenshots/Login.png)

### Login

The sign-in interface provides account access using an email address or username and password.

<details>
<summary><strong>Dashboard & Employee Workspace</strong></summary>

![ Dashboard](public/assets/screenshots/Live-dashboard.jpg)

### Dashboard

An overview of employee activity, attendance, leave balances, payroll totals, expenses, and available cash.

![ Employee Portal](public/assets/screenshots/Live-employee-portal.jpg)

### Employee Portal

A personal workspace summarizing attendance, working hours, salary figures, deductions, and requests to management.

![ Management Response Center](public/assets/screenshots/Live-employee-management-responses.jpg)

### Management Response Center

A consolidated view of employee requests, internal messages, management replies, and approval status.

![ Employee Complaints](public/assets/screenshots/Live-employee-complaints.jpg)

### Employee Complaints

A complaint submission interface with supporting files and optional audio recording, alongside the employee’s recent submissions.

</details>

<details>
<summary><strong>Attendance & Organization</strong></summary>

![ Attendance Records](public/assets/screenshots/Live-attendance-fingerprints.jpg)

### Attendance Records

Review check-in and check-out records by employee and date range, filter absences, and access manual entry or Excel import.

![ Weekly Leave Schedule](public/assets/screenshots/Live-attendance-leaves.jpg)

### Weekly Leave Schedule

Assign weekly days off and review employee schedules in a clear seven-day overview.

![ Centers](public/assets/screenshots/Live-structure-centers.jpg)

### Centers

Organize work centers and review member counts, working hours, and days off.

![ Departments](public/assets/screenshots/Live-structure-departments.jpg)

### Departments

Maintain departments and view their member counts as part of the organizational structure.

![ Positions](public/assets/screenshots/Live-structure-positions.jpg)

### Positions

Manage job positions and review vacancy counts within the organization.

![ Employee Directory](public/assets/screenshots/Live-structure-employees.jpg)

### Employee Directory

Find employee records and review employee identifiers, names, mobile numbers, and active status.

![ Employee Documents](public/assets/screenshots/Live-structure-employee-documents.jpg)

### Employee Documents

Organize employee identification documents and receipts, with document types and PDF or image uploads.

</details>

<details>
<summary><strong>Messaging, Requests & Customers</strong></summary>

![ Bulk Messaging](public/assets/screenshots/Live-messages-bulk.jpg)

### Bulk Messaging

A staged workspace for preparing message text, recipient numbers, and verification, with messaging activity totals.

![ Personal Messaging & Announcements](public/assets/screenshots/Live-messages-personal.jpg)

### Personal Messaging & Announcements

Prepare employee messages, publish bilingual management announcements, and access deduction summaries and messaging channels.

![ Employee Requests](public/assets/screenshots/Live-messages-employee-requests.jpg)

### Employee Requests

Review employee requests using status filters for pending, answered, approved, rejected, and cancelled requests.

![ Complaint Management](public/assets/screenshots/Live-messages-complaints.jpg)

### Complaint Management

Review employee complaints and their supporting attachments using complaint status filters.

![ Deleted Documents](public/assets/screenshots/Live-messages-deleted-documents.jpg)

### Deleted Documents

Review complaint attachments that have been removed and retained for administrator review.

![ Payroll Deductions](public/assets/screenshots/Live-discounts.jpg)

### Payroll Deductions

Record deductions by employee, amount, and reason, then review the dated deduction register with a print option.

![ Customers](public/assets/screenshots/Live-customers.jpg)

### Customers

Maintain customer profiles and service histories without requiring customers to have login accounts.

</details>

<details>
<summary><strong>Payroll, Reports & Activity</strong></summary>

![ Salary Report](public/assets/screenshots/Live-salary-report.jpg)

### Salary Report

A monthly summary of salaries, withdrawals, attendance values, and available treasury cash for payroll.

![ Employee Revenue Report](public/assets/screenshots/Live-accounts-employee-revenues.jpg)

### Employee Revenue Report

Review employee revenue and tips by month or date range, with branch filters and a printable report.

![ Daily Treasury Audit](public/assets/screenshots/Live-accounts-treasury-audit.jpg)

### Daily Treasury Audit

Review daily revenue, expenses, opening and closing cash balances, historical snapshots, and recorded treasury changes.

![ Employee Activity Tracking](public/assets/screenshots/Live-employee-tracking.jpg)

### Employee Activity Tracking

Review recorded system access and attendance events by employee and date range, including available location records.

![ Administrator Activity Tracking](public/assets/screenshots/Live-admin-tracking.jpg)

### Administrator Activity Tracking

Review administrator access and recorded operations, with separate totals for edits and deletions.

![ Expense Reports](public/assets/screenshots/Live-accounts-expense-reports.jpg)

### Expense Reports

Analyze purchases, withdrawals, tips, and employee advances by branch, expense type, and reporting period.

![ Payroll Payments](public/assets/screenshots/Live-accounts-payroll-payments.jpg)

### Payroll Payments

Review monthly payroll funding, outstanding employee salaries, withdrawals, and payment totals.

</details>

<details>
<summary><strong>Maktoum Accounts</strong></summary>

![ Maktoum Revenue](public/assets/screenshots/Live-accounts-maktoom-revenues.jpg)

### Maktoum Revenue

Record service or product revenue by employee and payment method, with monthly cash, card, and expense summaries.

![ Maktoum Expenses](public/assets/screenshots/Live-accounts-maktoom-expenses.jpg)

### Maktoum Expenses

Review branch purchases, cash withdrawals, and tips, with daily summaries and dated expense records.

![ Maktoum Treasury](public/assets/screenshots/Live-accounts-maktoom-treasury.jpg)

### Maktoum Treasury

Review opening cash, revenue, expenses, net movement, and closing treasury balances for a selected period.

![ Maktoum Monthly Income Report](public/assets/screenshots/Live-accounts-maktoom-monthly-income-report.jpg)

### Maktoum Monthly Income Report

Compare monthly sales, cash and card income, product and service revenue, expenses, payroll, and net totals.

</details>

<details>
<summary><strong>Avani Accounts</strong></summary>

![ Avani Revenue](public/assets/screenshots/Live-accounts-avani-revenues.jpg)

### Avani Revenue

Record service or product revenue by employee and payment method, with monthly cash, card, and expense summaries.

![ Avani Expenses](public/assets/screenshots/Live-accounts-avani-expenses.jpg)

### Avani Expenses

Review branch purchases, cash withdrawals, and tips, with daily summaries and dated expense records.

![ Avani Treasury](public/assets/screenshots/Live-accounts-avani-treasury.jpg)

### Avani Treasury

Review opening cash, revenue, expenses, net movement, and closing treasury balances for a selected period.

![ Avani Monthly Income Report](public/assets/screenshots/Live-accounts-avani-monthly-income-report.jpg)

### Avani Monthly Income Report

Compare monthly sales, cash and card income, product and service revenue, expenses, payroll, and net totals.

</details>

<details>
<summary><strong>Perfumes Accounts</strong></summary>

![ Perfumes Revenue](public/assets/screenshots/Live-accounts-perfumes-revenues.jpg)

### Perfumes Revenue

Record perfume product sales and review cash, card, expense, and monthly revenue summaries.

![ Perfumes Expenses](public/assets/screenshots/Live-accounts-perfumes-expenses.jpg)

### Perfumes Expenses

Review branch purchases, cash withdrawals, and tips, with daily summaries and dated expense records.

![ Perfumes Treasury](public/assets/screenshots/Live-accounts-perfumes-treasury.jpg)

### Perfumes Treasury

Review opening cash, revenue, expenses, net movement, and closing treasury balances for a selected period.

![ Perfumes Monthly Income Report](public/assets/screenshots/Live-accounts-perfumes-monthly-income-report.jpg)

### Perfumes Monthly Income Report

Compare monthly sales, cash and card income, product and service revenue, expenses, payroll, and net totals.

</details>

<details>
<summary><strong>Holidays, Statistics & Administration</strong></summary>

![ Public Holidays](public/assets/screenshots/Live-holidays.jpg)

### Public Holidays

Maintain holiday names, associated centers, date ranges, and notes for attendance planning.

![ Statistics](public/assets/screenshots/Live-statistics.jpg)

### Statistics

Select a reporting period to review deduction statistics and access report export controls.

![ User Accounts](public/assets/screenshots/Live-settings-users.jpg)

### User Accounts

Review login accounts linked to employee profiles, including assigned roles and branch access.

![ Roles](public/assets/screenshots/Live-settings-roles.jpg)

### Roles

Review system roles, descriptions, assigned users, and permission counts.

![ Permissions](public/assets/screenshots/Live-settings-permissions.jpg)

### Permissions

Browse system permissions grouped by functional area to understand available access controls.

![ Log Viewer](public/assets/screenshots/Live-log-viewer.jpg)

### Log Viewer

Search and browse application log files through the dedicated log viewer; the captured demo has no log files.

</details>

<details>
<summary><strong>Dyes, Inventory, Services & Archive</strong></summary>

![ Salon Dye Usage](public/assets/screenshots/Live-maktoom-dye-revenues.jpg)

### Salon Dye Usage

Prepare employee dye usage entries with product quantities and review recorded stock consumption.

![ Dye Inventory](public/assets/screenshots/Live-maktoom-dyes.jpg)

### Dye Inventory

Review dye colors, available quantities, total stock, and incoming stock movements.

![ Dye Reports](public/assets/screenshots/Live-maktoom-dye-reports.jpg)

### Dye Reports

Review dye consumption by employee, product, and date, including stock balances before and after deductions.

![ Inventory](public/assets/screenshots/Live-assets-inventory.jpg)

### Inventory

Review branch product quantities in pieces or grams, category summaries, prices, and recent stock movements.

![ Salon Services](public/assets/screenshots/Live-salon-services.jpg)

### Salon Services

Maintain salon service names, prices, branch assignments, and active status.

![ Invoices](public/assets/screenshots/Live-salon-invoices.jpg)

### Invoices

Prepare customer invoices with multiple services, payment details, and participating employees, and review recent invoices.

![ Inventory Reports](public/assets/screenshots/Live-assets-reports.jpg)

### Inventory Reports

Review product balances, stock valuation, sales, and dated inventory movements across branches.

![ Confidential Archive](public/assets/screenshots/Live-secret-archive.jpg)

### Confidential Archive

Organize branch documents in folders protected by numeric passcodes.

</details>

## Getting Started

### Requirements

- PHP 8.4 or later.
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
    email: hrms@abdeid.com
    password: 112233445566
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
Project author: [Abdallah Eid](https://github.com/ABDEID-dev).
Upstream source: [HRMS](https://github.com/amralsaleeh/HRMS).
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
