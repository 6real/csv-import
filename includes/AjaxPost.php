<?php

namespace Arkanite\CSV;

if (!defined('ABSPATH')) {
    exit;
}

class AjaxPost
{
    public function hooks()
    {
        add_action('wp_ajax_create_formation_post', [$this, 'bind']);
    }

    public function bind()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'code' => 'not_authorized',
            ]);
            exit;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'post_formation')) {
            wp_send_json_error([
                'code' => 'not_authorized',
            ]);
            exit;
        }

        $sanitizedArray = $this->sanitizer($_POST['data']);

        $this->post($sanitizedArray);

//        wp_send_json_success($sanitizedArray, 200);
        wp_die();
    }

    public function sanitizer($data): array
    {
        $sanitizedArray = [];

        $json_string = stripslashes($data);
        $formations = json_decode($json_string, true);

        foreach ($formations["formations"] as $formation) {
            $sanitizedItem = [];
            foreach ($formation as $index => $item) {
                $index = strtolower($index);
                $index = str_replace('é', 'e', $index);
                $index = str_replace('à', 'a', $index);
                $index = str_replace('ç', 'c', $index);
                $index = str_replace('è', 'e', $index);
                $index = str_replace(' ', '_', $index);

                $sanitizedItem[$index] = $item;
            }
            $sanitizedArray[] = $sanitizedItem;
        }
        return $sanitizedArray;
    }

    public function post($data)
    {
        foreach ($data as $formation) {

            $ID = $formation['id'];
            $title = $formation['titre_de_la_formation'];
            $category = $formation['categorie_de_formation'];
            $level = $formation['niveau'];
            $time = $formation['duree'];
            $picture = $formation['photo_'];

            $content_pedagogic = $formation['contenu_pedagogique'];
            $eligibility = $formation['elibilite'];
            $evaluation = $formation['evaluation_et_validation_des_acquis'];
            $structure = $formation['la_structure'];
            $advantage = $formation['les_+_de_sta'];
            $systems = $formation['les_systemes'];
            $place = $formation['lieu_'];
            $aero_manual = $formation['manuel_aeronautique'];
            $pedagogic_method = $formation['methodes_pedagogique'];
            $formation_num = $formation['numero_de_formation'];
            $goal = $formation['objectifs_de_la_formation'];
            $public_and_requirement = $formation['public_et_prerequis'];
            $tracing = $formation['tracage'];
            $terminology = $formation['vocabulaire'];

            $term = get_term_by('name', $category, 'categorie_formations');

            // Construct post data.

            //check if post with id  is already in database, if so, update post
            if (get_post_status($ID)) {
                $post = [
                    'ID' => intval($ID),
                    'post_title' => $title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => 'formation',
                    'post_author' => 1,
                    'tax_input' => [
                        "categorie_formations" => $term->term_id
                    ],
                ];
            } //if not in database, add post with id
            else {
                $post = [
                    'import_id' => intval($ID),
                    'post_title' => $title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => 'formation',
                    'post_author' => 1,
                    'tax_input' => [
                        "categorie_formations" => $term->term_id
                    ],
                ];

            }


            // Insert the post into the database.
            $postID = wp_insert_post($post);
            $arrayValueACF =[
                'a' =>'Initiation',
                'b' => 'Perfectionnement',
                'c' => 'Qualification',
                'd' => 'Spécialisation',
                'e' => 'Formation diplômante'
            ];

            foreach ($arrayValueACF as $index => $value){
                if ($value === $level){
                    update_field('niveau', [$index], $postID);
                }
            }
            update_field('numero_de_formation', $formation_num, $postID);
            update_field('objectifs_de_la_formation', $goal, $postID);
            update_field('methodes_pedagogiques', $pedagogic_method, $postID);
            update_field('contenu_pedagogique', $content_pedagogic, $postID);
            update_field('evaluation_et_validation_des_acquis', $evaluation, $postID);
            update_field('public_et_prerequis', $public_and_requirement, $postID);
            update_field('duree', $time, $postID);
            update_field('lieu', $place, $postID);
            update_field('eligibilite', $eligibility, $postID);
            update_field('vocabulaire', $terminology, $postID);
            update_field('manuel_aeronautique', $aero_manual, $postID);
            update_field('tracage', $tracing, [$postID]);
            update_field('la_structure', $structure, $postID);
            update_field('les_systemes', $systems, $postID);
            update_field('les_+_de_sta', $advantage, $postID);

            $this->setImg($postID, $picture);
        }
    }

    function setImg($postID, $imgName){
        $base_upload_folder = 'import'; // nom du sous-dossier de wp-content/upload dans lequel mettre l'image uploadée et traîtée
        $image_max_size = 1024; // taille maximum (largeur ou hauteur) de l'image originale gardée
        $plugin_dir = ABSPATH . 'wp-content/plugins/csv-import';

        set_time_limit(300); // augmenation du temps de traitement max si besoin
        ini_set('max_execution_time', 300); // augmentation du temps de traitement max si besoin
        ini_set('memory_limit', '8192M'); // augmentation de la mémoire max si besoins

        $wp_upload_path = wp_upload_dir()['basedir']; // dossier d'upload de base de WP
        if (!is_dir($wp_upload_path.'/'.$base_upload_folder.'/')){ // si le sous-dossier voulu pour l'upload n'existe pas...
            mkdir($wp_upload_path.'/'.$base_upload_folder.'/'); // ...on le crée
        }


        $attached = get_attached_media('image', $postID); // on récupère la liste des éventuelles "photo" attachées au post

        wp_delete_attachment($attached->ID, true); // on supprime l'attachement ainsi que les fichiers physiques associés


        if ($plugin_dir.'/assets/img/'.$imgName.'.jpg') { // si on a une URL de photo
            $filename = basename($plugin_dir.'/assets/img/'.$imgName.'.jpg'); // on récupère le nom du fichier
            $upload_path_dir = $wp_upload_path.'/'.$base_upload_folder.'/'; // on définie le chemin de notre dossier d'upload

            if (!is_dir($upload_path_dir)) {// si le sous-dossier custom n'exite pas...
                mkdir($upload_path_dir); // ...on le crée
            }

            $upload_path = $upload_path_dir.$filename; // on définie le chemin complet pour l'upload du fichier
            $c = 2;

//            while(is_file($upload_path)) // si jamais un fichier portant le même nom...
//                $upload_path = $upload_path_dir.pathinfo($filename,PATHINFO_FILENAME).'-'.($c++).'.'.pathinfo($filename,PATHINFO_EXTENSION); // on ajoute un suffixe numérique pour éviter l'écrasement

            file_put_contents($upload_path, file_get_contents($plugin_dir.'/assets/img/'.$imgName.'.jpg')); // on récupère le fichier distant (peut être remplacé par du cURL au choix)
            $image = wp_get_image_editor($upload_path); // on prépare le fichier récupérer dans l'éditeur d'images de WP

            if (!is_wp_error($image)) { // s'il n'y a pas d'erreur...
                $image->resize($image_max_size, $image_max_size, false); // ...on redimentionne l'image originale...
                $image->save($upload_path); // ...et on la sauvegarde
            }

            $filetype = wp_check_filetype(basename($upload_path), null); // on récupère le type de fichier

            $attachment = array( // on définie les paramètres de la pièce jointe...
                'guid'           => $upload_path,
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $upload_path, $postID); // ...qu'on crée et joint au post
//            require_once(ABSPATH.'wp-admin/includes/image.php'); // on charge la librairie de gestion d'images de WP
//            $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path); // on génère des métadatas pour la pièce jointe
//            wp_update_attachment_metadata($attach_id, $attach_data); // on les associe à la pièce jointe
            // Define attachment metadata
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path );

            // Assign metadata to attachment
            wp_update_attachment_metadata( $attach_id, $attach_data );

            // And finally assign featured image to post
            set_post_thumbnail( $postID, $attach_id );
        }

    }
}

