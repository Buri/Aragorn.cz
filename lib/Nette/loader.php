<?php

/**
 * Nette Framework (version 2.0-dev released on 2011-04-13, http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */



/**
 * Check and reset PHP configuration.
 */
if (!defined('PHP_VERSION_ID')) {
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

if (PHP_VERSION_ID < 50200) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}

error_reporting(E_ALL | E_STRICT);
@set_magic_quotes_runtime(FALSE); // @ - deprecated since PHP 5.3.0
iconv_set_encoding('internal_encoding', 'UTF-8');
extension_loaded('mbstring') && mb_internal_encoding('UTF-8');
@header('X-Powered-By: Nette Framework'); // @ - headers may be sent



/**
 * Load and configure Nette Framework
 */
define('NETTE', TRUE);
define('NETTE_DIR', dirname(__FILE__));
define('NETTE_VERSION_ID', 20000); // v2.0.0
define('NETTE_PACKAGE', 'PHP 5.2 prefixed');



require_once dirname(__FILE__) . '/common/exceptions.php';
require_once dirname(__FILE__) . '/common/Object.php';
require_once dirname(__FILE__) . '/Utils/LimitedScope.php';
require_once dirname(__FILE__) . '/Loaders/AutoLoader.php';
require_once dirname(__FILE__) . '/Loaders/NetteLoader.php';
require_once dirname(__FILE__) . '/Diagnostics/Helpers.php';


NNetteLoader::getInstance()->register();

NSafeStream::register();



/**
 * NCallback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return NCallback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof NCallback) ? $callback : new NCallback($callback, $m);
}



/**
 * NDebug::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) NDebug::dump($arg);
	return $var;
}
