# CVS Import Wordpress Plugin 

Wordpress plugin for importing data from CSV in CPT 


## Requirements

* NPM
* NodeJS 12.2+

## Compatibility

* WordPress 5.0+

## Getting started

After clone the plugin in your /plugins WP folder : 

``
npm install
``

``
npm run build
``
for custom on local env and got a livereload building

``
npm run prod
``

for create build for production

## Files and uderstand how make modifications :
(in French but I will translate this soon in EN)

La plupart de la logique d'import  utilise une class PHP : ``class AjaxPost`` dans  ``/includes/AjaxPost.php``

La logique actuelle fonctionne de cette manière : 

1) Le front du BO du plugin nous parse les infos du CSV et déclenche l'action Ajax nommée ``wp_ajax_create_formation_post``

2) La function ``sanitizer`` est appelée pour clean le contenu du ``$_POST``

3) La function ``post`` est utilisée pour créer le post en fonction des donnéess

4)  La function ``setImg`` est utilisé pour importer les images de la ligne du EXCEL (voir excel exemple) dans le BO. Puis met en place l'image en tant que featured_img dans le post 

####  Si vous souhaitez modifier le front du BO du plugin (pas nécessaire car générique) : ``/app/containers/Admin.jsx`` & ``/app/containers/helpers/CSVButton.jss`` 


*Si vous avez une question à propos de ce plugin -> contact@vision-marketing.fr*