conf:{
    "key": "dynamic",
    "title": "Dynamic Pages",
    "modules": {
        "PrettifySources": {
            "active": false
        }
    }
}:conf

#Dynamic Pages

While articles are generated from purely static markdown files, pages can be mixed with twig statements
to make them dynamic.

If a PHP file with the same filename exists next to the markdown file in your pages subfolder, it will be
executed first and can forward variables to the documentation engine.

Those generated variables are accessible from within the markdown file through twig syntax. Essentially,
your markdown file becomes a twig template! This means at first, all twig constructs are resolved before
the markdown is parsed.

If you don't create a PHP file, you can still use the twig constructs to output properties from your
global or local config, or use them to show/hide different parts of your pages. Its up to you.

A little example. The variable `time` is set in PHP:

    The current server time is: {{ vars.time }}

For a better understanding, look up the source of this page (both the PHP and markdown file) [on github](https://github.com/Paratron/DocEngine/tree/master/docs/pages/en).