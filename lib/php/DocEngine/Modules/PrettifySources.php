<?php

/**
 * PrettifySources
 * ===============
 * This module enables prettyprinting of code blocks using the google prettyprint project.
 *
 * @author: Christian Engel <hello@wearekiss.com>
 * @version: 1 22.04.14
 */
class PrettifySources {

    /**
     * Tells docEngine which methods to call when.
     * @var array
     */
    static $hooks = array(
            'contentParsed' => 'prettifyAll'
    );

    /**
     * Initial module config. Can be overridden by main config and local config.
     * @var array
     */
    static $conf = array(
            'active' => TRUE,
            'lang' => array(),
            'skin' => 'default',
            'linenums' => FALSE
    );

    /**
     * Takes the whole page source and adds the prettify
     * @param $source
     * @return mixed
     */
    public static function prettifyAll($source) {
        if (!static::$conf['active']) {
            return $source;
        }

        $conf = '';

        if (static::$conf['linenums']) {
            $conf = ' linenums';
        }

        $count = 0;
        $result = str_replace('<pre>', '<pre class="prettyprint' . $conf . '">', $source, $count);

        //When we have placed some prettyprints, we have to load the JS as well.
        if ($count > 0) {
            global $docEngine;

            $url = 'https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?skin=' . static::$conf['skin'];

            if (count(static::$conf['lang'])) {
                $url .= '&lang=' . implode('&lang=', static::$conf['lang']);
            }

            $docEngine->addJavascriptFile($url, TRUE);
        }

        return $result;
    }
}
 