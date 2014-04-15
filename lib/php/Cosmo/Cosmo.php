<?php
/**
 * Cosmo
 * ==========
 * This is the main cosmo class.
 *
 * @author: Christian Engel <hello@wearekiss.com>
 * @version: 1 09.04.14
 */

namespace Cosmo;

class Cosmo {
    /**
     * @var {Object} $mainConfig Contains the cosmo config data.
     */
    var $mainConfig;

    /**
     * @var array Config object of the current page.
     */
    var $localConfig = array();

    /**
     * @var {String} $themeFolder Contains the path to the local theme folder.
     */
    var $themeFolder;

    /**
     * @var {Array} $requestParams Contains the request URI parameters, split into an array.
     */
    var $requestParams;

    /**
     * @var {String} The current request url.
     */
    var $requestURL;

    /**
     * @var string Path of the current content file.
     */
    private $contentPath = '';

    /**
     * @var string The HTML content of the current page.
     */
    var $content = '';

    function __construct() {
        //First, lets read the cosmo config.
        $this->mainConfig = json_decode(file_get_contents('docs/cosmo_config.json'));

        $this->themeFolder = 'lib/theme/' . $this->mainConfig->theme;
        if(isset($_SERVER['PATH_INFO'])){
            $this->requestParams = explode('/', $_SERVER['PATH_INFO']);
            array_shift($this->requestParams);
        } else {
            $this->requestParams = array();
        }

        $this->requestURL = implode('/', $this->requestParams);

        /*
         * A URL request always consists from:
         * /(page|article|reference)/(language)/(id)
         */
        $pCount = count($this->requestParams);

        //Language parameter missing?
        if ($pCount > 0 && $pCount < 3) {
            //One or no Param - assumes language to be not given
            $this->language = $this->mainConfig->defaultLanguage;
            $last = array_pop($this->requestParams);
            $path = glob('docs/{articles,pages,reference}/*/*' . $last . '.md', GLOB_BRACE);

            if (!count($path)) {
                header('location: ' . $this->mainConfig->basePath . $this->mainConfig->homepage);
                die();
            }

            $lang = explode('/', $path[0]);
            $lang = $lang[2];
            header('location: ' . $this->mainConfig->basePath . $this->requestParams[0] . '/' . $lang . '/' . $last);
            die();
        }
        else if($pCount > 0){
            $this->language = strtolower(substr($this->requestParams[1], 0, 2));
            $path = glob('docs/{articles,pages,reference}/*/*' . $this->requestParams[2] . '.md', GLOB_BRACE);
        }


        if (!count($path)) {
            header('location: ' . $this->mainConfig->basePath . $this->mainConfig->homepage);
            die();
        }

        $this->contentPath = $path[0];
        $this->content = $this->parse(file_get_contents($path[0]));
    }

    /**
     * Returns the contents of the current main language file.
     */
    function readLanguage() {
        return json_decode(file_get_contents('lib/language/' . $this->language . '.json'), TRUE);
    }

    /**
     * Tries to parse the given content.
     * @param $content
     */
    private function parse($content) {
        require_once 'lib/php/Michelf/Markdown.inc.php';

        $result = $this->readJSONBlock($content, 'conf');
        $this->localConfig = $result['json'];
        $this->stripJSONBlock($content, $result);

        $content = \Michelf\Markdown::defaultTransform($content);

        return $content;
    }

    private function readJSONBlock($content, $blockTag) {
        $start = strpos($content, $blockTag . ':{');
        $end = strpos($content, '}:' . $blockTag, $start);
        $content = substr($content, $start + strlen($blockTag) + 1, $end - ($start + strlen($blockTag)));

        $result = array(
                'tag' => $blockTag,
                'start' => $start,
                'end' => $end,
                'json' => json_decode($content)
        );

        return $result;
    }

    /**
     * Reads the title attribute from the config block.
     * @param $content
     * @return string
     */
    private function readTitle($content) {
        $start = strpos($content, '"title"');

        $stringStart = 0;
        for ($i = $start + 7; $i < 100; $i++) {
            if (substr($content, $i, 1) === '"' && substr($content, $i - 1, 1) !== '\\') {
                if (!$stringStart) {
                    $stringStart = $i;
                }
                else {
                    return substr($content, $stringStart + 1, $i - $stringStart - 1);
                }
            }
        }

        if (!$stringStart) {
            throw new \ErrorException('No title definition found');
        }

        return substr($content, $stringStart + 1, $i - $stringStart - 1);
    }

    private function stripJSONBlock(&$content, $result, $replace = '') {
        $content = substr_replace($content, $replace, $result['start'], $result['end'] - $result['start'] + strlen($result['tag']) + 2);
    }

    function getPageList($type) {
        switch($type){
            case 'page':
                $p1 = 'pages';
                $p2 = 'page';
                break;
            case 'article':
                $p1 = 'articles';
                $p2 = 'article';
                break;
            case 'reference':
                $p1 = $p2 = 'reference';
        }

        $pages = glob('docs/' . $p1 . '/' . $this->language . '/*.md');
        $out = array();

        foreach ($pages as $v) {
            $key = explode('/', $v);
            $key = explode('.md', array_pop($key));
            $key = explode('_', $key[0]);
            if (is_numeric($key[0])) {
                array_shift($key);
            }
            $key = implode('_', $key);

            $out[] = array(
                    'url' => $p2 . '/' . $this->language . '/' . $key,
                    'title' => $this->readTitle(file_get_contents($v))
            );
        }

        return $out;
    }

    /**
     * Will return a list of all page elements.
     */
    function getPages() {
        return $this->getPageList('page');
    }

    function getArticles(){
        return $this->getPageList('article');
    }

    function getReferences(){
        return $this->getPageList('reference');
    }
}
 