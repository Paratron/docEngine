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
            'contentUnparsed' => 'embedDemos'
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

        while($demoTag = $cosmo->readJSONBlock($content, 'demo', FALSE, TRUE)){
            $content = substr_replace($content, 'DEMO!', $demoTag['start'], $demoTag['end'] - $demoTag['start']);
        }

        return $content;
    }
}
 