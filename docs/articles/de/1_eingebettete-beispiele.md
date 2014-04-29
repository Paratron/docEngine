conf:{
    "key": "inlineDemos",
    "title": "Eingebettete Beispiele"
}:conf

#Eingebettete Beispiele
DocEngine erlaubt dir JSON tags in deinen Markdown Dateien anzulegen, mit denen du Beispiele in deine
Dokumente einbetten kannst.

Der Zweck der eingebetteten Beispiele ist es, deinen Benutzern Features und Beispiele des dokumentierten
Themas direkt in deinem Dokumentationstext zu zeigen.

Neben einem visuellen, interaktivem Beispiel und Anzeigen der Quellltexte, gibt
das eingebettete Beispiel-Element deinen Usern auch die Möglichkeit das Beispiel in einer Sandbox zu bearbeiten,
um durch das Herumspielen ein besseres Verständnis zu erlangen.

##Sicherheit bei eingebetteten Beispiele
Eingebettete Beispiele können nur statische Resourcen verwenden. Du kannst keine Beispiele erstellen, die
irgendeine Art von serverseitiger Sprache wie PHP oder Ruby verwenden. Wenn du solche Dateien im
Beispiel Block auflistest, wird deren Quelltext direkt an den User ausgeliefert.

Bearbeitbare eingebettete Beispiele erzeugen bei jedem Laden der Seite eine neue Sandbox. Das bedeutet
dass alle Änderungen die ein Besucher vorgenommen hat bei jedem neu-laden oder verlassen der Seite
verworfen werden.

Die Sandboxen sind fest an die Browser Session des Besuchers gebunden. Auf diese Art ist es unmöglich
auf die Sandbox eines anderen Besuchers zuzugreifen.

##Ein Beispiel einbetten
Bereite zuerst einen Ordner mit allen Dateien die du für dein Beispiel benötigst in einem Unterordner
von `docs/demos/` vor. Du kannst dich frei entscheiden wie du die Ordnerstruktur in deinem Beispiele-Ordner
anlegst. Auf diese Weise kannst du Beispiele am besten frei an die Art deiner Dokumentation anpassen.

Hier eine Demonstration eines eingebetteten Beispiels:

demo:{
    "target": "de/inlineDemoExample/",
    "display": [
        "index.html",
        "demo.css",
        "demo.js"
    ],
    "editable": true
}:demo

Um ein Beispiel in dein Markdown Dokument einzubetten, verwendest du einfach einen JSON Block in deiner
Markdown Datei, welcher die Einstellungen für das Beispiel Element enthält.

Für das obrige Beispiel haben wir den folgenden JSON Block verwendet:

    demo:{
        "target": "de/inlineDemoExample/",
        "display": [
            "index.html",
            "demo.css",
            "demo.js"
        ],
        "editable": true
    }:demo

Zuerst gibst du den `target` Ordner an, in dem die Dateien deines Beispiels gespeichert sind. Wir haben
einen Unterordner für jede Sprache in unserem Beispiele-Ordner angelegt, damit wir Beispiele in mehreren Sprachen
anbieten können. Achte darauf, dass das Beispiel Element immer nach einer `index.html` Datei in deinem Beispiel Ordner
sucht, um sie als Ergebnis-Dokument darzustellen.

Der `display` Array gibt an, welche Dateien aus dem Ordner dem User mit Quelltextansicht gezeigt
werden sollen.

Die `editable` Eigenschaft definiert, ob ein Besucher die Möglichkeit haben soll den angezeigten Quellcode
zu bearbeiten, oder nicht. Damit bearbeitbare Beispiele funktionieren musst du sicherstellen das DocEngine
schreibzugriff auf den Ordner `lib/cache/` hat.
