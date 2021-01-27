# ADOPTE UN MEC | TINDER

[![forthebadge](https://s.adopteunmec.com/fr/www/img/_common/logos/aum_256_256.jpg?d2e5c7c1dc5cf98b5e9f9ce208a8f5dc)]() 
[![forthebadge](https://assets.stickpng.com/images/580b57fcd9996e24bc43c53b.png)]() 


Site web | statistiques | booster de rencontres

## Technos

- Symfony 5
- Node Javascript 
- MYSQL

### Installation

``php bin/console doctrine:schema:update -f`` 

``php bin/console server:run`` 

## CLI Démarrage

Choissiez votre payload : 
_Scraping de profils_: Executez la commande ``php bin/console app:parse compte@gmail.com 'passord' 2chiffresducodepostal`` 
_Scraping d'informations sur profils en base_: Executez la commande ``php bin/console app:parse compte@gmail.com 'passord'``
_Scraping d'image pour enregistrement en binaire dans la base_: Executez la commande ``php bin/console app:getimages``  


## Versions

**Fonctionnalité :**

- Visualisation des profils sur carte de France
- Visualisation des déplacements par profils 
- Booster de profils 
- Statistiques
- Moteur de recherche

## License

Ce projet est sous licence ``WTFTPL`` - voir le fichier [LICENSE.md](LICENSE.md) pour plus d'informations


