<?php

/**
 * DocEngine Static Pages Generator
 * ================================
 *
 * If you don't want to utilize DocEngine's dynamic features,
 * you can use this script to generate a folder with static HTML
 * files from your markdown source.
 *
 * @version: 1
 */
$sourceFolder = isset($argv[1]) ? $argv[1] : 'docs/';
$destFolder = isset($argv[2]) ? $argv[2] : 'static/';

if(!file_exists($sourceFolder . 'docEngine_config.json')){
    die('Error: No docEngine_config.json found in source folder (' . getcwd() . $sourceFolder . ').');
}

if(!file_exists($destFolder)){
    mkdir($destFolder);
}

require('lib/php/Twig/Autoloader.php');

Twig_Autoloader::register();

require 'lib/php/DocEngine/DocEngine.php';

$docEngine = new \DocEngine\DocEngine($sourceFolder);
$docEngine->makeStaticFileStructure();

$loader = new Twig_Loader_Filesystem($docEngine->themeFolder . '/templates');
$twig = new Twig_Environment($loader, array(
        'cache' => $docEngine->mainConfig->cache ? '/lib/cache' : NULL,
));

$docEngine->buildStatic($destFolder);

require 'lib/php/Kiss/Utils.php';

try{
	\Kiss\Utils::mkdir('static/lib/theme/');
	\Kiss\Utils::copy($docEngine->themeFolder, 'static/lib/theme/');
	\Kiss\Utils::copy('docs/demos/', 'static/');
}
catch(ErrorException $e){

}

echo 'Process finished';