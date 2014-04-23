<?php

require('lib/php/Twig/Autoloader.php');

Twig_Autoloader::register();

require 'lib/php/Cosmo/Cosmo.php';

$cosmo = new \Cosmo\Cosmo();

$cosmo->init();

$loader = new Twig_Loader_Filesystem($cosmo->themeFolder . '/templates');
$twig = new Twig_Environment($loader, array(
        'cache' => $cosmo->mainConfig->cache ? '/lib/cache' : NULL,
));

$cosmo->callHook('beforeRender');

$result = $twig->render('base.twig', array(
        'cosmo' => $cosmo,
        'lang' => $cosmo->readLanguage(),
        'config' => $cosmo->localConfig
));

$result = $cosmo->callHook('afterRender', $result);

echo $result;