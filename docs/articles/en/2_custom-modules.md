conf:{
    "key": "modules",
    "title": "Custom Modules"
}:conf

#Custom Modules
DocEngine is designed to implement custom modules without much hassle. Do you want to have a user system
where people can login to do stuff? Just implement a module. Do you need to have fancy graphs rendered
or want LaTEX to be interpreted and rendered? Its just a small module away.


##Module Basics
The source of your module muse be saved in the folder `lib/php/DocEngine/Modules/`. Thats the folder that
is being scanned by DocEngine and all of the PHP files inside it are loaded as modules.

Your module code needs to be a class that offers only static methods and properties. It will never be
instanciated and only called statically.

Your class is required to have the static properties `$hooks` and `$conf` defined - everything else is optional.

Right now, any additional stuff for modules is stored inside the theme folder. If you need sub-templates,
like the inlineDemo and quickNavigation modules do, those go to `lib/theme/[themename]/templates/modules/`.
The same applies for JS, CSS and images. This makes installing / removing of modules a bit af a hassle, but
since the output of modules might be different, based on the current theme, I haven't figured out a better
way of storing additional module files, yet. If you have an idea, please contact me.


##Understanding a example module
Have a look at one of the most basic modules for DocEngine: the already bundled module that adds googles
code prettifier to the documentation to make your code examples (including this one) look beautiful.

We will explain it in detail below the code block:


    class PrettifySources {
        // Tells docEngine which methods to call when.
        static $hooks = array(
                'contentParsed' => 'prettifyAll'
        );

        // Initial module config. Can be overridden by main config and local config.
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
            $result = str_replace('<pre>',
                                  '<pre class="prettyprint' . $conf . '">',
                                  $source,
                                  $count);

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

The class is being loaded by docEngine in docEngines' `init()` method. DocEngine looks for the `$hooks` property and
registers each given hook to a static method inside the class. In our example class here,
the `prettifyAll` method is hooked against the `contentParsed` hook.

When the `contentParsed` hook is called after the current pages' markdown file has been loaded and interpreted,
the HTML result is passed over to all methods that have been registered to the `contentParsed` hook - including
our method.

So the `prettifyAll` method of our class is being called and handed over the HTML code that is about to be passed
to the current themes template and then to the browser. We want to inject googles prettify library, so we need
to do two things:

- Adding the css class `prettyprint` to all `pre` tags
- Loading the google prettify javascript library into the page

Adding the `prettyprint` class to all `pre` tags is done easily:

    $result = str_replace('<pre>',
                          '<pre class="prettyprint' . $conf . '">',
                          $source,
                          $count);

We even do something more. You can command the prettyprint library to display line-numbers or not
by adding the additional css-class `linenumbers` to the `pre` tag as well.
So by checking the modules config object if linenumbers are wanted, we can decide here to include
the `linenumbers` class here as well, or not. More about module configs below.

After the result has been modified, we are checking the `$count` property, if any replacements have
been made (maybe there aren't any code blocks in the current page). If so, the prettyprint Javascript
library is being loaded into the page via `$docEngine->addJavascriptFile()`.

The modified result is returned at the end of the method so docEngine can finish rendering the page and display
it to the user. It wasn't that complicated, wasn't it?


##Module configuration
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