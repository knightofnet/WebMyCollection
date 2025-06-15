<?php
require_once('vendor/autoload.php');

session_start();

require_once('apiConf.php');
require_once('siteConf.php');

const SERVER_ROOT = __DIR__;


$siteConf = \MyCollection\app\utils\SiteIniFile::instance(SERVER_ROOT . SITE_SECRET_FILE);

$config = new \MiniPhpRest\core\MiniPhpRestConfig();
$config->setIsDebug(true);
$config->setServerRootPath(SERVER_ROOT);
$config->setAppClassFolders([
    'MyCollection' => 'php'
]);
$config->setUriSliceOffset($siteConf->getValue('miniPhpFw', 'uriSliceOffset', 2));

\MiniPhpRest\Runner::followRoute($routes, $config);