<?php

/**
 * ellak - Greek Github Contributors Delete Posts
 *
 * @package     none
 * @author      David Bromoiras
 * @copyright   2016 Your Name or Company Name
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Greek Github Contributors Delete Posts
 * Plugin URI:  
 * Description: Delete all existing posts of Github Contributor custom post type.
 * Version:     0.1
 * Author:      David Bromoiras
 * Author URI:  https://www.anchor-web.gr
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 **/

  /* PLUGIN DOCUMENTATION AT https://team.ellak.gr/projects/sites/wiki/Greek-github-contrs/ */
if (post_type_exists('github_contributor')) {
    register_activation_hook(__FILE__, 'delete_all_github_contributor_post_type');
    function delete_all_github_contributor_post_type() {
        $wp_my_query=new WP_Query(array('post_type'=>'github_contributor', 'posts_per_page'=>-1, 'post_status'=>'any'));
        if($wp_my_query->have_posts()){
            while($wp_my_query->have_posts()){
                $wp_my_query->the_post();
                wp_delete_post(get_the_ID(), true);
            }
        }
    }
}
