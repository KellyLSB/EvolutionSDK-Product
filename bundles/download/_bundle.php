<?php

namespace Bundles\Download;
use Exception;
use Phar;
use e;

class Bundle {

	public function _on_router_route($path) {
		if(array_shift($path) !== 'download') return;
		$branch = array_shift($path);

		if(empty($branch)) return;

		/**
		 * Build Initial Dir
		 */
		$dir = e\siteCache."/compileFrame";

		/**
		 * Make sure build dirs exists
		 */
		if(!is_dir($dir)) mkdir($dir);
		if(!is_writable($dir)) chmod($dir, 0777);
		if(!is_dir("$dir/build")) mkdir($dir);
		if(!is_writable("$dir/build")) chmod($dir, 0777);
		if(!is_dir("$dir/build/$branch")) mkdir($dir);
		if(!is_writable("$dir/build/$branch")) chmod($dir, 0777);

		/**
		 * Download latest code
		 */
		e\moveFile("https://github.com/EvolutionSDK/EvolutionSDK/tarball/$branch", "$dir/$branch.tar.gz");

		/**
		 * Delete old directory
		 */
		rmdir("$dir/$branch");

		/**
		 * Extract Archive
		 */
		$tar = new Archive_Tar("$dir/$branch.tar.gz");
		$tar->extract("$dir/$branch");

		/**
		 * Rearrange some files
		 */
		$srcRoot = array_shift(glob("$dir/$branch/*", GLOB_ONLYDIR));

		# CLI Interface
		file_put_contents("$srcRoot/cli.php", "<?php echo 'EvolustionSDK currently cannot run from the command line';");

		# Web Interface
		file_put_contents("$srcRoot/web.php", "<?php
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
\$environment = dirname(Phar::running(false)) . '/' . 'environment.php';
if(!file_exists(\$environment))
	die(str_replace('%message', 'Please create <code>' . \$environment . '</code>', MinimalError));
require(\$environment);

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
e::\$router->route(\$_SERVER['REDIRECT_URL']);

__HALT_COMPILER();");

		# Start Compiling the Phar
		if(ini_get('phar.readonly'))
			throw new Exception("php.ini value `phar.readonly` must be set to `0` or we cannot built the framework.");

		$buildRoot = "$dir/build/$branch";

		$phar = new Phar($buildRoot . '/evolutionsdk.phar', 0, 'evolutionsdk.phar');
		$phar->compressFiles(Phar::GZ);
		$phar->setSignatureAlgorithm (Phar::SHA1);

		$phar->buildFromDirectory($srcRoot);
		$phar->setDefaultStub('cli.php', 'web.php');
		$phar = null;

		header('Content-disposition: attachment; filename=evolutionsdk.phar');
		readfile($buildRoot . '/evolutionsdk.phar');

		e\Disable_Trace();
		e\complete();
	}

}