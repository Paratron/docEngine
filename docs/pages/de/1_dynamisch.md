conf:{
    "key": "dynamic",
    "title": "Dynamische Seiten"
}:conf

Dynamische Seiten
=================

Während Artikel aus komplett statischen Markdown Dateien generiert wird, können Seiten mit Twig Statements
gemischt werden, um sie zu dynamisieren.

Sofern eine PHP Datei mit dem selben Dateinamen wie die Markdown Datei gefunden wurde, wird diese zuerst
ausgeführt und kann Variablen an die Dokumentations-Engine weiterleiten.

Diese generierten Variablen sind aus der Markdown Datei heraus durch die Twig Syntax nutzbar. Im Grunde
genommen wird die Markdown Datei zum Twig Template! Das bedeutet, zuerst werden alle Twig Konstrukte aufgelöst,
bevor das Markdown geparsed wird.

Wenn Sie keine PHP Datei erstellen können Sie dennoch die Twig Konstrukte verwenden um Eigenschaften der globalen
oder lokalen Config zu vewenden um sie auf der Seite auszugeben oder bestimmte Elemente zu zeigen oder zu verstecken.
Es liegt ganz bei Ihnen.

Ein kleines Beispiel. Die Variable `time` wird in PHP gesetzt:

    The current server time is: {{ vars.time }}