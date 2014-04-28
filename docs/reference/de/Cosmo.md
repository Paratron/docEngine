conf:{
    "root": "Cosmo",
    "title": "Cosmo",
    "constructor": false,
    "file": "lib/php/Cosmo/Cosmo.php",
    "key": "ref:cosmo"
}:conf

#Cosmo
Die Cosmo Klasse ist von jedem Seitentemplate, wie auch aus jedem Modul oder anderer Art Code aufrufbar.
Wenn du das Cosmo Objekt von innerhalb eines Templates verwenden willst, nutze einfach das `cosmo` Objekt innerhalb deines
Twig Templates. Wenn du von jeglichem PHP Code (Modul oder anderes) auf das Cosmo Objekt zugreifen willst, rufe einfach
`global $cosmo` auf, damit es in deiner Klasse oder Funktion verfügbar wird.

##Objekteigenschaften

###mainConfig:array {.property}
Enthält die globalen Einstellungen. Wird in der Datei `docs/cosmo_config.json` definiert.

###localConfig:array {.property}
Enthält die Einstellungen der aktuellen Seite. Wird innerhalb eines `conf:{}:conf` JSON Blocks am Anfang jeder
Markdown Datei definiert. Eigenschaften der lokalen Einstellungen sollten vorrangig vor den globalen Einstellungen stehen.

###themeFolder:string {.property}
Pfad zum momentan verwendeten Theme Ordner. Kann in den globalen Einstellungen unter der Eigenschaft `theme` definiert werden.

###requestParams:array {.property}
Enthält die URI Parameter des aktuellen Aufrufs, aufgeteilt in ein Array.

###requestURL:string {.property}
Die URL des aktuellen Aufrufs, abzüglich des Base-Pfads. Also zum Beispiel `article/de/beispiel`.

###currentPage:array {.property}
Enthält das momentane Seitenobjekt, welches von der Routing Funktion ermittelt wurde.
Beispiel:

    {
        config: {},
        filePath: "article/de/2_beispiel.md",
        sortOrder: 2,
        type: "article",
        key: "übersetzungsSchlüssel",
        lang: "de",
        url: "article/de/beispiel"
    }


###contentPath:string {.property}
Der Dateipfad der momentan verwendeten Inhaltsdatei. Zum Beispiel: `docs/articles/de/beispiel.md`.

###content:string {.property}
Das HTML Ergebnis des verarbeiteten Markdown-Codes.

###globalLanguage:array {.property}
Zwischenspeicher für das globale Sprach-Array, welches von `readLanguage()` geholt wird.

###headerJavaScriptFiles:array {.property}
Liste von URLs von Javascript Dateien die im Seiten-Header geladen werden sollen.

###footerJavaScriptFiles:array {.property}
Liste von URLs von Javascript Dateien die im Seiten-Footer geladen werden sollen.

###cssFiles:array {.property}
Liste von URLs von CSS Dateien, die im Seiten-Header geladen werden sollen.



##Methoden
Folgende Methoden können innerhalb deiner eigenen Module oder in Dokumentationstemplates verwendet werden.

###Cosmo($test, $me):Cosmo {.method .constructor}
Automatisch innerhalb der `index.php` aufgerufen. Liest die globalen Einstellungen von `docs/cosmo_config.json` aus,
danach wird die Dateistruktur ausgelesen, sowie alle vorhandenen Module geladen.

###init():void {.method}
Ebenfalls innerhalb der `index.php` aufgerufen. Diese Methode übernimmt die Aufgabe des Routing, sowie das Laden und
Anzeigen der aktuellen Seite.

###readLanguage():array {.method}
Gibt den Inhalt der momentan genutzten Haupt-Sprachdatei (gespeichert in `lib/language/`) zurück. Diese Datei enthält
globale Sprachwerte, welche über alle Seiten der Dokumentation hinweg verwendet werden.

###readJSONBlock($content, $blockTag, [$assoc], [$noindent]):array {.method}
Liest einen JSON Block mit dem angegebenen Namen aus dem Content-String aus.

Gibt solch ein Array zurück:

    {
        "tag": "deinTagName",
        "start": 123,
        "end": 3112,
        "json": {}
    }

`Start` und `end` sind die Zeichenpositionen des Anfangs und Ende des JSON Block im Content-String.

###getTwigStringInstance():Twig_Environment {.method}
Gibt ein Twig Environment zum Rendern von Twig Templates zurück. Dies ist für Module implementiert,
die ihre eigenen Untertemplates rendern möchten.

Ein Beispiel zur Verwendung:

    $twig = $cosmo->getTwigStringInstance();
    $html = $twig->render($templateString, $dataArray);


###stripJSONBlock(&$content, $result, [$replace]):void {.method}
Hier kannst du ein JSON Block Objekt übergeben, dass du vorher von `readJSONBlock()` erhalten hast, um
den Block mit etwas Anderem zu ersetzen. Ersetzt den Block standardmäßig mit einem leeren String (entfernt ihn).

Achtung: Diese Funktion überrschreibt den übergebenen $content String direkt.


###getPageList($type):array {.method}
Methode um Arrays der verschiedenen Inhaltstypen von Cosmo abzurufen. Wird verwendet um die Navigation innerhalb von
Cosmos Seitentemplate zu erzeugen. Der Typ kann entweder `article`, `page` oder `reference` sein.

