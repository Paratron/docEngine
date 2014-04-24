conf:{
    "key": "inlineDemos",
    "title": "Eingebettete Demos"
}:conf

#Eingebettete Demos
Cosmo erlaubt dir JSON tags in deinen Markdown Dateien anzulegen, mit denen du Demos in deine
Dokumente einbetten kannst.

Der Zweck der eingebetteten Demos ist es, deinen Benutzern Features und Beispiele des dokumentierten
Themas direkt in deinem Dokumentationstext zu zeigen.

Neben einem visuellen, interaktivem Beispiel und Anzeigen der Quellltexte, gibt
das eingebettete Demo-Element deinen Usern auch die Möglichkeit die Demo in einer Sandbox zu bearbeiten,
um durch das Herumspielen ein besseres Verständnis zu erlangen.

##Sicherheit bei eingebetteten Demos
Eingebettete Demos können nur statische Resourcen verwenden. Du kannst keine Demos erstellen, die
irgendeine Art von serverseitiger Sprache wie PHP oder Ruby verwenden. Wenn du solche Dateien im
Demo Block auflistest, wird deren Quelltext direkt an den User ausgeliefert.

Bearbeitbare eingebettete Demos erzeugen bei jedem Laden der Seite eine neue Sandbox. Das bedeutet
dass alle Änderungen die ein Besucher vorgenommen hat bei jedem neu-laden oder verlassen der Seite
verworfen werden.

Die Sandboxen sind fest an die Browser Session des Besuchers gebunden. Auf diese Art ist es unmöglich
auf die Sandbox eines anderen Besuchers zuzugreifen.

##Eine Demo einbetten
Bereite zuerst einen Ordner mit allen Dateien die du für deine Demo benötigst in einem Unterordner
von `docs/demos/` vor. Du kannst dich frei entscheiden wie du die Ordnerstruktur in deinem Demos-Ordner
anlegst. Auf diese Weise kannst du Demos am besten frei an die Art deiner Dokumentation anpassen.

Hier ein Beispiel einer eingebetteten Demo:

demo:{
    "target": "de/inlineDemoExample/",
    "display": [
        "index.html",
        "demo.css",
        "demo.js"
    ],
    "editable": true
}:demo

Um eine Demo in dein Markdown Dokument einzubetten, verwendest du einfach einen JSON Block in deiner
Markdown Datei, welcher die Einstellungen für das Demo Element enthält.

Für die obrige Demo haben wir den folgenden JSON Block verwendet:

    demo:{
        "target": "de/inlineDemoExample/",
        "display": [
            "index.html",
            "demo.css",
            "demo.js"
        ],
        "editable": true
    }:demo

Zuerst gibst du den `target` Ordner an, in dem die Dateien deiner Demo gespeichert sind. Wir haben
einen Unterordner für jede Sprache in unserem Demos-Ordner angelegt, damit wir Demos in mehreren Sprachen
anbieten können. Achte darauf, dass das Demo Element immer nach einer `index.html` Datei in deinem Demo Ordner
sucht, um sie als Ergebnis-Dokument darzustellen.

Der `display` Array gibt an, welche Dateien aus dem Ordner dem User mit Quelltextansicht gezeigt
werden sollen.

Die `editable` Eigenschaft definiert, ob ein Besucher die Möglichkeit haben soll den angezeigten Quellcode
zu bearbeiten, oder nicht. Damit bearbeitbare Demos funktionieren musst du sicherstellen das Cosmo
schreibzugriff auf den Ordner `lib/cache/` hat.
