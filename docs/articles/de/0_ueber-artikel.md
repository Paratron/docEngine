conf:{
    "key": "aboutArticles",
    "title": "Über Artikel"
}:conf


#Über Artikel

Artikel sind völlig statische Markdown Dokumente. Du kannst
[markdown extra](http://michelf.ca/projects/php-markdown/extra/)
verwenden, um zusätzliche Elemente wie Tabellen, Definitionslisten,
Fußnoten und mehr zusätzlich zur grundlegenden Markdown Syntax
einzusetzen.

Artikel werden ebenfalls durch Module beeinflusst - also kann ihre
Ausgabe in verschiedensten Wegen angepasst werden. Standardmäßig fügt
DocEngine Syntax-Highlighting zu den Code Blöcken, sowie die Unterstützung
von eingebetteten Beispielen zu den Zusatzfeatures hinzu, die du in
statischen Artikeln verwenden kannst.

Verwende Artikel um kleine Tutorials oder Beispiele für Teile deiner
dokumentierten Einheit zu schreiben, über die du ein wenig mehr Informationen
als nur die Funktionsreferenz zur verfügung stellen willst.

Wenn du etwas dynamischeres brauchst, erzeuge stattdessen eine Seite.
Seiten können vorhergehendes PHP verwenden und die erzeugten Werte innerhalb
des Markdown Dokuments ausgeben. Artikel sind dagegen komplett statisch
und können keine dynamischen Werte empfangen (ausser von Modulen).