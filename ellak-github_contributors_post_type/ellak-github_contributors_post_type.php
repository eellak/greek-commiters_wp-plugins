<?php

/**
 * ellak - Greek Github Contributors Post Type
 *
 * @package     none
 * @author      David Bromoiras
 * @copyright   2016 Your Name or Company Name
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Greek Github Contributors Post Type
 * Plugin URI:  
 * Description: Accomodate the github api retrieved json into custom post type instances.
 * Version:     0.1
 * Author:      David Bromoiras
 * Author URI:  https://www.anchor-web.gr
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txtd
 *
 **/

  /* PLUGIN DOCUMENTATION AT https://team.ellak.gr/projects/sites/wiki/Greek-github-contrs/ */
if (! post_type_exists('github_contributor')) {
    function add_greek_github_contributor_post_type() {
        $labels = array(
            'name' => 'Github Developers',
            'singular_name' => 'Github Developer',
        );
        $description = 'Περιέχει τις πληροφορίες που έχουν αντληθεί από το github και αφορούν τους ενεργούς Έλληνες contributing developers.';
        $args = array(
            'labels' => $labels, 
            'description' => $description, 
            'has_archive'=>true, 
            'public' => true, 
            'show_ui' => true, 
            'show_in_menu' => true, 
            'supports' => array('title', 'custom-fields'), 
            'exclude_from_search'=>false, 
            'publicly_querable'=>true,
            );
        register_post_type('github_contributor', $args);
    }
    //register_activation_hook(__FILE__, 'add_greek_github_contributor_post_type');
    add_action('init', 'add_greek_github_contributor_post_type');
    
    add_action('init', 'contr_taxonomies');
    function contr_taxonomies(){
        $labels=array('name'=>'Εταιρία');
        $args=array('labels'=>$labels);
        register_taxonomy('github_contr_eteria', 'github_contributor', $args);
        
        $labels=array('name'=>'Τοποθεσία');
        $args=array('labels'=>$labels);
        register_taxonomy('github_contr_topothesia', 'github_contributor', $args);
    }
    
    add_action('pre_get_posts', 'set_contributors_per_page_number');
    function set_contributors_per_page_number($query){
        if(is_post_type_archive('github_contributor')){
            $query->set('posts_per_page', 42);
            return $query;
        }
    }

    
//    add_action('pre_get_posts', 'ellak_set_contributors_order_by');
//    function ellak_set_contributors_order_by($query){
//        if(isset($query->query_vars['post_type']) && strcmp($query->query_vars['post_type'], 'github_contributor')){
//            $contr_order_string=get_query_var('contr_order');
//            if(isset($contr_order_string) && !strcmp($contr_order_string, "")){
//            error_log($contr_order_string.'blah');
//                switch($contr_order_string){
//                    case 'followers':
//                        break;
//                    case 'language':
//                        $query->set('meta_key', 'contributor_full_name');
//                        error_log('inside');
//                        break;
//                    default:
//                        $query->set('orderby', 'meta_value_num');
//                        $query->set('meta_key', 'contributions_number');
//                        $query->set('order', 'DESC');
//                        break;
//                }
//            }
//        }
//    }
}

