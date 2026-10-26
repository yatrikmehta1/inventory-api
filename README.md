# Smart Inventory Management API

## Endpoints
| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/categories/tree` | Nested active category tree |
| POST | `/api/products` | Filtered, paginated product listing |
| POST | `/api/stock/adjust` | Transactional stock adjustment |
| GET | `/api/inventory/summary` | Cached stock totals by product |
| GET | `/api/inventory/low-stock?threshold=10` | Paginated low-stock results |
| POST | `/api/products/{id}/movements` | Movement history and database summary |

## Cache keys
- `inventory:categories:tree:v1` — 30 minutes; forgotten on category changes.
- `inventory:products:v{version}:{filter-hash}` — 2 minutes; product version increments on relevant writes.
- `inventory:summary:v1` — 5 minutes; forgotten after stock changes.
- `inventory:low-stock:v{version}:threshold:{n}` — 2 minutes; low-stock version increments after stock changes.

## Indexing decisions
- `categories.slug` unique: avoid duplicate slugs.
- `categories.parent_id`: traverse parent/child relationships.
- `products.sku` unique: prevent duplicate SKU and speed exact lookup.
- `products(category_id, is_active)`: common category/active filters.
- `products.base_price`: price range filter.
- `stock(product_id, warehouse_id)` unique: enforce one balance row per pair and speed exact lookup.
- `stock(warehouse_id, quantity, reserved_quantity)`: warehouse availability reports.
- `stock_movements(product_id, moved_at)`: product history and date filters.
- `stock_movements(warehouse_id, moved_at)`: warehouse history.
- `stock_movements(movement_type, moved_at)`: movement-type aggregations.

## Assumptions
- Stock quantities are whole units and cannot be negative.
- `stock_in` and `stock_out` represent physical movement; reservation operations only change reserved quantity.
- `net_movement = stock_in - stock_out`; reservations are excluded.

## Setup
1. Install PHP 8.1+, Composer, MySQL 8+, Redis, and a Redis PHP extension/client.
2. Create a Laravel 11 project: `composer create-project laravel/laravel inventory-api "^11.0"`.
3. Create database `inventory_api`, copy `.env.example` to `.env`, configure credentials, then run `php artisan key:generate`.
4. Configure API routes in bootstrap/app.php. If needed, run php artisan install:api and define endpoints in routes/api.php.
5. Install Redis on Windows: Download the PHP 8.2-compatible php_redis.dll from PECL Redis(https://pecl.php.net/package/redis/6.3.0/windows) 6.3.0, copy it into PHP's ext folder, and add extension=php_redis.dll to php.ini. Install Memurai for Windows and start/stop its service using net start Memurai / net stop Memurai in an Administrator terminal.
5. Set `CACHE_STORE=redis`; ensure Redis is running and the configured client is installed.
6. Run `php artisan migrate`
6. Run `php artisan db:seed`.
7. Run `php artisan serve` and test endpoints at `http://127.0.0.1:8000/api/...`.


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

