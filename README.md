# Illuminate Database Plugin
Integrates the Laravel's Illuminate Database Component into the Electro framework

#### Server Requirements

- PHP >= 5.6
- A fully-functional installation of the Electro framework
- [Laravel's requirements](https://laravel.com/docs/5.2#server-requirements)

## Installation

To install this plugin on your application, using the terminal, `cd` to your app's directory and type:

```bash
workman module:install plugin electro-modules/illuminate-database
```

> For correct operation, do not install this package directly with Composer.

## Migrations

### Available commands

Command              | Description
---------------------|-----------------------------------------------------------------------------------------
`make:migration`     | Create a new database migration.
`make:seeder`        | Create a new database seeder.
`migrate`            | Runs all pending migrations of a module, optionally up to a specific version.
`migrate:refresh`    | Reset and re-run all migrations.
`migrate:reset`      | Rollback all database migrations.
`migration:rollback` | Reverts the last migration of a specific module, or optionally up to a specific version.
`migration:seed`     | Run all available seeders of a specific module, or just a specific seeder.
`migration:status`   | Print a list of all migrations of a specific module, along with their current status.

You can also type `workman` on the terminal to get a list of available commands.

Type `worman help xxx` (where `xxx` is the command name) to know which arguments and options each command supports.

## License

The Electro framework is open-source software licensed under the [MIT license](http://opensource.org/licenses/MIT).

**Electro framework** - Copyright &copy; Cláudio Silva and Impactwave, Lda.

