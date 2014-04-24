<?php

/**
 * InlineDemos
 * ===========
 * This module looks for demo:{}:demo JSON blocks and replaces them with inline demo elements.
 *
 * @author: Christian Engel <hello@wearekiss.com>
 * @version: 1 22.04.14
 */
class InlineDemos {

    /**
     * Tells cosmo which methods to call when.
     * @var array
     */
    static $hooks = array(
            'contentUnparsed' => 'embedDemos',
            'module:demo' => 'inlineDemo'
    );

    static $conf = array(
        'active' => TRUE
    );

    /**
     * Adds the prettify library to be loaded in the page header.
     */
    public static function embedDemos($content) {
        if (!static::$conf['active']) {
            return $content;
        }

        global $cosmo;

        while($demoTag = $cosmo->readJSONBlock($content, 'demo', TRUE, TRUE)){
            $json = $demoTag['json'];

            session_start();
            $_SESSION['cosmo_demo_' . $json['target']] = $json;

            $html = '<iframe class="inlineDemo" src="module/demo/' . $json['target'] . '"></iframe>';

            $content = substr_replace($content, $html, $demoTag['start'], $demoTag['end'] - $demoTag['start']);
        }

        return $content;
    }


    public static function inlineDemo($urlParams){
        session_start();

        $demoName = implode('/', $urlParams);

        if(!isset($_SESSION['cosmo_demo_' . $demoName . '/'])){
            die('Undefined demo');
        }

        $demoConfig = $_SESSION['cosmo_demo_' . $demoName . '/'];

        $fileData = '';

        foreach($demoConfig['display'] as $file){
            if(!file_exists('docs/demos/' . $demoName . '/' . $file)){
                die('Cannot read file to display: ' . $file);
            }
            $fileContent = file_get_contents('docs/demos/' . $demoName . '/' . $file);
            $fileN = explode('.', $file);
            $fileN = implode('_', $fileN);
            $fileData .= '<script type="text/html" id="file_' . $fileN . '">' . $fileContent . '</script>';
        }

        global $cosmo;

        require 'lib/php/Kiss/Utils.php';

        $dta = array(
            'editable' => isset($demoConfig['editable']) ? ($demoConfig['editable'] ? 'true' : 'false') : 'false',
            'target' => $cosmo->mainConfig->basePath . 'docs/demos/' . $demoName . '/',
            'basePath' => $cosmo->mainConfig->basePath,
            'themeFolder' => $cosmo->themeFolder,
            'files' => json_encode($demoConfig['display']),
            'fileData' => $fileData
        );

        die(\Kiss\Utils::template('@file::' . $cosmo->themeFolder . '/templates/modules/inlineDemo.twig', $dta));
    }
}
 