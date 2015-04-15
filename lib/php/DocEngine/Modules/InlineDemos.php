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
	 * Tells docEngine which methods to call when.
	 * @var array
	 */
	static $hooks = array(
			'contentUnparsed' => 'embedDemos',
			'module:demo' => 'inlineDemo',
			'module:demofiles' => 'fileServer',
			'module:demopulse' => 'pulse'
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

		global $docEngine;
		if (!$docEngine->staticBuildInProgress) {
			session_start();

			if (!is_dir('lib/cache/sandbox')) {
				mkdir('lib/cache/sandbox');
				mkdir('lib/cache/sandbox/open');
			}
			else {
				$openSessions = scandir('lib/cache/sandbox/open');
				array_shift($openSessions);
				array_shift($openSessions);
				foreach ($openSessions as $s) {
					if (filemtime('lib/cache/sandbox/open/' . $s) < time() - 60) {
						$sessFiles = glob('lib/cache/sandbox/' . $s . '_*');
						foreach ($sessFiles as $f) {
							unlink($f);
						}
						unlink('lib/cache/sandbox/open/' . $s);
					}
				}
			}
		}


		while ($demoTag = $docEngine->readJSONBlock($content, 'demo', TRUE, TRUE)) {
			$json = $demoTag['json'];
			$sandboxId = uniqid('');

			$json['languageTransfer'] = $docEngine->language;

			if (!$docEngine->staticBuildInProgress) {
				$_SESSION[$sandboxId] = $json;

				touch('lib/cache/sandbox/open/' . $sandboxId);

				$html = '<iframe class="inlineDemo" src="../module/demo/' . $sandboxId . '/' . $json['target'] . '"></iframe>';
			} else {
				$html = '<iframe id="demo' . $sandboxId . '" class="inlineDemo" src="../../demos/demoviewer.html"></iframe>';
				$html .= '<script>document.getElementById("demo' . $sandboxId . '").contentWindow.postMessage(\'' . json_encode($json) . '\');</script>';
			}

			$content = substr_replace($content, $html, $demoTag['start'], $demoTag['end'] - $demoTag['start']);
		}

		return $content;
	}


	public static function inlineDemo($urlParams) {
		session_start();

		$sandboxId = array_shift($urlParams);
		$demoName = implode('/', $urlParams);

		if (!isset($_SESSION[$sandboxId])) {
			die('Undefined demo');
		}

		$demoConfig = $_SESSION[$sandboxId];

		if (!isset($demoConfig['display'])) {
			$demoConfig['display'] = array();
		}

		if (!isset($demoConfig['links'])) {
			$demoConfig['links'] = array();
		}

		if (!isset($demoConfig['notice'])) {
			$demoConfig['notice'] = '';
		}

		$fileData = '';

		foreach ($demoConfig['display'] as $file) {
			if (!file_exists('docs/demos/' . $demoName . '/' . $file)) {
				die('Cannot read file to display: ' . $file);
			}
			$fileContent = file_get_contents('docs/demos/' . $demoName . '/' . $file);
			$fileN = str_replace(array(
					'.',
					'/'
			), '_', $file);
			$fileData .= '<script type="text/html" id="file_' . $fileN . '">' . str_replace(array(
							'<',
							'>'
					), array(
							'%-gts-%',
							'%-lts-%'
					), $fileContent) . '</script>';
		}

		global $docEngine;

		require 'lib/php/Kiss/Utils.php';

		$docEngine->language = $demoConfig['languageTransfer'];
		$lang = $docEngine->readLanguage();

		$dta = array(
				'sandboxId' => $sandboxId,
				'editable' => isset($demoConfig['editable']) ? ($demoConfig['editable'] ? 'true' : 'false') : 'false',
				'target' => $docEngine->mainConfig->basePath . $docEngine->language . '/module/demofiles/' . $sandboxId . '/',
				'basePath' => $docEngine->mainConfig->basePath,
				'themeFolder' => $docEngine->themeFolder,
				'files' => json_encode($demoConfig['display']),
				'links' => json_encode($demoConfig['links']),
				'notice' => $demoConfig['notice'],
				'fileData' => $fileData,
				'lang' => json_encode($lang['modules']['inlineDemo'])
		);

		die(\Kiss\Utils::template('@file::' . $docEngine->themeFolder . '/templates/modules/inlineDemo.twig', $dta));
	}

	public static function fileServer($urlParams) {
		session_start();

		$sandboxId = array_shift($urlParams);
		$filePath = implode('/', $urlParams);

		if (!$filePath) {
			$filePath = 'index.html';
		}

		if (!isset($_SESSION[$sandboxId])) {
			die('Unknown sandbox');
		}

		$demoConfig = $_SESSION[$sandboxId];

		$fileName = 'docs/demos/' . $demoConfig['target'] . $filePath;
		$fileId = $sandboxId . '_' . md5($fileName);

		if (!file_exists($fileName)) {
			die('File not found :(');
		}

		//User setting data!
		if (isset($demoConfig['editable']) && $demoConfig['editable'] && isset($_POST['data'])) {
			file_put_contents('lib/cache/sandbox/' . $fileId, $_POST['data']);
			die('1');
		}

		require 'lib/php/Kiss/Utils.php';

		header('Content-Type: ' . \Kiss\Utils::getMimeType($filePath));
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');

		if (file_exists('lib/cache/sandbox/' . $fileId)) {
			header('Content-Length: ' . filesize('lib/cache/sandbox/' . $fileId));
			readfile('lib/cache/sandbox/' . $fileId);
			die();
		}

		header('Content-Length: ' . filesize($fileName));
		readfile($fileName);
		die();
	}

	public static function pulse($urlParams) {
		session_start();

		$sandboxId = array_shift($urlParams);

		if (isset($_SESSION[$sandboxId])) {
			touch('lib/cache/sandbox/open/' . $sandboxId);
			die('1');
		}

		die('0');
	}
}
 