<?php

require('lib/php/Twig/Autoloader.php');

Twig_Autoloader::register();

require 'lib/php/Cosmo/Cosmo.php';

$cosmo = new \Cosmo\Cosmo();

$loader = new Twig_Loader_Filesystem($cosmo->themeFolder . '/templates');
$twig = new Twig_Environment($loader, array(
        'cache' => $cosmo->mainConfig->cache ? '/lib/cache' : NULL,
));

echo $twig->render('base.twig', array(
        'cosmo' => $cosmo,
        'lang' => $cosmo->readLanguage(),
        'config' => $cosmo->localConfig
));