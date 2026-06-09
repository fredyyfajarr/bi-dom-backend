```text
__________.___  ________   ________      _____    __________                
\______   \   | \______ \  \_____  \    /     \   \______   \_____    ____  
 |    |  _/   |  |    |  \  /   |   \  /  \ /  \   |    |  _/\__  \ _/ ___\ 
 |    |   \   |  |    `   \/    |    \/    Y    \  |    |   \ / __ \\  \___ 
 |______  /___| /_______  /\_______  /\____|__  /  |______  /(____  /\___  >
        \/              \/         \/         \/          \/      \/     \/ 
 __                      .___                                               
|  | __ ____   ____    __| _/                                               
|  |/ // __ \ /    \  / __ |                                                
|    <\  ___/|   |  \/ /_/ |                                                
|__|_ \\___  >___|  /\____ |                                                
     \/    \/     \/      \/                                                
```

<div align="center">
  <p align="center">
    <a href="https://github.com/fredyyfajarr/bi-dom-backend/issues">
      <img src="https://img.shields.io/github/issues/fredyyfajarr/bi-dom-backend?style=for-the-badge&color=red" alt="Issues" />
    </a>
    <a href="https://github.com/fredyyfajarr/bi-dom-backend/pulls">
      <img src="https://img.shields.io/github/issues-pr/fredyyfajarr/bi-dom-backend?style=for-the-badge&color=red" alt="Pull Requests" />
    </a>
    <a href="https://github.com/fredyyfajarr/bi-dom-backend/stargazers">
      <img src="https://img.shields.io/github/stars/fredyyfajarr/bi-dom-backend?style=for-the-badge&color=red" alt="Stars" />
    </a>
  </p>
</div>

## Table of Contents
- [About The Project](#about-the-project)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Usage](#usage)
- [Contributing](#contributing)
- [License / Copyright](#license--copyright)

## About The Project

BI DOM Backend is a specialized Business Intelligence and Inventory Management REST API built on Laravel 13. It is specifically designed to crunch transactional data, generate inventory forecasts, and provide actionable business metrics securely and efficiently to its client applications.

The platform provides a solid foundation for enterprise reporting, enabling CSV transaction imports, automated COGS (Cost of Goods Sold) calculations, and dynamic PDF report generation. It heavily utilizes Laravel Octane for high-performance requests, making it exceptionally fast when handling bulk reporting data or heavy forecast algorithms.

## Key Features

- **Inventory Forecasting Algorithm:** Built-in predictive metrics to forecast inventory requirements based on historical transactions.
- **Transaction Processing:** High-throughput transactional data ingestion, including support for bulk CSV imports and automated COGS linking.
- **Reporting & Exports:** Dynamic report generation capabilities allowing business users to export analytical results as pristine PDFs (`barryvdh/laravel-dompdf`).
- **High Performance:** Configured out-of-the-box with Laravel Octane and FrankenPHP to ensure minimal latency and maximal request throughput.
- **Robust Security:** Stateful and Token-based authentication using Laravel Sanctum to protect sensitive BI data.
- **Comprehensive Test Suite:** Highly tested logic using PHPUnit, especially around the tricky Inventory Forecast Calculator constraints.

## Tech Stack

- **Framework:** [Laravel 13](https://laravel.com/)
- **Runtime:** PHP ^8.3 (with FrankenPHP via Octane)
- **Database:** Relational DB (MySQL / PostgreSQL supported)
- **Authentication:** Laravel Sanctum
- **PDF Generation:** `laravel-dompdf`
- **Testing:** PHPUnit, Faker, Mockery

## Project Structure

```text
bi-dom-backend/
├── app/                  # Application core (Controllers, Models, BI Services)
├── config/               # Laravel configuration files
├── database/             # Migrations, Model Factories, and complex Seeders
│   ├── migrations/       # Schema definitions (users, inventory, transactions, etc.)
│   ├── samples/          # Sample CSV files for import testing
│   └── seeders/          # Database population classes
├── public/               # Web root and FrankenPHP worker entry point
├── resources/            # Views (Blade templates for PDF reports)
├── routes/               # API and Web route definitions
├── storage/              # Generated PDFs, Logs, and Framework cache
└── tests/                # Feature and Unit tests (Forecast algorithms, CSV parsers)
```

## Getting Started

### Prerequisites
- PHP >= 8.3
- Composer
- A Database Engine (MySQL, PostgreSQL, or SQLite)
- Node.js & npm (for potential asset bundling)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/fredyyfajarr/bi-dom-backend.git
   ```
2. Navigate into the application directory:
   ```bash
   cd bi-dom-backend
   ```
3. Run the automated setup script. This installs dependencies, creates an `.env` file, generates an app key, runs migrations, and builds assets:
   ```bash
   composer setup
   ```
4. *Alternatively*, set up manually by running `composer install`, copying `.env.example` to `.env`, generating the app key, and running `php artisan migrate`.

## Usage

1. Start the development server (runs the server, queue worker, and log listener concurrently):
   ```bash
   composer dev
   ```
   *(Or simply run `php artisan serve` if you prefer the standard approach)*
2. To seed the database with realistic business intelligence transaction data:
   ```bash
   php artisan db:seed --class=RealisticTransactionSeeder
   ```
3. To execute the test suite:
   ```bash
   composer test
   ```

## Contributing

Contributions are highly valued. Whether it's adding new forecasting models or fixing reporting logic, please feel free to fork the repository.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License / Copyright

Copyright &copy; 2026 Fredy Fajar Adi Putra. All Rights Reserved.
