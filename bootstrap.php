<?php

/**
 * Lumber test bootstrap
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

chdir(__DIR__);

$loader = null;

if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
} else if (file_exists('../../autoload.php')) {
    $loader = include '../../autoload.php';
} else {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$loader->add('MvlabsLumberTest',__DIR__.DIRECTORY_SEPARATOR."tests");

if (!$config = @include 'configuration.php') {
    $config = require 'TestConfiguration.php.dist';
}
