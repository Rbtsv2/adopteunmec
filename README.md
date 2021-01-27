# ADOPTE UN MEC | TINDER

![alt-text-1](https://s.adopteunmec.com/fr/www/img/_common/logos/aum_256_256.jpg?d2e5c7c1dc5cf98b5e9f9ce208a8f5dc "adopteunmec") 

## Technos

- Symfony 5
- Node Javascript 
- MYSQL

### Installation

``php bin/console doctrine:schema:update -f`` 

``php bin/console server:run`` 

## CLI Démarrage

Commandes CLI :  
_Scraping de profils_:``php bin/console app:parse compte@gmail.com 'passord' 2chiffresducodepostal``  
_Scraping d'informations sur profils en base_:``php bin/console app:parse compte@gmail.com 'passord'``  
_Scraping d'image pour enregistrement en binaire dans la base_:``php bin/console app:getimages``   


## Versions

**Fonctionnalité :**

- Visualisation des profils sur carte de France
- Visualisation des déplacements par profils 
- Booster de profils 
- Statistiques
- Moteur de recherche

## License

Ce projet est sous licence ``WTFTPL`` - voir le fichier [LICENSE.md](LICENSE.md) pour plus d'informations


