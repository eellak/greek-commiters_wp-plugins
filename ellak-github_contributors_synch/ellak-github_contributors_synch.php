<?php

/**
 * ellak - Greek Github Contributors Synch Plugin
 *
 * @package     none
 * @author      David Bromoiras
 * @copyright   2016 Your Name or Company Name
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Greek Github Contributors Synch Plugin
 * Plugin URI:  
 * Description: Accomodate the github api retrieved json into custom post type instances.
 * Version:     0.1
 * Author:      David Bromoiras
 * Author URI:  https://www.anchor-web.gr
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txtd
 *

  /* PLUGIN DOCUMENTATION AT https://team.ellak.gr/projects/sites/wiki/Greek-github-contrs/ */


function ellak_github_contributors_synch_posts(){
    $custom_base_dir=get_stylesheet_directory();
    $list_of_developers=array(); //associative array of developers with number of contrbutions
    //access the file with the list of the developer logins and the contributions total
    if(file_exists($custom_base_dir.'/sum_contr-3000.txt')){
        $sum_contr=fopen($custom_base_dir.'/sum_contr-3000.txt', 'r');
        
        /**
         * The black list file is used to exclude the falsely retrieved contributor
         * entries usually because of same name of location but in other countries,
         * eg: Athens, GR - Athens, GA
         * check to see if the black list file exists. If it exists, retrive all
         * its contents to an array, array entry per file line
         **/
        if(file_exists($custom_base_dir.'/black-list-sh.txt')){
            $black_list=file($custom_base_dir.'/black-list-sh.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        }
        
        /**
         * This array holds the administrator normalized location values corresponding
         * to all the different permutations that different contributors enter
         * as their location.
         */
        $location_normalize_array=array();
        if(file_exists($custom_base_dir.'/location_normalize.json')){
            $location_normalize_str=file_get_contents($custom_base_dir.'/location_normalize.json');
            $location_normalize_array=json_decode($location_normalize_str, true);
        }
        
        
        $contr_list_args=array(
            
        );
        //$existing_contributors_list=get_posts();
        
        /**
         * Open the file where we push the locations that satisfy the
         * -UNSPECIFIED- if block.
         */
        if(file_exists($custom_base_dir."/locations_to_review")){
          $locations_to_review=fopen($custom_base_dir."/locations_to_review", "w");
          error_log('locations_to_review file is OPEN.');
        }
        
        /**
         * Create a file to add all the usernames for the contributors
         * that get a record in the wordpress system.
         * Then compare to the list of contributors retrieved by nick 
         * to see where the difference in the number comes from.
         */
        if(file_exists($custom_base_dir."/registered_usernames")){
          $registered_usernames=fopen($custom_base_dir."/registered_usernames", "w");
          error_log('registered usernames file is OPEN.');
        }
        
        while (! feof($sum_contr)){
            $contributor_line=fgetcsv($sum_contr);
            //$contributor_line_csv_array=str_getcsv($contributor_line);
            $contributions_number=$contributor_line[0];
            $contributor_username=trim($contributor_line[1]);
            if(!in_array($contributor_username, $black_list, true)){
                error_log($custom_base_dir.'/greek-commiters-3000/user/info/'.$contributor_username.'.json');
                if (file_exists($custom_base_dir.'/greek-commiters-3000/user/info/'.$contributor_username.'.json')){
                    $contributor_info_str=file_get_contents($custom_base_dir.'/greek-commiters-3000/user/info/'.$contributor_username.'.json', false, NULL, 0, 5000); //setting the max size for security
                    $contributor_info=json_decode($contributor_info_str);
                    if($post_id=post_exists($contributor_username)){
                        //certify that post is PUBLISHED
                        if(get_post_status($post_id)!=='publish'){
                            $tmp_post=array('ID'=>$post_id, 'post_status'=>'publish');
                            wp_update_post($tmp_post);
                        }
                        update_field('id', $contributor_info->id, $post_id);
                        update_field('contributor_username', $contributor_username, $post_id);
                        update_field('contributor_full_name', $contributor_info->name, $post_id);
                        //if($contributor_info.gravatar_id!="")
                            update_field('contributor_avatar_url', $contributor_info->avatar_url, $post_id);
                        //else
                        //   update_field('contributor_avatar_url', $contributor_info.gravatar_id, $post_id);
                        update_field('contributor_github_url', $contributor_info->html_url, $post_id);
                        update_field('contributor_email', $contributor_info->email, $post_id);
                        update_field('contributor_personal_webpage', $contributor_info->blog, $post_id);
                        update_field('contributions_number', $contributions_number, $post_id);
                        update_field('repos_number', $contributor_info->public_repos, $post_id);
                        update_field('followers_number', $contributor_info->followers, $post_id);
                        wp_set_post_terms($post_id, $contributor_info->company, 'github_contr_eteria');
                        $the_location=$contributor_info->location;
                        $non_comma_location=$contributor_info->location;
                        $final_location=array();
                        $unspecified_flag=true;
                        //if(preg_match('/[Gg][Rr][Ee][Ee][Cc][Ee]/', $the_location)==1 || preg_match('/[Hh]{0,1}[Ee][Ll]{1,2}[Aa][SsDd][Aa]{0,1}/', $the_location)==1 || preg_match('/[Gg][Rr]/', $the_location)==1  || preg_match('/[Ee][Ll]/', $the_location)==1){
                            error_log('true');
                            if(preg_match('/[Aa][Tt][Hh][EeIi][Nn][AaSs][Ii]{0,1}/', $the_location)==1){
                              $final_location[]='Athens';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Nn][\.]{0,1}[Ee]{0,1}[Aa]{0,1}[ ]{0,1}[Ss][Mm][YyIi][Rr][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='N. Smyrni';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Gg][Ll][YyIi][Kk][Aa][ ][Nn][Ee][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Gerakas';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Rr][YyIi][Oo][Nn][Ee][Rr][Ii]/', $the_location)==1){
                              $final_location[]='Ag. Stefanos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Gg][Ll][YyIi][Ff][Aa][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Glyfada';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Oo][Ll][Aa][Rr][Gg][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Holargos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[WwOo][Rr][WwOo][Pp][Oo][Ss]/', $the_location)==1){
                                $final_location[]='Oropos';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Hh][Ee][Ss]{1,2}[Aa][Ll]{1,2}[Oo][Nn][Ii][Kk][Ii]/', $the_location)==1 || preg_match('/[Ss][Aa][Ll]{1,2}[Oo][Nn][Ii][Kk][Aa]/', $the_location)==1){
                                $final_location[]='Thessaloniki';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Rr][Tt][Aa]/', $the_location)==1){
                                $final_location[]='Arta';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Aa][Rr][Gg][Aa]/', $the_location)==1){
                                $final_location[]='Kanalaki';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Rr][Ee][VvBb][Ee][Zz][Aa]/', $the_location)==1){
                                $final_location[]='Preveza';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Ee][FfPp][Hh]{0,1}[Aa][Ll][OoIiHh][Nn][Ii][Aa]/', $the_location)==1 || preg_match('/[Aa][Rr][Gg][Oo][Ss][Tt][Oo][Ll][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                                $final_location[]='Argostoli';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Ll]{1,2}[Ii][Tt8][Hh]{0,1}[Ee][Aa]/', $the_location)==1){
                              $final_location[]='Kallithea';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Mm][Aa][Tt][Ee][Rr][Oo][Nn]{0,1}/', $the_location)==1 || preg_match('/[Aa][Gg][.Ii]{0,1}[Oo]{0,1}[Ii]{0,1}[ ][Aa][Nn][Aa][Rr][Gg][YyIi][Rr][Oo]{0,1}[Ii]/', $the_location)==1){
                              $final_location[]='Ag. Anargyroi';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Oo]{0,1}[UuYy][Nn][Ii][Oo][Nn]{0,1}/', $the_location)==1 || preg_match('/[Ll][Aa][VvBb][Rr][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Lavrio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Gg][Kk]{0,1}[Aa][Dd][Aa][Ss]/', $the_location)==1 || preg_match('/[Ll][Aa][Gg]{1,2}[Kk]{0,1}[Aa][Dd][Ii][Kk][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lagkadas';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Cc]{0,1}[Hh][Aa][Ii][DdTh][Hh]{0,1}[Aa][Rr][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Chaidari';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][YyIi][Rr][Oo][Ss]/', $the_location)==1 || preg_match('/[Hh]{0,1}[Ee][Rr][Mm][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Syros';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Pp][Aa][Rr][Tt][AaIi]/', $the_location)==1){
                              $final_location[]='Sparta';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Aa][Tt][Rr][Aa][Ss]{0,1}/', $the_location)==1 || preg_match('/[Rr][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Patra';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Oo][Rr][Ii][Nn][Tt8][Hh][Oo]{0,1}[Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Korinthos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Oo]{0,1}[UuYy][Tt][Rr][Aa][Kk][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Loutraki';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Ee][Rr][Ii][Ff][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Serifos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][KkCc][Oo][Pp][Ee][Ll][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Skopelos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[IiHh][Ee]{0,1}[Rr][Aa][CcKk][Ll][Ee]{0,1}[Ii]{0,1}[Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Herakleio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Rr][Ii][Ss]{1,2}[Aa]/', $the_location)==1){
                              $final_location[]='Larissa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Vv][Oo][Ll][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Volos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Rr][Hh]{0,1}[Oo][Dd][OoEe][Ss]/', $the_location)==1){
                              $final_location[]='Rhodes';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ii][OoWw][Aa][Nn]{1,2}[Ii][Nn][Aa]/', $the_location)==1){
                              $final_location[]='Ioannina';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Aa][Nn][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Chania';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXxKk][Hh]{0,1}[Aa][Ll][Kk][Ii][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Chalkida';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Gg][Rr][Ii][Nn][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Agrinio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Aa][Tt][Ee][Rr][Ii][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='Katerini';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Rr][Ii][Kk][Aa][Ll][Aa]/', $the_location)==1){
                              $final_location[]='Trikala';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Ee][Rr]{0,1}[AaEe][IiSs]/', $the_location)==1){
                              $final_location[]='Serres';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Ss][Tt][Oo][Rr][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Kastoria';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Mm][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lamia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Ll][Ee][XxKk][Ss]{0,1}[Aa][Nn][Dd][Rr][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Alexandroupoli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Oo]{0,1}[UuYy][Ff][Ll][Ii]/', $the_location)==1){
                              $final_location[]='Soufli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Rr][Ii][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Tripoli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Oo][Zz][Aa][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='Kozani';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcKk][Aa][BbVv][Aa][Ll][Aa]/', $the_location)==1){
                              $final_location[]='Kavala';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Vv][Ee][Rr]{1,2}[Oo]{0,1}[Ii][Aa]/', $the_location)==1){
                              $final_location[]='Veroia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Ii][Ll][Kk][Ii][Ss]/', $the_location)==1){
                              $final_location[]='Kilkis';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Oo][Mm][Oo][Tt][HhIi][Nn][HhIi]/', $the_location)==1){
                              $final_location[]='Komotini';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][XxKk][Ss]{0,1}[Ii][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Polykastro';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Ii][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Chios';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Cc][Oo][Rr][Ff][Uu]/', $the_location)==1 || preg_match('/[Kk][Ee][Rr][Kk][YyIi][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Corfu';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Dd][Rr][Aa][Mm][Aa]/', $the_location)==1){
                              $final_location[]='Drama';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[IiHh][Gg][Oo]{0,1}[UuYy][Mm][Ee][Nn][Ii][Tt][Ss][Aa]/', $the_location)==1){
                              $final_location[]='Igoumenitsa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Oo][Rr][Ee][Ss][Tt][Ee]{0,1}[Ii][Aa][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Orestiada';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ii][Ee][Rr][Aa][Pp][Ee][Tt][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Ierapetra';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Aa][Mm][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Samos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Mm][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lamia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Rr][Ii][Ss]{1,2}[Aa]/', $the_location)==1){
                              $final_location[]='Larissa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Mm][Ee][Ss]{1,2}[Oo][Ll][Oo][Nn]{0,1}[Gg]{1,2}[Hh]{0,1}[Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Messolonghi';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Oo][Ll][Yy][MmBb][PpBb]{0,1}[Ii][Aa]/', $the_location)==1){
                              $final_location[]='Pyrgos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Ee]{0,1}[Ii][Rr][Aa]{0,1}[IiEe][UuAa]{0,1}[Ss]/', $the_location)==1){
                              $final_location[]='Piraeus';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Aa][Ll][Aa][Mm][Ii][SsNn][Aa]{0,1}/', $the_location)==1){
                                $final_location[]='Salamina';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Rr][Ee][Tt8][Hh]{0,1}[YyIi][Mm][Nn][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Rethymno';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[XxKk][Ss]{0,1}[Aa][Nn][Tt8][Hh]{0,1}[IiHh]/', $the_location)==1){
                              $final_location[]='Xanthi';
                              $unspecified_flag=false;
                            }
