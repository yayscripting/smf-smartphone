SMF Smartphone
==============
Om wijzigingen door te voeren kun je deze repo clonen en pull requests doen.

Ik stel het op prijs als punten van deze lijst worden opgepakt: http://bugs.gmot.jessedegger.nl/

Verder om te testen kan je een versie van SMF 1.x installeren en de bestanden uit deze repo daarbij uploaden. Graag geen hele SMF-installatie in je pull requests stoppen, deze worden geweigerd :)!

Hartelijk dank voor je medewerking.


Werking
==============
In index.php wordt bepaald of de wireless layout moet worden aangeroepen: https://github.com/yayscripting/smf-smartphone/blob/master/index.php#L125-L140

In wireless.php staan smartphone-gerelateerde functies:
https://github.com/yayscripting/smf-smartphone/blob/master/Themes/default/Wireless.template.php#L957-L1929

Je ziet dat dit een exacte kopie is van de andere wireless functionaliteiten (zoals imode, wap2, wap) maar dan met keurige HTML5. Niet alle pagina's lijken te verwijzen naar wireless.php dus ik weet niet of zoiets als een zoekfunctie mogelijk is maar voel je vrij te experimenteren.

In de map /smartphone staan bestanden die de HTML-layout van de smartphone-versie gebruiken (hier kunnen ook CSS en JS-bugs worden opgelost).
