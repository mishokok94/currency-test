## Currency Control Project

This project consumes the public [ExchangeRate-API](https://www.exchangerate-api.com), stores historical rates in MySQL, and visualises the data both on a public page and within a Filament admin dashboard.

### Requirements

- Docker & Docker Compose
- Make, Bash
- Node.js 20+ (only if you plan to run Vite locally instead of inside the container)

### Quick Start

```bash
cp .env.example .env
make install
make start
npm install        # or `make npm`
npm run build      # or `make build`
```

After these steps:

- Public page: http://localhost:8080/
- Filament admin: http://localhost:8080/admin
- Seeded admin user: `admin@example.com` / `password`

### Make Targets

| Target | Description |
| ------ | ----------- |
| `make install` | Composer install |
| `make start` | Start containers, run migrations and seed admin user |
| `make stop` | Stop containers |
| `make sync` | Run single exchange-rate synchronisation |
| `make schedule-run` | Execute due scheduled commands (`exchange:sync` every 12 hours) |
| `make schedule-work` | Start scheduler worker |
| `make logs` | Tail application logs |
| `make test` | Run PHPUnit test suite |
| `make lint` | Run Laravel Pint |
| `make dev` / `make build` | Vite dev server / build |

### Manual Installation (without Make)

```bash
composer install
docker compose up -d
php artisan key:generate
php artisan migrate --seed --class=AdminUserSeeder
npm install
npm run build
```

To run the scheduler:

```bash
php artisan schedule:work
```

or add a cron job calling `php artisan schedule:run`.

### Project Structure

- `app/Services/ExchangeRate/...` – API integration.
- `app/DataTransferObjects` – DTOs for rates.
- `app/Filament/Admin/...` – Filament resources, widgets, dashboard.
- `database/seeders/AdminUserSeeder.php` – admin user seeder.
- `Makefile` – automation commands.
- `bootstrap/app.php` – schedule configuration (`exchange:sync` every 12 hours).

### Configuration

Key environment variables:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
ADMIN_NAME=Administrator
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
EXCHANGE_RATE_API_KEY=<your key>
EXCHANGE_RATE_BASE_CURRENCY=MDL
EXCHANGE_RATE_SYMBOLS=USD,EUR,RON,UAH,RUB,GBP,CHF,PLN,TRY,CAD
```

### Testing

```bash
make test
```

### License

MIT
