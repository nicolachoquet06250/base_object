# base_object
Framework avec gestion de DAO et de requettes sql 
( gestion de formats de données sous forme de sql tel que le format **JSON** ) 
avec une classe de base.
<br><br>
Génération automatique d'une page de documentation CSS en bootstrap.
<br><br>
Possibilité d'ajouter un tableau de débug à n'importe quel endrois dans 
une page en ajoutant dans le code HTML `[debug]` à l'endrois où on veux ajouter 
le tableau.
<br>
Le tableau apparetras lorsequ'on passera la variable `$_GET['debug']` à `on`
ou si on veux l'activer tout le temps, 
il suffit de rajouter cette ligne de code au debut du script php :
 - `debug::active();`

# Prérequis
- php 7
- apache / ngnix / php7-fpm ( commande pour activer le serveur : `php -S localhost:<port>` )
- node.js
- npm

Vous n'avez plus qu'à vous mettre dans le répertoir de 
votre projet dans un terminal et taper la commande `npm install`

#Mémo
Génération d'images de test de n'importe quelle taille : `http://via.placeholder.com/<X>x<Y>`
