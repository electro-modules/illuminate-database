<?php
use Electro\Plugins\IlluminateDatabase\AbstractMigration;
use Illuminate\Database\Schema\Blueprint;

/**
 * A database migration that uses the Illuminate Database API for interacting with the database.
 *
 * ### The Illuminate Database API
 *
 * You can access the Illuminate Database API via the `db` property.
 *
 * The most relevant methods for writing migrations:
 *
 * - `$this->db->schema()`     - returns an instance of the Illuminate Database schema builder.
 * - `$this->db->query()`      - returns an instance of the Illuminate Database query builder.
 * - `$this->db->table($name)` - a shortcut to `$this->query()->from($name)`.
 *
 * ### Eloquent
 *
 * You can use Eloquent models as usual.
 *
 * > Ex: `$user = User::find(1);`
 *
 * ### Facades
 *
 * You can also use these Laravel-compatible facades:
 *
 * - `DB::method()`     - equivalent to `$this->query()->method()`
 * - `Schema::method()` - equivalent to `$this->schema()->method()`
 *
 * > **Note:** be sure to import the correct namespaces before using these facades:
 * > ``
 * > use Electro\Plugins\IlluminateDatabase\DB;
 * > use Electro\Plugins\IlluminateDatabase\Schema;
 * > ```
 * > Do not use the original Illuminate facades, they will not work!
 *
 * > **Note:** although facades are an anti-pattern and its use is discouraged by Electro, migrations is one of those
 * few cases where they are ok to use, if you wish to. Beware that you will NOT get IDE autocompletion and code analysis
 * though.
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * <p>
 * To suppress the inclusion of this documentation block on future migrations, add the `--no-doc` or `-d` option to the
 * migration command.
 *
 * > Ex: `workman make:migration -d`
 */
class __CLASS__ extends AbstractMigration
{
  /**
   * Run the migration.
   *
   * @return void
   */
  function up ()
  {
    $schema = $this->db->schema ();

    // Pick one of the alternatives below (and delete the other one):

    // Create a new table
    $schema->create ('tableName', function (Blueprint $table) {
      $table->increments ('id');
      $table->timestamps ();
      // More columns...
    });

    // Or

    // Modify an existing table
    $schema->table ('tableName', function (Blueprint $table) {
      // Your code here
    });
  }

  /**
   * Reverse the migration.
   *
   * @return void
   */
  function down ()
  {
    $schema = $this->db->schema ();
    // WARNING: do NOT test for table existence, as this code will run when migrating (for storing the queries on the migrations table), NOT when rolling back!

    // Pick one of the alternatives below (and delete the other one):

    // Drop the tables created on up(), in reverse order
    $schema->drop ('tableName');

    // Or

    // Undo the modifications made to the table on up()
    $schema->table ('tableName', function (Blueprint $table) {
      // Your code here
    });
  }

}