//                            if(preg_match('/[CcKk][Rr][EeIiHh][Tt][EeIiHhAa]/', $the_location)==1){
//                                $final_location[]='-Unspecified-';
//                                $unspecified_flag=false;
//                            }
                            if($unspecified_flag){
                              $final_location[]='-Unspecified-';
                              error_log('enter unspecified loop---->'.$the_location);
                              
                              /**
                               * Checking to see if the file is available for write.
                               * If it locks, write the unspecified location in a new line.
                               * If not, a warning message is recorded to the log.
                               */
                              if(flock($locations_to_review, LOCK_EX)){
                                fwrite($locations_to_review, 'location: '.$the_location."\n");
                                flock($locations_to_review, LOCK_UN);
                              }
                              else{
                                error_log(date(DATE_RSS)."--WARNING: file 'locations_to_review' unavailable for write!!!");
                              }
                            }
//                                }
//                                else{
//                                    $final_location=str_replace(",", "-", $the_location);
//                                }
                        error_log('normalized location: '.print_r($final_location));
                        wp_set_post_terms($post_id, $final_location, 'github_contr_topothesia');
                    }
                    else{
                        if(true){
                            $post_id=wp_insert_post(['post_type'=>'github_contributor', 'post_title'=>$contributor_username, 'post_status'=>'publish']);
                            update_field('id', $contributor_info->id, $post_id);
                            update_field('contributor_username', $contributor_username, $post_id);
                            update_field('contributor_full_name', $contributor_info->name, $post_id);
                            //if($contributor_info.gravatar_id!="")
                                update_field('contributor_avatar_url', $contributor_info->avatar_url, $post_id);
                            //else
                            //    update_field('contributor_avatar_url', $contributor_info.gravatar_id, $post_id);
                            update_field('contributor_github_url', $contributor_info->html_url, $post_id);
                            update_field('contributor_email', $contributor_info->email, $post_id);
                            update_field('contributor_personal_webpage', $contributor_info->blog, $post_id);
                            update_field('contributions_number', $contributions_number, $post_id);
                            update_field('repos_number', $contributor_info->public_repos, $post_id);
                            update_field('followers_number', $contributor_info->followers, $post_id);
                            wp_set_post_terms($post_id, $contributor_info->company, 'github_contr_eteria');
                            $the_location=$contributor_info->location;
                            $non_comma_location=$contributor_info->location;
                            
                            /**
                             * This if block never executes.
                             */
                            if(false){
                                $f1=str_replace(",", ".", $non_comma_location);
                                $f2=str_replace("-", ".", $f1);
                                $f3=str_replace(" .", ".", $f2);
                                $f4=str_replace(". ", ".", $f3);
                                $f5=str_replace(" . ", ".", $f4);

                                $final_location="$f5";
                                error_log(var_dump($location_normalize_array));
                                if(array_key_exists($f5, $location_normalize_array)){
                                    $final_location=$location_normalize_array[$f5];
                                }
                                $exploded_location=explode(".", $final_location);
                                if($exploded_location && !empty($exploded_location)){
                                    $final_location=$exploded_location[0];
                                }
                            }
                        
                            //if(true){
                            $final_location=array();
                            $unspecified_flag=true;
                            //if(preg_match('/[Gg][Rr][Ee][Ee][Cc][Ee]/', $the_location)==1 || preg_match('/[Hh]{0,1}[Ee][Ll]{1,2}[Aa][SsDd][Aa]{0,1}/', $the_location)==1 || preg_match('/[Gg][Rr]/', $the_location)==1  || preg_match('/[Ee][Ll]/', $the_location)==1){
                                error_log('true');
                            if(preg_match('/[Aa][Tt][Hh][EeIi][Nn][AaSs][Ii]{0,1}/', $the_location)==1){
                              $final_location[]='Athens';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Nn][\.]{0,1}[Ee]{0,1}[Aa]{0,1}[ ]{0,1}[Ss][Mm][YyIi][Rr][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='N. Smyrni';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Gg][Ll][YyIi][Kk][Aa][ ][Nn][Ee][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Gerakas';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Rr][YyIi][Oo][Nn][Ee][Rr][Ii]/', $the_location)==1){
                              $final_location[]='Ag. Stefanos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Gg][Ll][YyIi][Ff][Aa][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Glyfada';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Oo][Ll][Aa][Rr][Gg][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Holargos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[WwOo][Rr][WwOo][Pp][Oo][Ss]/', $the_location)==1){
                                $final_location[]='Oropos';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Hh][Ee][Ss]{1,2}[Aa][Ll]{1,2}[Oo][Nn][Ii][Kk][Ii]/', $the_location)==1 || preg_match('/[Ss][Aa][Ll]{1,2}[Oo][Nn][Ii][Kk][Aa]/', $the_location)==1){
                                $final_location[]='Thessaloniki';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Rr][Tt][Aa]/', $the_location)==1){
                                $final_location[]='Arta';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Aa][Rr][Gg][Aa]/', $the_location)==1){
                                $final_location[]='Kanalaki';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Rr][Ee][VvBb][Ee][Zz][Aa]/', $the_location)==1){
                                $final_location[]='Preveza';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Ee][FfPp][Hh]{0,1}[Aa][Ll][OoIiHh][Nn][Ii][Aa]/', $the_location)==1 || preg_match('/[Aa][Rr][Gg][Oo][Ss][Tt][Oo][Ll][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                                $final_location[]='Argostoli';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Ll]{1,2}[Ii][Tt8][Hh]{0,1}[Ee][Aa]/', $the_location)==1){
                              $final_location[]='Kallithea';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Mm][Aa][Tt][Ee][Rr][Oo][Nn]{0,1}/', $the_location)==1 || preg_match('/[Aa][Gg][.Ii]{0,1}[Oo]{0,1}[Ii]{0,1}[ ][Aa][Nn][Aa][Rr][Gg][YyIi][Rr][Oo]{0,1}[Ii]/', $the_location)==1){
                              $final_location[]='Ag. Anargyroi';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Oo]{0,1}[UuYy][Nn][Ii][Oo][Nn]{0,1}/', $the_location)==1 || preg_match('/[Ll][Aa][VvBb][Rr][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Lavrio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Gg][Kk]{0,1}[Aa][Dd][Aa][Ss]/', $the_location)==1 || preg_match('/[Ll][Aa][Gg]{1,2}[Kk]{0,1}[Aa][Dd][Ii][Kk][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lagkadas';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Cc]{0,1}[Hh][Aa][Ii][DdTh][Hh]{0,1}[Aa][Rr][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Chaidari';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][YyIi][Rr][Oo][Ss]/', $the_location)==1 || preg_match('/[Hh]{0,1}[Ee][Rr][Mm][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Syros';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Pp][Aa][Rr][Tt][AaIi]/', $the_location)==1){
                              $final_location[]='Sparta';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Aa][Tt][Rr][Aa][Ss]{0,1}/', $the_location)==1 || preg_match('/[Rr][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Patra';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Oo][Rr][Ii][Nn][Tt8][Hh][Oo]{0,1}[Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Korinthos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Oo]{0,1}[UuYy][Tt][Rr][Aa][Kk][Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Loutraki';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Ee][Rr][Ii][Ff][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Serifos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][KkCc][Oo][Pp][Ee][Ll][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Skopelos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[IiHh][Ee]{0,1}[Rr][Aa][CcKk][Ll][Ee]{0,1}[Ii]{0,1}[Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Herakleio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Rr][Ii][Ss]{1,2}[Aa]/', $the_location)==1){
                              $final_location[]='Larissa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Vv][Oo][Ll][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Volos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Rr][Hh]{0,1}[Oo][Dd][OoEe][Ss]/', $the_location)==1){
                              $final_location[]='Rhodes';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ii][OoWw][Aa][Nn]{1,2}[Ii][Nn][Aa]/', $the_location)==1){
                              $final_location[]='Ioannina';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Aa][Nn][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Chania';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXxKk][Hh]{0,1}[Aa][Ll][Kk][Ii][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Chalkida';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Gg][Rr][Ii][Nn][Ii][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Agrinio';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Aa][Tt][Ee][Rr][Ii][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='Katerini';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Rr][Ii][Kk][Aa][Ll][Aa]/', $the_location)==1){
                              $final_location[]='Trikala';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Ee][Rr]{0,1}[AaEe][IiSs]/', $the_location)==1){
                              $final_location[]='Serres';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Aa][Ss][Tt][Oo][Rr][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Kastoria';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Mm][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lamia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][Ll][Ee][XxKk][Ss]{0,1}[Aa][Nn][Dd][Rr][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Alexandroupoli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Oo]{0,1}[UuYy][Ff][Ll][Ii]/', $the_location)==1){
                              $final_location[]='Soufli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Tt][Rr][Ii][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Tripoli';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Oo][Zz][Aa][Nn][IiHh]/', $the_location)==1){
                              $final_location[]='Kozani';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcKk][Aa][BbVv][Aa][Ll][Aa]/', $the_location)==1){
                              $final_location[]='Kavala';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Vv][Ee][Rr]{1,2}[Oo]{0,1}[Ii][Aa]/', $the_location)==1){
                              $final_location[]='Veroia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Kk][Ii][Ll][Kk][Ii][Ss]/', $the_location)==1){
                              $final_location[]='Kilkis';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[KkCc][Oo][Mm][Oo][Tt][HhIi][Nn][HhIi]/', $the_location)==1){
                              $final_location[]='Komotini';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Aa][XxKk][Ss]{0,1}[Ii][Oo]{0,1}[UuYy][Pp][Oo][Ll][IiHh][Ss]{0,1}/', $the_location)==1){
                              $final_location[]='Polykastro';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[CcXx][Hh]{0,1}[Ii][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Chios';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Cc][Oo][Rr][Ff][Uu]/', $the_location)==1 || preg_match('/[Kk][Ee][Rr][Kk][YyIi][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Corfu';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Dd][Rr][Aa][Mm][Aa]/', $the_location)==1){
                              $final_location[]='Drama';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[IiHh][Gg][Oo]{0,1}[UuYy][Mm][Ee][Nn][Ii][Tt][Ss][Aa]/', $the_location)==1){
                              $final_location[]='Igoumenitsa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Oo][Rr][Ee][Ss][Tt][Ee]{0,1}[Ii][Aa][SsDd][Aa]{0,1}/', $the_location)==1){
                              $final_location[]='Orestiada';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ii][Ee][Rr][Aa][Pp][Ee][Tt][Rr][Aa]/', $the_location)==1){
                              $final_location[]='Ierapetra';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Aa][Mm][Oo][Ss]/', $the_location)==1){
                              $final_location[]='Samos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Mm][Ii][Aa]/', $the_location)==1){
                              $final_location[]='Lamia';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ll][Aa][Rr][Ii][Ss]{1,2}[Aa]/', $the_location)==1){
                              $final_location[]='Larissa';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Mm][Ee][Ss]{1,2}[Oo][Ll][Oo][Nn]{0,1}[Gg]{1,2}[Hh]{0,1}[Ii][Oo]{0,1}[Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Messolonghi';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Oo][Ll][Yy][MmBb][PpBb]{0,1}[Ii][Aa]/', $the_location)==1){
                              $final_location[]='Pyrgos';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Pp][Ee]{0,1}[Ii][Rr][Aa]{0,1}[IiEe][UuAa]{0,1}[Ss]/', $the_location)==1){
                              $final_location[]='Piraeus';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[Ss][Aa][Ll][Aa][Mm][Ii][SsNn][Aa]{0,1}/', $the_location)==1){
                                $final_location[]='Salamina';
                                $unspecified_flag=false;
                            }
                            if(preg_match('/[Rr][Ee][Tt8][Hh]{0,1}[YyIi][Mm][Nn][Oo][Nn]{0,1}/', $the_location)==1){
                              $final_location[]='Rethymno';
                              $unspecified_flag=false;
                            }
                            if(preg_match('/[XxKk][Ss]{0,1}[Aa][Nn][Tt8][Hh]{0,1}[IiHh]/', $the_location)==1){
                              $final_location[]='Xanthi';
                              $unspecified_flag=false;
                            }
                            if($unspecified_flag){
                              $final_location[]='-Unspecified-';
                              error_log('enter unspecified loop---->'.$the_location);

                              /**
                               * Checking to see if the file is available for write.
                               * If it locks, write the unspecified location in a new line.
                               * If not, a warning message is recorded to the log.
                               */
                              if(flock($locations_to_review, LOCK_EX)){
                                fwrite($locations_to_review, 'location: '.$the_location."\n");
                                flock($locations_to_review, LOCK_UN);
                              }
                              else{
                                error_log(date(DATE_RSS)."--WARNING: file 'locations_to_review' unavailable for write!!!");
                              }
                              
                              /**
                               * Checking to see if the file is available for write.
                               * If it locks, write the unspecified location in a new line.
                               * If not, a warning message is recorded to the log.
                               */
                              if(flock($registered_usernames, LOCK_EX)){
                                fwrite($registered_usernames, $contributor_username."\n");
                                flock($registered_usernames, LOCK_UN);
                              }
                              else{
                                error_log(date(DATE_RSS)."--WARNING: file 'locations_to_review' unavailable for write!!!");
                              }
                            }
