# Illuminate Database Plugin
Integrates the Laravel's Illuminate Database Component into the Electro framework

### Introdution

This plugin integrates with `PhpKit\ExtPDO` and the framework's database subsystem, so that connections defined on the
framework's `Connections` service are automatically available as Illuminate Database connections.


## Installation

#### Server Requirements

- PHP >= 5.6
- A fully-functional installation of the Electro framework
- [Laravel's requirements](https://laravel.com/docs/5.2#server-requirements)

#### Installation

To install this plugin on your application, using the terminal, `cd` to your app's directory and type:

```bash
workman install plugin electro-modules/illuminate-database
```

> For correct operation, do not install this package directly with Composer.

## Using the plugin

First, start by injecting the plugin API into your controller (or component, command, migration, etc).

```PHP
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;

class MyController
{
  private $api;

  function __construct (DatabaseAPI $api) {
    $this->api = $api;
  }
}
```

### Using the query builder

##### Example

```PHP
$this->api->query()->from('products')->where('type','box')->get();
```
or simply
```PHP
$this->api->table('products')->where('type','box')->get();
```

### Using the schema builder

##### Example

```PHP
use Illuminate\Database\Schema\Blueprint;

$this->api->schema()->create ('news', function (Blueprint $table) {
    $table->increments ('id');
    $table->string ('title', 100);
});
```

### Facades

This plugin also emulates some common database-related Laravel facades:

- `DB::method()`     - equivalent to `$api->connection ()->method()`
- `Schema::method()` - equivalent to `$api->schema()->method()`

This way, you don't need to inject an API instance to call the query builder or the schema builder.

> **Note:** being an anti-pattern, facades are not recommended for development with Electro.

Be sure to import the related namespaces before using the facades (do **not** use the original facades, it won't work).

- `use Electro\Plugins\IlluminateDatabase\DB;`
- `use Electro\Plugins\IlluminateDatabase\Schema;`

### Using Eloquent

To use Eloquent, access your models as usual, but don't forget to base their classes on ` Electro\Plugins\IlluminateDatabase\BaseModel` instead of `Illuminate\Database\Eloquent\Model`.

##### Example

```
use Electro\Plugins\IlluminateDatabase\BaseModel;

class Article extends BaseModel { }

$article = Article::find(1);
```

### Migrations

#### Available commands

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