Beispiel des zurückgegebenen Arrays:

    [
        {
            "url": "article/de/beispiel",
            "title": "Beispielartikel"
        }
    ]

###getArticles():array {.method}
Kürzel für `getPageList('article')`.

###getPages():array {.method}
Kürzel für `getPageList('page')`.

###getReferences():array {.method}
Kürzel für `getPageList('reference')`.


###renderLanguageWidget():string {.method}
Rufe dies wie folgt aus einem Seitentemplate heraus auf: `{{ cosmo.renderLanguageWidget()|raw }}`.
Dies Platziert das Cosmo Sprach-Widget auf deiner Seite. Die Template-Datei `templates/wgt-language.twig` aus dem
Theme Ordner wird verwendet um das Sprach-Widget zu rendern.

###callHook($hookName, [$data]):mixed {.method}
Dies ruft alle Methoden auf, die für den angegebenen Hook aus Modulen oder anderem Code heraus registriert wurden.
Die Methode kann von Überall her aufgerufen werden - Module können ihre eigenen Hooks einführen, Hooks können sogar
aus Templates heraus aufgerufen werden, damit zusätzliche Inhalte eingeschoben werden können.

Wenn du die `$data` Eigenschaft in die Methode übergibst wird diese an jede Methode übergeben, welche für den Hook
registriert wurde. Der Wert wird am Ende wieder zurückgegeben nachdem alle Hooks ausgeführt wurden und die Möglichkeit
hatten die Daten zu modifizieren.

###addCSSFile($url, [$media]):void {.method}
Rufe dies auf um eine CSS-Datei hinzuzufügen, die vom aktuellen Theme geladen werden soll. `$url` wird relativ zum
Themeordner behandelt.
Die `$media` Eigenschaft ist standardmäßig auf `screen` gesetzt.

###addJavascriptFile($url, [$header]):void {.method}
Fügt eine Javascript Datei entweder zum Seiten-Header oder -Footer hinzu. `$header` steht standardmäßig auf `false`;
so werden Javascript Tags im Footer der Seite hinzugefügt.


##Events
Cosmo verwendet eine Art Event-System (genannt Hooks) um Modulen die Möglichkeit geben an verschiedenen Punkten in den
Programmfluss und das Rendering einzugreifen. Lies den Artikel über Module um mehr darüber zu erfahren wie du eine Methode
eines Moduls für einen bestimmten Hook registrierst.

###beforeRender {.event}
Wird aufgerufen, bevor das Seitentemplate des aktuellen Themes gerendert wird.

###afterRender($result) {.event}
Wird aufgerufen, nachdem das Seitentemplate des aktuellen Themes gerendert wurde.
`$result` beinhaltet das HTML-Ergebnis, befor es an den Browser übergeben wird.

###modulesLoaded {.event}
Wird direkt nachdem die Dateistruktur gescannt und alle Module geladen wurden aufgerufen.

###module:HOOKNAME($params) {.event}
Dieser Spezialhook wird aufgerufen, wenn ein Aufruf folgenden Schemas getätigt wird:

    /module/[hookname]/[params]/...

Dieses Konstrukt kann verwendet werden, um AJAX Aufrufe zu serverseitigem Modulcode auszuführen.
Alle URL Parameter nach dem Hooknamen werden aufgetrennt und als Array als Hook Parameter übergeben.

Du kannst mehr hierüber im Artikel über Module erfahren.

###routingFinished($page) {.event}
Wird aufgerufen nachdem das Seiten-Routing abgeschlossen wurde und Cosmo sich entschieden hat, welche
Seite es anzeigen muss. Beispiel Seiten-Objekt:

    {
        config: {},
        filePath: "article/de/2_beispiel.md",
        sortOrder: 2,
        type: "article",
        key: "übersetzungsSchlüssel",
        lang: "de",
        url: "article/de/beispiel"
    }

Die `config` Eigenschaft enthält die geparseten Inhalte des "config" JSON Blocks aus der Markdown Datei.
`sortOrder`, `type` und `lang` werden aus dem `filePath` abgeleitet. Die `key` Eigenschaft wird von Cosmo verwendet
um die korrekten Übersetzungen von Seiten aus allen Sprachen miteinander zu verknüpfen.


###contentUnparsed($content) {.event}
Wird aufgerufen, wenn das Markdown Dokument von der Festplatte geladen wurde und sein Config-Block bereits
geparsed und entfernt ist; aber bevor irgendwelcher Markdown-Code interpretiert wurde. Du kannst den Markdown
Code modifizieren bevor er geparsed wird.

###contentParsed($content) {.event}
Wird aufgerufen nachdem der Markdown Code in HTML umgewandelt wurde. Dies ist der HTML Inhalt der Seite,
allerdings noch ohne den HTML-Code, welchen das Seitentemplate des aktuellen Theme hinzufügt.

###renderHeader($html) {.event}
Wird von innerhalb des Seitentemplate aufgerufen, um zusätzlichen HTML-Code in den Seitenkopf einzufügen.
Wird unter Umständen nicht von anderen als dem Standard-Theme verwendet.

###renderFooter($html) {.event}
Wird von innerhalb des Seitentemplate aufgerufen, um zusätzlichen HTML-Code in den Seitenfooter einzufügen.
Wird unter Umständen nicht von anderen als dem Standard-Theme verwendet.