//                                }
//                                else{
//                                    $final_location=str_replace(",", "-", $the_location);
//                                }
                        error_log('normalized location: '.print_r($final_location));
                        error_log('username: '.print_r($contributor_username));
                        wp_set_post_terms($post_id, $final_location, 'github_contr_topothesia');                           
                            //} //if(true)
                        }
                    }
                }
            }
            //fclose($contributor_info_file);
        }
        fclose($sum_contr);
        fclose($locations_to_review);
        fclose($registered_usernames);
    }
}
register_activation_hook(__FILE__, 'ellak_github_contributors_synch_posts');
//add_action('init', 'ellak_github_contributors_synch_posts');

/* Add monthly scheduling interval */
//function ellak_add_my_scheduling_intervals($schedules){
//    //error_log('check 3-4');
//    $schedules['ellak_weekly']=array('interval'=>604800, 'display'=>__('Once weekly'));
//    $schedules['ellak_monthly']=array('interval'=>2635200, 'display'=>__('Once monthly'));
//    return $schedules;
//}
//add_filter('cron_schedules', 'ellak_add_my_scheduling_intervals');

//register_activation_hook(__FILE__, 'ellak_activate_contributors_synch');
///* Monthly schedule the synching of files with posts */
//function ellak_activate_contributors_synch(){
//    if(! wp_next_scheduled('ellak_monthly_synch_contributors')){
//        
//        if(!wp_schedule_event(time()+60, '1min', 'ellak_monthly_synch_contributors'))
//                error_log('check 1-2');
//    }
//}
//add_action('ellak_monthly_synch_contributors', 'ellak_github_contributors_synch_posts');
//
//register_deactivation_hook(__FILE__, 'ellak_deactivate_contributors_synch');
//function ellak_deactivate_contributors_synch() {
//	wp_clear_scheduled_hook('ellak_monthly_synch_contributors');
//}
//?>
