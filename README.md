## Action Matrix Admin

Laravel 13 bootstrap project with:

- Blade-based MVC structure
- Laravel Breeze authentication
- Bootstrap 5 admin shell
- PostgreSQL-first environment defaults

## Local Setup

1. Update PostgreSQL credentials in `.env`.
2. Run `composer install`.
3. Run `php artisan key:generate`.
4. Run `php artisan migrate --seed`.
5. Start the app with `php artisan serve`.

If you want to use the default Laravel frontend toolchain later, install Node.js first and then run `npm install`.

## Default Admin

- Email: `admin@example.com`
- Password: `password`

These values come from `.env` and can be changed before running `php artisan db:seed`.
