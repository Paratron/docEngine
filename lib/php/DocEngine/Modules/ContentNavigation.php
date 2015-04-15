<?php
/**
 * ContentNavigation
 * =================
 * This module scans the page for headlines and generates a 
 * sub navigation from them.
 *
 * @author: Christian Engel <hello@wearekiss.com> 
 * @version: 1 15.04.15
 */

class ContentNavigation {
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
        global $docEngine;

        if($docEngine->currentPage['type'] === 'reference'){
            return $content;
        }

        preg_match_all('#<h([2-6])(.*?)>(.+?)</h[2-6]>#s', $content, $matches);

		$subNavigation = array();

		foreach($matches[0] as $k => $v){
			$result = preg_match('/id="(.+?)"/', $matches[2][$k], $itemId);
			if($result){
				$itemId = $itemId[1];
			} else {
				$itemId = strtolower(str_replace(' ', '-', $matches[3][$k]));
				$content = str_replace($matches[0][$k], '<h' . $matches[1][$k] . ' id="' . $itemId . '" ' . $matches[2][$k] . '>' . $matches[3][$k] . '</h' . $matches[1][$k] . '>', $content);
			}

			//Storing the id, title and level of the navigation item
			$subNavigation[] = array(
					'anchor' => $itemId,
					'title' => $matches[3][$k],
					'level' => $matches[1][$k]
			);
		}

		$twig = $docEngine->getTwigStringInstance();

		$template = $docEngine->themeFolder . '/templates/modules/contentNavigation.twig';
		$html = $twig->render(file_get_contents($template), array('items' => $subNavigation));

		$count = 0;
		$content = str_replace('<!--contentnavigation-->', $html, $content, $count);
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
