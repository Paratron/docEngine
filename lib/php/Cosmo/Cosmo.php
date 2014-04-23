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
     * @var Array Contains an array of all available pages.
     */
    private $fileStructure = array();

    /**
     * @var Array Contains quick links if you search for language variations by key.
     */
    private $keyFileStructureReference = array();

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

    /**
     * Cache for global language strings fetched by readLanguage();
     * @var null
     */
    var $globalLanguage = NULL;

    /**
     * This array stores all defined hook entry points.
     * @var array
     */
    private $hooks = array();

    /**
     * Names of all loaded modules.
     * @var array
     */
    private $moduleNames = array();

    /**
     * @var array List of URLs of javascript files to be loaded in the page header.
     */
    var $headerJavaScriptFiles = array();
    /**
     * @var array List of URLs of javascript files to be loaded in the page footer (before closing body tag).
     */
    var $footerJavaScriptFiles = array();
    /**
     * @var array List of URLs of css files to be loaded in the page header.
     */
    var $cssFiles = array();

    function __construct() {
        //First, lets read the cosmo config.
        $this->mainConfig = json_decode(file_get_contents('docs/cosmo_config.json'));

        if (!$this->mainConfig) {
            throw new \ErrorException('Cosmo Main Config file damaged!');
        }

        $this->readFileStructure();

        $this->loadModules();
    }

    function init() {
        $this->callHook('modulesLoaded');

        $this->themeFolder = 'lib/theme/' . $this->mainConfig->theme;

        if (isset($_SERVER['PATH_INFO'])) {
            $this->requestParams = explode('/', $_SERVER['PATH_INFO']);
            array_shift($this->requestParams);
        }
        else {
            $this->requestParams = array();
        }

        $this->requestURL = implode('/', $this->requestParams);

        /*
         * A URL request always consists from:
         * /(page|article|reference)/(language)/(id)
         */
        $pCount = count($this->requestParams);

        $page = NULL;

        //Language parameter missing?
        if ($pCount > 0 && $pCount < 3) {
            //One or no Param - assumes language to be not given
            $this->language = $this->mainConfig->defaultLanguage;
            $last = $this->requestParams[$pCount - 1];

            if (!($page = $this->getPageByURL(implode('/', array(
                    $this->requestParams[0],
                    $this->language,
                    $last
            ))))
            ) {
                header('location: ' . $this->mainConfig->basePath . $this->mainConfig->homepage);
                die();
            }

            $lang = $page['lang'];
            header('location: ' . $this->mainConfig->basePath . $this->requestParams[0] . '/' . $lang . '/' . $last);
            die();
        }
        else {
            if ($pCount > 0) {
                $this->language = strtolower(substr($this->requestParams[1], 0, 2));
                $page = $this->getPageByURL($this->requestURL);
            }
        }

        $page = $this->callHook('routingFinished', $page);

        if (!$page) {
            header('location: ' . $this->mainConfig->basePath . $this->mainConfig->homepage);
            die();
        }

        $this->contentPath = $page['filePath'];
        $this->localConfig = $page['config'];

        //Local config can override main config settings for modules.
        foreach ($this->moduleNames as $moduleName) {
            if (isset($page['config']['modules']) && isset($page['config']['modules'][$moduleName])) {
                foreach ($page['config']['modules'][$moduleName] as $k => $v) {
                    /** @var $conf ,be quiet phpstorm */
                    $moduleName::$conf[$k] = $v;
                }
            }
        }

        if ($page['type'] === 'page') {
            $dynFileName = preg_replace('#\.md$#', '.php', $page['filePath']);
            if (file_exists($dynFileName)) {
                $vars = array();
                $this->sandbox($dynFileName, $vars);
                $loader = new \Twig_Loader_String();
                $twig = new \Twig_Environment($loader, array(
                        'cache' => $this->mainConfig->cache ? '/lib/cache' : NULL,
                ));

                $content = $twig->render(file_get_contents($this->contentPath), array('vars' => $vars));
                $this->content = $this->parse($content);
                return;
            }
        }

        $this->content = $this->parse(file_get_contents($this->contentPath));
    }

    private function sandbox($dynFileName, &$vars) {
        include $dynFileName;
    }

    private function getPageByURL($url) {
        foreach ($this->fileStructure as $f) {
            if ($f['url'] == $url) {
                return $f;
            }
        }

        return NULL;
    }

    /**
     * Returns the contents of the current main language file.
     */
    function readLanguage() {
        if ($this->globalLanguage === NULL) {
            $this->globalLanguage = json_decode(file_get_contents('lib/language/' . $this->language . '.json'), TRUE);
        }

        return $this->globalLanguage;
    }

    /**
     * Tries to parse the given content.
     * @param $content
     * @return array
     */
    private function parse($content) {
        require_once 'lib/php/Michelf/Markdown.inc.php';

        $result = $this->readJSONBlock($content, 'conf');
        $this->stripJSONBlock($content, $result);

        $content = $this->callHook('contentUnparsed', $content);

        $content = \Michelf\Markdown::defaultTransform($content);

        $content = $this->callHook('contentParsed', $content);

        return $content;
    }

    /**
     * Reads a given JSON block from the given content string.
     * @param $content
     * @param $blockTag
     * @param bool $assoc
     * @param bool $noindent Ignore indented blocks
     * @throws \ErrorException
     * @return array
     */
    function readJSONBlock($content, $blockTag, $assoc = FALSE, $noindent = FALSE) {
        $start = strpos($content, $blockTag . ':{');

        if($start === FALSE){
            return FALSE;
        }

        if($noindent && $start > 0){
            $before = substr($content, $start - 1, 1);
            if($before == ' ' || $before == "\t")
            return FALSE;
        }

        $end = strpos($content, '}:' . $blockTag, $start);
        $content = substr($content, $start + strlen($blockTag) + 1, $end - ($start + strlen($blockTag)));
        $json = json_decode($content, $assoc);

        if($json === NULL){
            throw new \ErrorException('JSON block parsing error. Block tag: ' . $blockTag . ' Content: ' . $content);
        }

        $result = array(
                'tag' => $blockTag,
                'start' => $start,
                'end' => $end + strlen($blockTag) + 2,
                'json' => $json
        );

        return $result;
    }

    function loadModules() {
        $moduleFiles = glob('lib/php/Cosmo/Modules/*.php');

        foreach ($moduleFiles as $m) {
            require $m;

            $moduleName = explode('.', basename($m));
            $moduleName = array_shift($moduleName);
            $moduleVars = get_class_vars($moduleName);

            $this->moduleNames[] = $moduleName;

            if (isset($this->mainConfig->modules->$moduleName)) {
                foreach ($this->mainConfig->modules->$moduleName as $k => $v) {
                    /** @var $conf ,be quiet phpstorm */
                    $moduleName::$conf[$k] = $v;
                }
            }

            foreach ($moduleVars['hooks'] as $hookName => $funcName) {
                if (!isset($this->hooks[$hookName])) {
                    $this->hooks[$hookName] = array();
                }

                $this->hooks[$hookName][] = array(
                        $moduleName,
                        $funcName
                );
            }
        }
    }

    function readFileStructure() {
        $files = glob('docs/{articles,pages,reference}/*/*.md', GLOB_BRACE);

        $struct = array();
        $keyStructRef = array();

        foreach ($files as $f) {
            $structObj = array();

            $fConfig = $this->readConfigFromFile($f);
            $structObj['config'] = $fConfig['json'];

            $structObj['filePath'] = $f;
            $f = explode('/', $f);
            $f[3] = explode('.', $f[3]);
            $f[3] = explode('_', $f[3][0]);
            if (is_numeric($f[3][0])) {
                $structObj['sortOrder'] = $f[3][0];
            }
            else {
                $structObj['sortOrder'] = -1;
            }
            $f[3] = array_pop($f[3]);

            if ($f[1] !== 'reference') {
                $f[1] = substr($f[1], 0, -1);
            }

            $structObj['type'] = $f[1];

            if (isset($structObj['config']['key'])) {
                $key = $structObj['config']['key'];
                if (!isset($keyStructRef[$f[1] . '_' . $key])) {
                    $keyStructRef[$f[1] . '_' . $key] = array();
                }
                $structObj['key'] = $key;
                $keyStructRef[$f[1] . '_' . $key][] = count($struct);
            }
            else {
                $structObj['key'] = NULL;
            }
            $structObj['title'] = $structObj['config']['title'];
            $structObj['lang'] = $f[2];
            $structObj['url'] = $f[1] . '/' . $f[2] . '/' . $f[3];
            $struct[] = $structObj;
        }

        $this->fileStructure = $struct;
        $this->keyFileStructureReference = $keyStructRef;
    }

    private function readConfigFromFile($fileName) {
        if (!file_exists($fileName)) {
            throw new \ErrorException('File not found');
        }

        $f = fopen($fileName, 'r');
        $inConfigBlock = FALSE;
        $buffer = '';


        while ($line = fgets($f)) {
            if (strpos($line, '}:conf') !== FALSE) {
                $buffer .= $line;
                break;
            }

            if (substr($line, 0, 6) === 'conf:{') {
                $inConfigBlock = TRUE;
                $buffer .= $line;
                continue;
            }

            if ($inConfigBlock) {
                $buffer .= $line;
            }
        }

        fclose($f);

        return $this->readJSONBlock($buffer, 'conf', TRUE);
    }

    private function readConfigValueFromFile($fileName, $valueNames) {
        if (!is_array($valueNames)) {
            $valueNames = array($valueNames);
        }

        $out = array();

        $config = $this->readConfigFromFile($fileName);

        foreach ($valueNames as $v) {
            if (isset($config[$v])) {
                $out[$v] = $config[$v];
            }
            else {
                $out[$v] = NULL;
            }
        }

        return $out;
    }

    private function quickReadConfigValue($content, $valueName) {
        $start = strpos($content, '"' . $valueName . '"');

        $stringStart = 0;
        for ($i = $start + strlen($valueName) + 2; $i < 100; $i++) {
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
            throw new \ErrorException('Config Value not found');
        }

        return substr($content, $stringStart + 1, $i - $stringStart - 1);
    }

    private function stripJSONBlock(&$content, $result, $replace = '') {
        $content = substr_replace($content, $replace, $result['start'], $result['end'] - $result['start']);
    }

    function getPageList($type) {
        $out = array();

        foreach ($this->fileStructure as $f) {
            if ($f['type'] === $type && $f['lang'] === $this->language) {
                $out[] = array(
                        'url' => $f['url'],
                        'title' => $f['config']['title']
                );
            }
        }

        return $out;
    }

    /**
     * Will return a list of all page elements.
     */
    function getPages() {
        return $this->getPageList('page');
    }

    function getArticles() {
        return $this->getPageList('article');
    }

    function getReferences() {
        return $this->getPageList('reference');
    }

    function renderLanguageWidget() {
        $this->readLanguage();

        $wgt = array(
                'currentLanguage' => array(
                        'isoCode' => $this->language,
                        'name' => $this->globalLanguage['languages'][$this->language],
                        'url' => $this->requestURL
                ),
                'availableLanguages' => array()
        );

        if (isset($this->localConfig['key'])) {
            $refKey = $this->requestParams[0] . '_' . $this->localConfig['key'];
            if (isset($this->keyFileStructureReference[$refKey])) {
                foreach ($this->keyFileStructureReference[$refKey] as $refIndex) {
                    $f = $this->fileStructure[$refIndex];
                    $wgt['availableLanguages'][] = array(
                            'url' => $f['url'],
                            'isoCode' => $f['lang'],
                            'name' => $this->globalLanguage['languages'][$f['lang']],
                            'pageTitle' => $f['title'],
                            'active' => $this->language === $f['lang']
                    );
                }
            }
        }


        global $twig;
        return $twig->render('wgt-language.twig', array(
                'wgt' => $wgt,
                'lang' => $this->readLanguage()
        ));
    }

    /**
     * Iterates over all registered hooks of a specific name and calls them.
     * Their results are collected in an array and returned in the end.
     * @param {String} $hookName
     * @param {Array} [$data]
     * @return array
     */
    function callHook($hookName, $data = NULL) {
        if (!isset($this->hooks[$hookName])) {
            return $data;
        }

        foreach ($this->hooks[$hookName] as $hook) {
            $data = call_user_func($hook, $data);
        }

        return $data;
    }

    /**
     * Adds a CSS file to be loaded by the theme.
     * @param $url
     * @param string $media
     */
    function addCSSFile($url, $media = 'screen') {
        $cssFile = array(
                'local' => !(substr($url, 0, 5) == 'http:' || substr($url, 0, 6) == 'https:'),
                'url' => $url,
                'media' => $media
        );

        $this->cssFiles[] = $cssFile;
    }

    /**
     * Adds a JavaScript file to be loaded by the theme.
     * @param $url
     * @param bool $header
     */
    function addJavascriptFile($url, $header = FALSE) {
        $jsFile = array(
                'local' => !(substr($url, 0, 5) == 'http:' || substr($url, 0, 6) == 'https:'),
                'url' => $url
        );

        if ($header) {
            $this->headerJavaScriptFiles[] = $jsFile;
            return;
        }
        $this->footerJavaScriptFiles[] = $jsFile;
    }
}
 