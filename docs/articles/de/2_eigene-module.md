conf:{
    "key": "modules",
    "title": "Eigene Module"
}:conf

#Custom Modules
DocEngine wurde so gestaltet, dass sich eigene Module ohne große Mühe integrieren lassen. Willst du ein
Benutzersystem haben, bei dem sich die Leute einloggen können um Dinge auf deiner Seite zu erledigen?
Willst du schicke Diagramme rendern, oder willst du LaTEX interpretieren und rendern? Das alles ist nur
ein kleines Modul weit weg.

##Modulgrundlagen
Der Quellcode deines Modul muss im Ordner `lib/php/DocEngine/Modules/` gespeichert werden. Dies ist der Ordner,
welcher von DocEngine gescanned wird - alle enthaltenen PHP Dateien darin werden als Module geladen.

Dein Modulcode muss eine Klasse sein, die lediglich statische Methoden und Eigenschaften verwendet. Sie wird
niemals instanziert und nur statisch aufgerufen werden.

Deine Klasse muss mindestens die statischen Eigenschaften `$hooks` und `$conf` implementieren - alles andere
ist optional.

Momentan werden alle zusätzlichen Dateien von Modulen im Theme Ordner gespeichert. Wenn du Sub-Templates benötigst,
so wie die inlineDemo und quickNavigation Module, speicherst du diese unter `lib/theme/[themename]/templates/modules/`.
Das selbe gilt für JS- und CSS-Dateien, sowie Grafiken. Dies macht das Installieren und Entfernen von Modulen ein bischen
umständlich, allerdings konnte ich bisher noch keine bessere Möglichkeit finden, da die Ausgabe von Modulen je nach Theme
anders sein kann. Wenn du eine Idee hast, kontaktiere mich bitte.

##Ein Beispielmodul verstehen
Schau dir einmal eins der einfachsten Module für DocEngine an: das bereits enthaltene Module, welches Googles Code-Prettifier
 zur Dokumentation hinzufügt um deine Code-Beispiele (auch das jetzt folgende) schön darzustellen.

Wir werden es nach dem Code-Block detailliert analysieren:

    class PrettifySources {
        // Teilt docEngine mit, welche Methoden wann aufzurufen sind.
        static $hooks = array(
                'contentParsed' => 'prettifyAll'
        );

        // Voreingestellte Modul Konfiguration. Kann von der globalen Konfiguration,
        // oder lokalen Konfiguration überschrieben werden.
        static $conf = array(
                'active' => TRUE,
                'lang' => array(),
                'skin' => 'default',
                'linenums' => FALSE
        );

        /**
         * Nimmt den kompletten Quelltext der Seite und fügt
         * die Prettify Klasse zu pre tags hinzu.
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
            $result = str_replace('<pre>',
                                  '<pre class="prettyprint' . $conf . '">',
                                  $source,
                                  $count);

            //Wenn wir ein paar prettyprints platziert haben, müssen wir auch das JS laden.
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

Die Klasse wird in DocEngines' `init()` Methode geladen. DocEngine sucht nach der `$hooks` Eigenschaft und verknüpft jeden
Hook mit der jeweils angegebenen statischen Methode in der Klasse. In unserer Beispielklasse hier wird die `prettifyAll` Methode
mit dem Hook `contentParsed` verbunden.

Wenn der `contentParsed` hook aufgerufen wird, nachdem die Markdown-Datei der aktuellen Seite geladen und interpretiert wurde,
wird das HTML-Ergebnis an alle Methoden übergeben, welche mit dem `contentParsed` Hook verknüpft sind - darunter auch unsere Methode.

Also wird die `prettifyAll` Methode unserer Klasse aufgerufen und bekommt den HTML-Code übergeben, der danach in das Template des
aktuellen Themes eingefügt und an den Browser des Besuchers weitergeleitet wird. Wir wollen Googles Prettify Library einfügen, also
 benötigen wir zwei Dinge:

- Die CSS-Klasse `prettyprint` zu allen `pre` tags hinzufügen.
- Die Google Prettify Javascript Bibliothek in die Seite hinein laden.

Das Hinzufügen der `prettyprint` Klasse zu allen `pre` Tags ist einfach erledigt:

    $result = str_replace('<pre>',
                          '<pre class="prettyprint' . $conf . '">',
                          $source,
                          $count);

Wir tun sogar etwas mehr: Du kannst die Prettyprint Bibliothek anweisen, Zeilennummern anzuzeigen, indem du die
zusätzliche CSS-Klasse `linenumbers` ebenfalls zum `pre` Tag hinzufügst.
Wenn wir im Modul Config-Objekt nachschauen ob Zeilennummern erwünscht sind, können wir hier entscheiden
die Klasse `linenumbers` ebenfalls einzufügen, oder nicht. Mehr über die Modul Config findest du weiter unten.

Nachdem das Ergebnis modifiziert wurde, prüfen wir die `$count` Eigenschaft, ob irgendwelche Ersetzungen gemacht
wurden (eventuell gibt es in der aktuellen Seite ja garkeine Code-Blöcke). Wenn das der Fall ist, wird die Prettyprint
Javascript Bibliothek in die Seite geladen mit dem Aufruf `$docEngine->addJavascriptFile()`.

Das modifizierte Ergebnis wird am Ende der Methode zurückgegeben, damit DocEngine das Rendern der Seite abschließen und
diese dem User anzeigen kann. Das war jetzt nicht kompliziert, oder?

##Modulkonfiguration


You most certainly already noticed the `$conf` property of the module. This is where you can set some
options to modify the modules behaviour. You can pre-define some default values from inside the module
code (like you can see above), but the properties here will be overwritten if other values have been
defined either in the global config, or local config.

If you want to modify the default skin to be used by the prettifier, you need to set the property
 `modules.PrettifySources.skin` inside the global config file `docs/docEngine_config.json`.

Each module can get a configuration assigned if you define a object with the modules' name inside the
`modules` object inside the global config.

The same behaviour can be achieved if you define a `modules` object inside the markdown files' config
block and create a sub-object with the modules name.

This is useful if you want to disable some modules for certain pages completely (setting active to false),
or want to use different skins, or whatever.


##Module exclusive page routing
If you need to output some module-data and nothing else, you can utilize the following route:

    /module/myCoolHook/what/ever/you/want

The `/module` route is reserved for direct-to-module calls inside docEngine, so calling this route will
trigger a hook and no theme template is being rendered around any data you echo to the browser.

In the example above, docEngine will call the hook `module:myCoolHook` with the remaining URL parameters
passed into the hook as an array. In our case `["what", "ever", "you", "want"]`.
If you register a method of your module to it, everything that is being returned from the method
is ignored, so you have to echo your data directly.

After the hook is processed, docEngine stops the execution flow and will output nothing else. This makes
the construct perfect for performing AJAX calls, or render completely independend pages out of a module.

The inlineDemo module uses this construction to save data for editable demos.