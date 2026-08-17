# Le dictionnaire des mots de session

`french-words.tsv` alimente la table `words`, dans laquelle est tiré le mot
proposé par le bouton « Mot au hasard » à l'ouverture d'une session.

## Source

[Lexique 3.83](http://www.lexique.org), New B., Pallier C., Ferrand L.,
Matos R. (2001) — *Une base de données lexicales du français contemporain
sur internet : LEXIQUE*, L'Année Psychologique, 101, 447-462.

Distribué sous licence **CC BY-SA 4.0**. Le fichier présent en est une
adaptation : redistribuer cette liste impose de citer Lexique et de la
partager sous la même licence.

## Ce qui a été retenu

Le dictionnaire intégral tirerait surtout des formes conjuguées et des termes
techniques, qui font de piètres amorces d'écriture. Ne sont gardés que :

- les lemmes (`islem = 1`) au singulier, donc ni conjugaisons ni pluriels ;
- les noms, adjectifs, verbes et adverbes ;
- les mots de 4 à 16 lettres, sans trait d'union, espace ni apostrophe ;
- ceux dont la fréquence (livres ou films) atteint 0,5 par million, ce qui
  écarte les raretés que personne ne saurait faire jouer.

Soit 22 308 mots. La commande de régénération est consignée dans
`ImportFrenchWords`.
