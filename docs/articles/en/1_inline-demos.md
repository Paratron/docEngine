conf:{
    "key": "inlineDemos",
    "title": "Inline Demos"
}:conf

#Inline Demos
Cosmo allows you to place JSON tags inside your markdown files to embed inline demos into
your documents.

The purpose of inline demos is to show your users features and examples of the documented
entity right beside your descriptive texts.

Besides of displaying a visual example and letting users peek into the source code, the
inline demo element also allows users to modify the predefined sources in a sandboxed
environment so they can play around with your examples to get a better understanding.


##Embedding an inline demo
First, prepare a folder with all the assets needed for your demo in a subfolder of `docs/demos/`.
You are free to decide about the folder structure inside your demos folder so it fits the best
to the kind of documentation you write.

Here's an example of a inline demo:

demo:{
    "target": "en/inlineDemoExample/",
    "display": [
        "index.html",
        "demo.css",
        "demo.js"
    ],
    "editable": false
}:demo

To embed a inline demo into your markdown element, you simply use a JSON block inside
your markdown file, describing the settings of your demo element.

For the above demo, we used the following JSON block:

    demo:{
        "target": "en/inlineDemoExample/",
        "display": [
            "index.html",
            "demo.css",
            "demo.js"
        ],
        "editable": true
    }:demo

First, you specify the `target` folder where your demo data is stored. We created a subfolder
for each language inside our demos folder to provide demos in multiple languages. Keep in mind
that the inline demo presenter always looks for a index.html inside your target folder to display
it as the result document.

The `display` array specifies which files from the folder should be displayed to the user
in a source window.

The `editable` property defines if a user should be able to modify the displayed code in the
source windows, or not. In order to make editable demos work, you need to make sure that cosmo
has write access to the `lib/cache/` folder.
