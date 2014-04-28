<?php
/**
 * Reference
 * =========
 * This module enriches the reference pages by indexing their
 * definitions and delivering quick navigations and stuff.
 *
 * @author: Christian Engel <hello@wearekiss.com> 
 * @version: 1 27.04.14
 */

class Reference {
    static $hooks = array(
        'contentParsed' => 'scan'
    );

    static $conf = array(
        'active' => TRUE
    );

    /**
     * @param $content
     */
    public static function scan($content){
        global $cosmo;

        if($cosmo->currentPage['type'] !== 'reference'){
            return $content;
        }

        $defTag = static::$conf['definitionTag'];
        $qnClasses = static::$conf['quickNavigation'];
        $groups = array();

        preg_match_all('#\<' . $defTag . ' class=\"(.+?)\"\>(.+?)</' . $defTag . '\>#s', $content, $matches);

        foreach($matches[0] as $k => $v){
            $classes = explode(' ', $matches[1][$k]);
            $definition = explode(':', $matches[2][$k]);

            $definitionName = $definition[0];
            $definitionType = NULL;
            $parameters = NULL;
            if(count($definition) > 1){
                $definitionType = array_pop($definition);
            }
            if(substr_count($definitionName, '(')){
                preg_match('#([^\(]+?)\((.*?)\)#', $definitionName, $parameters);
                if(count($parameters)){
                    $definitionName = $parameters[1];
                    $parameters = explode(',', $parameters[2]);
                }
            }
            $definitionLink = static::getDefinitionLink($definitionName, $classes[0]);
            $absoluteDefinitionLink = $cosmo->requestURL . '#' . $definitionLink;


            foreach($classes as $c){
                if(!isset($groups[$c])){
                    $groups[$c] = array();
                }

                $groups[$c][] = array(
                    'title' => $definitionName,
                    'link' => $absoluteDefinitionLink,
                    'classes' => implode(' ', $classes)
                );
            }

            $html = '<a id="' . $definitionLink . '" href="' . $absoluteDefinitionLink . '">' . $definitionName . '</a>';

            if($parameters){
                $html .= '(<span>' . implode(', ', $parameters) . '</span>)';
            }

            $content = str_replace($matches[2][$k], $html, $content);
        }

        $moduleSettings = $cosmo->mainConfig->modules->Reference->quickNavigation;
        $lang = $cosmo->readLanguage();
        $lang = $lang['modules']['Reference']['quickNavigation'];

        $out = array();

        foreach($lang as $key => $title){
            usort($groups[$key], function($a, $b){
                return $a['title'] > $b['title'] ? 1 : -1;
            });

            $out[] = array(
                'title' => $title,
                'class' => $key,
                'children' => $groups[$key]
            );
        }

        $twig = $cosmo->getTwigStringInstance();

        $template = $cosmo->themeFolder . '/templates/modules/Reference-quickNavigation.twig';
        $html = $twig->render(file_get_contents($template), array('data' => $out));


        $count = 0;
        $content = str_replace('<!--quicknavigation-->', $html, $content, $count);
        if(!$count){
            $content = preg_replace('#\<\/h1\>#', "</h1>\n\n" . $html . "\n\n", $content, 1, $count);
        }

        if(!$count){
            $content = $html . $content;
        }

        return $content;
    }

    private static function getDefinitionLink($definitionName, $class){
        return urlencode($class . '-' . $definitionName);
    }
}
 