<?php

/**
 * ellak - github query handler.
 *
 * @package     none
 * @author      David Bromoiras
 * @copyright   2016 eellak
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: github_contr query handler.
 * Plugin URI:  
 * Description: .
 * Version:     1.0
 * Author:      David Bromoiras
 * Author URI:  https://www.anchor-web.gr
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txtd
 *
 **/

function ellak_github_contr_query_handler(){
    
    $sort=$_POST['sort'];
    $topothesia=$_POST['topothesia'];
    wp_redirect(home_url()."/github_contributor/?sort=$sort&topothesia=$topothesia");
}
add_action('admin_post_handle_github_contr_query', 'ellak_github_contr_query_handler');
add_action('admin_post_nopriv_handle_github_contr_query', 'ellak_github_contr_query_handler');

function filter_github_contr_query_by_taxonomies($query){
    if($query->is_main_query() && is_post_type_archive('github_contributor')){
        $tax_query=array('relation'=>'AND',);
        
        if (isset($_GET['topothesia'])){
            $topothesia=$_GET['topothesia'];
            if($topothesia!=='null_option'){
                array_push(
                        $tax_query,
                        array(
                        'taxonomy'=>'github_contr_topothesia',
                        'terms'=>$topothesia
                    ));
            }
        }
        if(isset($_GET['sort']) && $_GET['sort']!==''){
            $sort=$_GET['sort'];
            if($sort==='contributions_number' || $sort==='followers_number'){
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
            }
            else{
                $query->set('orderby', 'meta_value');
                $query->set('order', 'ASC');
            }
            $query->set('meta_key', $sort);
        }
        else{
            $query->set('orderby', 'meta_value_num');
            $query->set('meta_key', 'contributions_number');
            $query->set('order', 'DESC');
        }
        $query->set('post_type', 'github_contributor');
        $query->set('tax_query', $tax_query);
    }
}
add_action('pre_get_posts', 'filter_github_contr_query_by_taxonomies');

function add_github_contrs_query_vars_filter($vars){
    $vars[]='sort';
    $vars[]='topothesia';
    return $vars;
}
add_filter('query_vars', 'add_github_contrs_query_vars_filter');