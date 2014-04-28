conf:{
    "root": "Cosmo",
    "title": "Cosmo",
    "constructor": false,
    "file": "lib/php/Cosmo/Cosmo.php",
    "key": "ref:cosmo"
}:conf

#Cosmo
The cosmo class is accessable from within the page template, as well as from every module and other piece of code.
If you want to access the Cosmo object from within a template, simply use the `cosmo` object in your Twig template. If
 you want to access the Cosmo object from within any PHP code (module or else), make sure to call `global $cosmo;` to
 make the global Cosmo object available inside your function/class.

##Object Properties


###mainConfig:array {.property}
Contains the global config data. Is defined in the file `docs/cosmo_config.json`.


###localConfig:array {.property}
Contains the config object of the current page. Is defined inside a `conf:{}:conf` JSON block at the beginning
of each markdown page. Properties of the local config should be treated more important than the global config.


###themeFolder:string {.property}
The path to the currently used theme folder. Can be defined in the main config with the property `theme`.


###requestParams:array {.property}
Contains the request URI parameters, split into an array.


###requestURL:string {.property}
The current request URL minus the base path. So for example `article/en/example`.


###currentPage:array {.property}
Contains the current page object being emitted by the routing function.
Object example:

    {
        config: {},
        filePath: "article/en/2_example.md",
        sortOrder: 2,
        type: "article",
        key: "translationKey",
        lang: "en",
        url: "article/en/example"
    }


###contentPath:string {.property}
The file path of the currently used content file. For example: `docs/articles/en/example.md`.


###content:string {.property}
The HTML result of the parsed markdown source.


###globalLanguage:array {.property}
Cache for the global language array fetched by readLanguage()


###headerJavaScriptFiles:array {.property}
List of URLs of javascript files to be loaded in the page header.


###footerJavaScriptFiles:array {.property}
List of URLs of javascript files to be loaded in the page footer.


###cssFiles:array {.property}
List of URLs of css files to be loaded in the page header.



##Methods
The following methods can be used inside of your custom modules, or the documentation template.


###Cosmo($test, $me):Cosmo {.method .constructor}
Automatically called in the `index.php`. Reads the mainConfig from `docs/cosmo_config.json`, then
reads the file structure and loads all available modules.

###init():void {.method}
Also called from the `index.php`. This method does the routing job, as well as loading and displaying
the current page.

###readLanguage():array {.method}
Returns the content of the currently used main language file (stored in `lib/language/`. This file contains
global language strings to be used across the docs page.

###readJSONBlock($content, $blockTag, [$assoc], [$noindent]):array {.method}
Reads a JSON block with the given block name from the given content string.

Will return such an array:

    {
        "tag": "yourTagName",
        "start": 123,
        "end": 3112,
        "json": {}
    }

Start and end are the character positions of the beginning and end of the JSON block in the content string.

###getTwigStringInstance():Twig_Environment {.method}
Returns a twig environment to render Twig templates anwhere in the code - this
is implemented for modules that want to render their own sub-templates.

Example usage:

    $twig = $cosmo->getTwigStringInstance();
    $html = $twig->render($templateString, $dataArray);


###stripJSONBlock(&$content, $result, [$replace]):void {.method}
You can pass a JSONBlock object, previously received by `readJSONBlock()` here and get the string inside
 the content replaced with something else. Will replace the block with nothing by default (remove it).

 Heads up: this directly affects the $content string.


###getPageList($type):array {.method}
Method to fetch arrays of the different content types from cosmo. Used for creating a navigation
inside cosmos page template. The type can be `article`, `page`, or `reference`.

Example of the returned array:

    [
        {
            "url": "article/en/example",
            "title": "Example article"
        }
    ]

###getArticles():array {.method}
Shorthand for `getPageList('article')`.

###getPages():array {.method}
Shorthand for `getPageList('page')`.

###getReferences():array {.method}
Shorthand for `getPageList('reference')`.


###renderLanguageWidget():string {.method}
Call this from inside the page template like so `{{ cosmo.renderLanguageWidget()|raw }}`, to place
the cosmo language widget on your page. The template file `templates/wgt-language.twig` from inside
the theme folder will be used to render the language widget.


###callHook($hookName, [$data]):mixed {.method}
This will call all methods that have been registered to the hook with the given name by modules or other
code. Cann be called from anywhere - modules can introduce their own hooks - hooks are even called from inside
the template file.

If you pass a data property into the method, it will be passed to each method that is assigned to the hook and
be returned when all hooks have been run.


###addCSSFile($url, [$media]):void {.method}
Call this to add a CSS file to be loaded by the current theme. `$url` is threated relative to the theme path.
The `$media` property defaults to `screen`.


###addJavascriptFile($url, [$header]):void {.method}
Adds a Javascript file either to the page header or page footer. `$header` defaults to `false` so the script
tag goes to the footer.


##Events
Cosmo utilizes some kind of event system (called hooks) to enable modules to intervent with flow and rendering
of cosmo at any time. See the article about modules to learn about how to register a method of a module to a
certain hook.

###beforeRender {.event}
Is being called before the page template of the current theme is being rendered.

###afterRender($result) {.event}
Is being called after the page template of the current theme has been rendered.
$result contains the HTML result before its being passed to the browser.

###modulesLoaded {.event}
Being called right after the fileStructure has been scanned and all modules
have been loaded.

###module:HOOKNAME($params) {.event}
Special hook that is being called when a request has been made to:

    /module/[hookname]/[params]/...

This construction can be used to perform AJAX calls to serverside module code.
All URL parameters after the hook name are split into an array and passed as
hook parameter.
Read more about this in the article about modules.

###routingFinished($page) {.event}
Being called after the page routing has been finished and cosmo has decided
which page to show. Example page object:

    {
        config: {},
        filePath: "article/en/2_example.md",
        sortOrder: 2,
        type: "article",
        key: "translationKey",
        lang: "en",
        url: "article/en/example"
    }

The `config` property contains the parsed contents of the "config" JSON block from
inside the markdown document. `sortOrder`, `type` and `lang` are taken from the
pages `filePath`. The `key` property is used by cosmo to connect translations
of pages from different languages together.


###contentUnparsed($content) {.event}
Being called when the markdown document has been loaded from disc and its
config block has already been parsed and removed, but before any markdown
is resolved. You can modify the markdown code before its being parsed.


###contentParsed($content) {.event}
Being called after the markdown code has been parsed. This is the HTML content
of the page without the HTML from the current themes page template.


###renderHeader($html) {.event}
Called from within the page template to inject additional HTML into the
page header. Might not be utilized in other than the default theme.


###renderFooter($html) {.event}
Called from within the page template to inject additional HTML into the
page footer. Might not be utilized in other than the default theme.