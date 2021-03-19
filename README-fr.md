Thème Bandeau
==================================
Thème moodle héritant du thème Boost et ayant pour but de présenter les actions récurrentes sur un cours sous forme de bandeau.

Objectif
------------
L'objectif du thème était de pouvoir rendre disponible plus facilement aux utilisateurs (notamment gestionnaires) les actions récurrentes sur un cours et de pouvoir passer en "mode édition" plus intuitivement. <br/>
Il était également important que ce bandeau puisse permettre de présenter les potentielles fonctionnalités développées spécifiquement dans le dossier local/ par exemple sans trop surcharger le menu de paramétrage (menu "engrenage").

Présentation
------------
- Création d'un bandeau ajouté dans le bloc d'en-tête d'un cours. Il présente les actions récurrentes possibles sur un cours triées par type d'action.
- Possibilité de passer en "mode édition" plus facilement.
- Possibilité de garder le bandeau en haut de page même si l'on scrolle dans le cours.
- Possibilité d'ajouter un bloc facilement sans passer par le menu déroulant de gauche.
- Possibilité de revenir plus facilement à la page principale du cours.
- Afficher en fond du bloc d'en-tête l'image de cours définie dans les paramètres (à partir de la version 3.6 de moodle, sinon affichage de l'image par défaut configurable dans les réglage du thème).
- Utiliser une image de cours par défaut pour les vignettes de cours (à partir de la version 3.7 de moodle).

Pré-requis
------------
- Moodle en version 3.3 (build 2017051500) ou plus récente.<br/>
-> Tests effectués sur des versions 3.3 à 3.10.1 (attention toutes les fonctionnalités ne seront pas forcément présentes pour les versions 3.5.x à 3.5.x).<br/>
- Même autres pré-requis que pour le thème Boost.

Installation
------------
1. Installation du plugin

- Avec git:
> git clone https://github.com/andurif/moodle-theme_bandeau.git theme/bandeau

- Téléchargement:
> Télécharger le zip depuis https://github.com/andurif/moodle-theme_bandeau/archive/master.zip, dézipper l'archive dans le dossier theme/ et renommer le si besoin le dossier en "bandeau" ou installez-le depuis la page d'installation des plugins si vous possédeez les bons droits..
  
2. Aller sur la page de notifications pour finaliser l'installation du plugin.

3. Une fois l'installation terminée, plusieurs options d'administration sont à renseigner:

> Administration du site -> Présentation -> Thèmes -> Paramètres Bandeau -> default_course_img

Ce réglage permet de fixer l'url d'une image qui sera utilisée par défaut au niveau du bandeau de cours si aucune image n'a été défini au niveau du cours.

> Administration du site -> Présentation -> Thèmes -> Paramètres Bandeau -> show_default_course_img

Ce réglage permet d'indiquer si l'image définie juste avant doit être prise en compte ou non (si décoché l'image de fond du bandeau sera utilisée seulement pour les cours où une image de cours est définie).

Comment personnaliser le thème ?
-----
Par défaut, les actions les plus récurrentes et présentes dans le core de moodle ont été utilisées mais il est possible d'en ajouter d'autres.<br/>
Pour cela, modifiez le fichier <i>lib.php</i> et particulièrement les fonctions <i>theme_bandeau_build_header_links()</i> et <i>theme_bandeau_render_page_header_output()</i>.

- theme_bandeau_build_header_links()<br/>
Le but de cette fonction est de construire le tableau regroupant les liens à afficher selon des "types" à définir et selon des capacités par exemple.<br/>
```php
<?php
/* Structure du tableau à renvoyer */
$links = [
    "item1" => [  //Item principal
        "title" => ["icon" => "cf_icon_material", "label" => "Libellé item1"],
        "categories" => [  //tableaux des sous items
            "subitem1" => [  //Sous item
                 "icon" => "cf_icon_material",
                 "label" => "Libellé item1",
                 "links" => [  //Liste des lients
                    "icon" => "",
                    "label" => "Libellé item1",
                    "url" => "Url lien1"
                 ]
            ]
        ]
    ],
    "item2" => [  //Item principal
        "title" => ["icon" => "cf_icon_material", "label" => "Libellé item2"],
        "links" => [  //tableaux des liens des sous items
            [  //Sous item direct
                "icon" => "cf_icon_material",
                "label" => "Libellé sous item direct",
                "url" => "Url sous item direct"
            ]
        ]
    ],
    "item3" => [
        "title" => [  //Item lien direct
            "icon" => "",
            "label" => "Libellé Item direct",
            "url" => "Url item direct"
        ]
    ]
];
```

- theme_bandeau_render_page_header_output()<br/>
Le but de cette fonction est de réorganiser le tableau précédent. La seule personnalisation est faire dans cette fonction est de définir les éléments du tableau $links_items dans l'ordre que l'on veut voir les items dans le bandeau.

<strong>Attention</strong>, si un de vos plugins locaux ou additionnels possèdent une fonction du type <i>xxx_render_page_header_output()</i> il pourra il y avoir un conflit dans l'affichage du bandeau et du menu.
Il est cependant possible de n'utiliser que la fonction définie dans ce thème. Pour cela, vous devrez commenter/décommenter une partie de code comme indiqué à partir de la ligne 91 dans le fichier <i>classes/output/core_renderer.php</i> .


Pistes d'améliorations
-----
- Utiliser un fichier de configuration ou un paramétrage pour définir les différents éléments du menu et ainsi éviter de devoir aller dans le code du thème pour modifier le bandeau. De manière similaire au paramètre "Éléments du menu personnalisé" (paramétrable ainsi par les administrateurs pas forcéments développeurs).
- Lister les différentes méthodes d'inscriptions utilisées sur le cours au niveau de l'item "Utilisateurs".
- Modifier le thème en plugin pour qu'il puisse s'adapter à tout type de thème.
- Le plus possible faire des notions d'héritage par rapport au thème Boost (provider par ex, etc...)
- Améliorer l'accessibilité (title...)
- Faire en sorte qu'il n'y est pas de conflit si un autre plugin possède une fonction du type xxx_render_page_header_output().
- Améliorer la visibilité du bouton d'édition au niveau du carnet de notes et de la banque de contenus (souci de visibilité en fonction de l'image de cours utilisée).
- Bug: Si le bandeau de gauche est ouvert et le bandeau en position "sticky" le bouton "Mode édition" n'est plus visible.
- Bug: Si l'on scrolle dans le cours mais que celui-ci n'est pas trop long, possibilité de voir le bandeau freezer. 

A propos
------
<a href="https://www.uca.fr">Université Clermont Auvergne</a> - 2020.<br/>
