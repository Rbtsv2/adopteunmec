# ADOPTE CLI VERSION 1

[![forthebadge](http://forthebadge.com/images/badges/built-with-love.svg)](http://forthebadge.com)  [![forthebadge](https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Anonymous_emblem.svg/316px-Anonymous_emblem.svg.png)](http://forthebadge.com)

Adopte CLI est un outils permttant de réaliser des stats des célibataires en France

## Pour commencer

Cet outils realisé sur Symfony forunit 3 commandes


### Pré-requis

Ce qu'il est requis pour commencer avec votre projet :

- PhP 7.* 
- MYSQL

### Installation

_Initialiser la base de donnée_: Executez la commande ``php bin/console doctrine:schema:update -f`` 

Ensuite vous pouvez montrer ce que vous obtenez au final...

## Démarrage

_Démarrer le server_: Executez la commande ``php bin/console server:run`` 

Choissiez votre payload : 
_Scraping de profils_: Executez la commande ``php bin/console app:parse`` 
_Scraping d'informations sur profils en base_: Executez la commande ``php bin/console app:parse``
_Scraping d'image pour enregistrement en binaire dans la base_: Executez la commande ``php bin/console app:getimages``  

## Fabriqué avec

* [Symfony](https://symfony.com/) - Framework MVC (Back-end)
* [Twig](https://twig.symfony.com/) - (Compilateur de template)


## Versions

**Dernière version stable :** 1.0
- Système anti-fraude 
- Détection des fraudes
- Traite les profils inactifs
- Visites les profils en masse
- Enregistre les données de profiles
- Gestion des doublons
- Conformité ACIDE


## Auteurs

* **Jhon doe** _alias_ [@outout14](https://github.com/outout14)

Lisez la liste des [contributeurs](https://github.com/your/project/contributors) pour voir qui à aidé au projet !

## License

Ce projet est sous licence ``exemple: WTFTPL`` - voir le fichier [LICENSE.md](LICENSE.md) pour plus d'informations


