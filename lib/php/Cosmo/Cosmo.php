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

    function __construct() {
        //First, lets read the cosmo config.
        $this->mainConfig = json_decode(file_get_contents('docs/cosmo_config.json'));

        $this->readFileStructure();

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

        //Language parameter missing?
        if ($pCount > 0 && $pCount < 3) {
            //One or no Param - assumes language to be not given
            $this->language = $this->mainConfig->defaultLanguage;
            $last = $this->requestParams[$pCount - 1];

            $page = NULL;

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


        if (!$page) {
            header('location: ' . $this->mainConfig->basePath . $this->mainConfig->homepage);
            die();
        }

        $this->contentPath = $page['filePath'];
        $this->localConfig = $page['config'];

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
     */
    private function parse($content) {
        require_once 'lib/php/Michelf/Markdown.inc.php';

        $result = $this->readJSONBlock($content, 'conf');
        $this->stripJSONBlock($content, $result);

        $content = \Michelf\Markdown::defaultTransform($content);

        return $content;
    }

    private function readJSONBlock($content, $blockTag, $assoc = FALSE) {
        $start = strpos($content, $blockTag . ':{');
        $end = strpos($content, '}:' . $blockTag, $start);
        $content = substr($content, $start + strlen($blockTag) - 1, $end - ($start + strlen($blockTag) + 1));

        $result = array(
                'tag' => $blockTag,
                'start' => $start,
                'end' => $end,
                'json' => json_decode($content, $assoc)
        );

        return $result;
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

            if (strpos($line, 'conf:{') !== FALSE) {
                $inConfigBlock = TRUE;
                $buffer .= $line;
                continue;
            }

            if ($inConfigBlock) {
                $buffer .= $line;
            }
        }

        fclose($f);

        return $this->readJSONBlock($buffer, 'config', TRUE);
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
        $content = substr_replace($content, $replace, $result['start'], $result['end'] - $result['start'] + strlen($result['tag']) + 2);
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
}
 