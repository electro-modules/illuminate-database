# Illuminate Database Plugin
Integrates the Laravel's Illuminate Database Component into the Electro framework

#### Server Requirements

- PHP >= 5.6
- A fully-functional installation of the Electro framework
- [Laravel's requirements](https://laravel.com/docs/5.2#server-requirements)

## Installation

To install this plugin on your application, using the terminal, `cd` to your app's directory and type:

```bash
workman module:install-plugin electro-modules/illuminate-database
```

> For correct operation, do not install this package directly with Composer.

## Migrations

### Available commands

Command              | Description
---------------------|--------------------------------------------------------------------------
`make:migration`     | Create a new migration for the specified module.
`migration:up`       | Migrate the database for the specified module,
`migration:down`     | Rollback the last migration or a specific one, for the specified module.
`migration:status`   | Show migration status for the specified module.

You can also type `workman` on the terminal to get a list of available commands.

Type `worman help migration:xxx` (where `xxx` is the command name) to know which arguments and options each command supports.

## License

The Electro framework is open-source software licensed under the [MIT license](http://opensource.org/licenses/MIT).

**Electro framework** - Copyright &copy; Cláudio Silva and Impactwave, Lda.

