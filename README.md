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

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Dot.docs Developer Guide

### Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

### Test commands

```bash
php artisan test
npm run build
```

### Security and data protection commands

```bash
php artisan documents:encrypt-content
php artisan backup:create --label=manual --cleanup
php artisan backup:verify backups/<file>.tar.gz
php artisan privacy:export-user <userId>
php artisan privacy:enforce-retention
```

### Monitoring (Laravel Telescope)

- Telescope is installed and migrated.
- Use `TELESCOPE_ENABLED=true` in local/staging environments.
- In production, keep Telescope disabled unless explicitly needed.

## Deployment Guide

### CI workflow

- File: `.github/workflows/ci.yml`
- Runs on push and pull request to `main`
- Steps: Composer install, migrate, PHPUnit, npm build

### Staging deployment workflow

- File: `.github/workflows/deploy-staging.yml`
- Trigger: manual (`workflow_dispatch`)
- Required GitHub secrets:
	- `STAGING_SSH_PRIVATE_KEY`
	- `STAGING_HOST`
	- `STAGING_USER`
	- `STAGING_APP_PATH`

### Production deployment workflow

- File: `.github/workflows/deploy-production.yml`
- Trigger: manual (`workflow_dispatch`)
- Required GitHub secrets:
	- `PROD_SSH_PRIVATE_KEY`
	- `PROD_HOST`
	- `PROD_USER`
	- `PROD_APP_PATH`

### Migration strategy and rollback

Both deploy workflows run the same guarded strategy:

1. Enter maintenance mode.
2. Create pre-deploy backup (`backup:create`).
3. Pull latest `main`.
4. Install dependencies and build assets.
5. Run `php artisan migrate --force`.
6. If migrations fail, run `php artisan migrate:rollback --step=1 --force` and abort deploy.
7. Clear/optimize caches and bring app back up.

### Staging environment template

- File: `.env.staging.example`
- Includes defaults for DB, queue, backups, privacy export path, Telescope, and observability placeholders.
