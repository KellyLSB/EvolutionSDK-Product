<?php
/**
 * Error Handler
 */
const MinimalError = <<<_
<html>
	<head>
		<title>Evolution Setup</title>
		<style>
			body {font-family: sans-serif;}
			p {color: darkred;}
		</style>
	</head>
	<body>
		<h1>Evolution Setup</h1>
		<p>%message</p>
	</body>
_;

/**
 * EvolutionSite Location
 */
define('EvolutionSite', dirname(dirname(Phar::running(false))));

/**
 * Environment File
 */
$environment = dirname(Phar::running(false)) . '/' . 'environment.php';
if(!file_exists($environment))
	die(str_replace('%message', 'Please create <code>' . $environment . '</code>', MinimalError));
require($environment);

/**
 * EvolutionSDK Location
 */
define('EvolutionSDK', __DIR__);

/**
 * Include the startup file
 */
require_once __DIR__.'/kernel/startup.php';

/**
 * Use the evolution router
 */
e::$router->route($_SERVER['REDIRECT_URL']);

__HALT_COMPILER();