<?php

/**
 * @file
 * Bootstrap file for the Mailin API.
 */

spl_autoload_register('mailin_autoloader');

/**
 * Autoload callback.
 *
 * @param $class
 *   The class name.
 */
function mailin_autoloader($class) {
  $parts = explode('\\', $class);

  if (array_shift($parts) === 'Mailin' && $parts) {
    $file = __DIR__ . (reset($parts) === 'Tests' ? '/' : '/Classes/')  . implode('/', $parts)  . '.php';

    if (file_exists($file)) {
      include $file;
    }
  }
}
