# DriveSmart – Car Recommendation System

DriveSmart is a PHP + MySQL web app that recommends cars in Malaysia based on what a
user can actually afford. It works out a maximum car price from net salary, deposit,
loan tenure and interest rate, then matches that budget against a catalogue of cars —
alongside estimated Malaysian road tax, fuel costs, finance tools, EV charger lookup,
commute/travel estimates, and a live fuel-price/top-cars dashboard.

## Features

- **Affordability calculator** – computes maximum loan amount and suitable car price
  from net monthly salary, deposit, loan tenure, and interest rate (18% DSR-style rule).
- **Car recommendations** – filters the car catalogue (brand, model, body type, engine,
  fuel type, price) against the calculated budget.
- **Malaysian road tax estimator** – estimates annual road tax from engine size/fuel
  type using JPJ's tax bands (RM0 for EVs).
- **Finance tools** – dedicated loan/finance calculator page.
- **Travel info** – estimates commute distance/time between home and workplace via
  OpenStreetMap Nominatim (geocoding) and OSRM (routing) — no API key required.
- **EV charging station finder** – looks up nearby charging stations via the
  Open Charge Map API, with on-disk response caching.
- **Live fuel prices** – proxies Malaysia's official weekly RON95/RON97/Diesel price
  dataset from data.gov.my.
- **Top 10 cars dashboard** – tallies JPJ's official vehicle registration data to show
  Malaysia's most-registered cars year-to-date, cached on disk.
- **World clock widget** – via TimeZoneDB, with a built-in PHP timezone fallback if no
  API key is configured.
- **Admin dashboard** – hCaptcha-protected login for managing the car catalogue
  (add/update/delete cars, images, pricing).

## Tech stack

- PHP (PDO/MySQL), vanilla JS, Tailwind CSS
- MySQL database, seeded from `cars.csv`
- External APIs: OpenRouteService, Open Charge Map, Nominatim/OSRM, TimeZoneDB,
  hCaptcha, data.gov.my

## Prerequisites

- PHP 8+ with the `pdo_mysql` and `curl` extensions
- MySQL/MariaDB (e.g. via XAMPP)
- Node.js (only needed to rebuild Tailwind CSS)

## Setup

1. **Clone the repo** into your web server's document root (e.g. `htdocs/` for XAMPP).

2. **Create the database**

   ```sql
   CREATE DATABASE car_recommendation;
   ```

3. **Configure environment variables**

   Copy `.env.example` to `.env` and fill in your secrets:

   ```bash
   cp .env.example .env
   ```

   ```
   HCAPTCHA_SECRET_KEY=your_hcaptcha_secret_key_here
   ```

   Review `config.php` for the remaining settings (DB credentials, OpenRouteService,
   Open Charge Map, TimeZoneDB). Free API keys for each service are linked inline in
   that file. Values can also be overridden via environment variables instead of
   editing the file directly.

4. **Import car data**

   ```bash
   php import_cars.php
   ```

   This creates the `cars` table and imports every row from `cars.csv`.

5. **Install front-end dependencies** (optional, only if you plan to edit styles)

   ```bash
   npm install
   npx @tailwindcss/cli -i ./input.css -o ./output.css --watch
   ```

6. **Run the app**

   Start Apache/MySQL (e.g. via XAMPP) and visit:

   ```
   http://localhost/Car%20Recommendation/
   ```

## Project structure

```
├── templates/           # Front-end HTML views (landing, cars, assessment)
├── Images/               # Car images by brand
├── cache/ocm/            # Cached Open Charge Map responses
├── cars.csv              # Seed data for the car catalogue
├── config.php            # App configuration & API keys
├── db.php / functions.php# DB connection + shared helper functions
├── recommendation.php    # Core recommendation logic
├── cars.php              # Car listing/filtering endpoint
├── finance.php           # Finance/loan calculator page
├── travel_info.php       # Commute distance/time endpoint
├── get_ev_chargers.php   # EV charging station lookup endpoint
├── fuel_price.php        # Live fuel price endpoint
├── top_cars.php          # Top registered cars endpoint
├── get_timezone.php      # World clock endpoint
├── admindashboard.php    # Admin CRUD dashboard
└── login.php             # Admin login (hCaptcha-protected)
```

## Security note

`config.php` is committed to this repo and contains fallback API keys. Before making
this repository public, rotate/remove any real keys from `config.php` and rely on the
`.env` file (already gitignored) for secrets instead.

## License

ISC
