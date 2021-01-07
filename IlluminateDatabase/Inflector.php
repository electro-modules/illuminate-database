<?php

namespace Electro\Plugins\IlluminateDatabase;

/**
 * PATCH: this is a workaround to allow us to use an older version of doctrine/inflector and not get the dreaded
 * deprecation warning. Otherwise we would need to upgrade illuminate/database to a higher version, which could break
 * things.
 * TODO: upgrade illuminate/database to a higher version.
 */
class Inflector
{
  public static function pluralize (string $word): string
  {
    return substr ($word, -1) == 's' ? $word : $word . 's';
  }
}
