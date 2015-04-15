conf:{
    "key": "usingDocEngine",
    "title": "Using DocEngine"
}:conf


#Using DocEngine {#using-docengine}
DocEngine is build very straightforward and can also be used if you don't have much PHP knowledge.

In fact, you won't have to write a single line of PHP to make use of DocEngine as your documentations
rendering system - just download the project, extract it and write your markdown files and you are good
to go.


##Getting started {#getting-started}
After you have downloaded DocEngine [from github](https://github.com/Paratron/DocEngine), you will
have the following file structure (unnecessary files removed):

    +-- docs
       |-- articles
       |-- demos
       |-- pages
       |-- reference
       └-- docEngine_config.json
    +-- lib
       |-- cache
       +-- language
           |-- de.json
           |-- en.json
       +-- php
           |-- [various system files]
       +-- theme
           |-- [various theme folders]
    |-- .htaccess
    |-- index.php

The `docs` folder is completely separated from any logic and contains all your markdown files for
articles, pages and references as well as a dedicated folder for storing demos.

The configuration file `docEngine_config.json` is also stored inside the `docs` folder and is the
main configuration of your documentation. We will analize its contents below.

The `lib` folder contains all logic of DocEngine, the custom modules, as well as theme packs.

> __Heads up!__
> The folder `lib/cache` needs to have write access for PHP in order to use editable demos and advanced caching.

The `.htaccess` file is necessary to map requests to the index.php and enables you to use pretty URLs.
Of course, you need `mod_rewrite` to be activated on your server.

The `index.php` is the heart of DocEngine - it loads and initializes the system and routes any requests
to the correct content.


##The main configuration file {#main-config}
The main config of your documentation is stored inside the file `docs/docEngine_config.json`.

The following is the config data for this documentation, we added comments to be a bit more
descriptive:

    {
        "title": "DocEngine",           //The documentations title to be displayed in the header
        "version": "0.4.0",             //A little addition to be displayed besides the header
        "basePath": "/docEngine/",      //Basepath on the server for correct CSS loading
        "homepageType": "page",         //Element type of the homepage - see explanation below
        "homepageKey": "welcome",       //Element key of the homepage
        "defaultLanguage": "en",        //Default language to select
        "cache": false,                 //Should the system cache generated pages on the HDD
        "theme": "flatbeat",            //Name of a folder inside /lib/theme/ to use as doc theme
        "disqus": false,                //Set a disqus handle here to use disqus comments
        "modules": {                    //Different config objects for additional DocEngine modules
            "PrettifySources": {
                "skin": "desert",
                "linenums": false
            },
            "Reference": {              //Configuration for reference pages
                "github": "Paratron/docEngine",
                "branch": "master",
                "quickNavigation": [
                    "property",
                    "method",
                    "event"
                ],
                "definitionTag": "h3"
            }
        }
    }

The properties `title`, `version` and `basePath` are directly used by the selected theme
and may result in varying behaviours depending on the selected theme. For example, the default
theme `flatbeat` will print out the `version` property next to the `title` in the pages header.

Keep an eye on the three properties `homepageType`, `homepageKey` and `defaultLanguage` because they
configure DocEngines default behaviour when it is being accessed without a specially given URL to a
resource. Read more about it below in the [file structure section](#file-structure).

Setting the `cache` property to `true` will enable DocEngine to deliver dynamic demos as well as
caching generated pages on the HDD to speed up your page load times.

The `theme` property defines, which theme should be used to render your documentation for the browser.
It defaults to `flatbeat`.

If you want to use [disqus](http://disqus.com) to add comment sections to the bottom of your doc pages,
just place your disqus handle for the project here. You can fine-tune the usage of disqus for each
page individually from inside the inline configs in your markdown documents.

The huge `modules` object gives configuration options for all bundled and custom modules that are
currently working in your DocEngine installation. DocEngine bundles a couple of modules, such as
`InlineDemos`, `PrettifySources` and `Reference Pages` which can be configured from this central
place.

##File stucture of your documentation {#file-structure}
Your complete documentation is stored in the folder `docs/` which is separated from the logical
code of DocEngine (stored in `lib/`). Because of that you can easily move your documentation around,
update the sourcecode of DocEngine and much more.

The `docs/` folder can have multiple subfolders for content. DocEngine supports i18n (internationalization)
out of the box, so you are required to again create subfolders for each language following the
 [ISO-3166-1 Alpha-2 standard](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements).

####articles/
Store all your articles that are written to support your documentation inside the `docs/articles/`
subfolder. Everything like tutorials or in-depth descriptions go here.

####pages/
If you want to have some download pages or general information about your project, that should go
to the `docs/pages/` subfolder.

####reference/
Reference pages are used to document the functions, events and other stuff of your classes. The
reference pages are very flexible and can also be tweaked to support REST references or include
CSS class listings of your project or whatever you may need to build reference pages for.
There is a dedicated article for more detailed information about [reference pages](reference-pages).

####demos/
This folder stores all demo contents for your in-line demos inside your documentation. Create subfolders
for each demo (you may also nest those subfolders) and point a demo-tag to such a folder. Read more about
them in the article about [inline demos](inline-demos).

This folder behaves a bit different to the other content folders - its sub-structure is _not_ bound
to the i18n mechanism, so you don't need to create subfolders for different languages. But you are
encouraged to do so, if you want to translate your demos as well.


###How URLs are mapped against the markdown files
A DocEngine content URL looks like this:

    /language/type/key

Where `language` is the two-character country code of the language you want to serve. For example
`en` for english, or `de` for german.

The `type` property of the URL can be `article`, `page` and `reference` (and `module` - more about that in the [module article](custom-modules#exclusive-routing)).

