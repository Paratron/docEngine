<?php

require('lib/php/Twig/Autoloader.php');

Twig_Autoloader::register();

require 'lib/php/DocEngine/DocEngine.php';

$docEngine = new \DocEngine\DocEngine();

$docEngine->init();

$loader = new Twig_Loader_Filesystem($docEngine->themeFolder . '/templates');
$twig = new Twig_Environment($loader, array(
        'cache' => $docEngine->mainConfig->cache ? '/lib/cache' : NULL,
));

$docEngine->callHook('beforeRender');

$result = $twig->render('base.twig', array(
        'docEngine' => $docEngine,
        'lang' => $docEngine->readLanguage(),
        'config' => $docEngine->localConfig
));

$result = $docEngine->callHook('afterRender', $result);

echo $result;