<?php

if(!class_exists('cnp_includes')){
	class CNP_Includes{
		//set constructor and execute enqueing
		function __construct(){
			add_action('wp_ajax_cnp_getshortcode', 'cnp_get_shortcode');
		}
		
		function cnp_get_shortcode(){
			if(isset($_POST['type'])){
				$type = $_POST['type'];
			}else{
				die("No type");
			}
			
			if(isset($_POST['blogid'])){
				$blogid = $_POST['blogid'];
			}else{
				die("No blog ID");
			}


			$shortcode = "[cnp blogid=".$blogid;
			if($type=='post'){
				if(isset($_POST['postid'])){
					$post_ID = $_POST['postid'];
					$shortcode .= ", postid=".$post_ID;
				}else{
					die("Content empty");
				}
			}
			if($type=='category'){
				if(isset($_POST['catid'])){
					$cat_ID = $_POST['catid'];
					$shortcode .= ", catid=".$cat_ID;	
				}else{
					die("Content empty");
				}
			}

			if(isset($_POST['excerpt'])){
				$shortcode .= ", excerpt=".$_POST['excerpt'];
			}

			if(isset($_POST['header']) && $_POST['header'] != ""){
				$shortcode .= ", header=".$_POST['header'];
			}

			if(isset($_POST['numberofposts']) && $_POST['numberofposts'] != ""){
				$shortcode .= ", numberofposts=".$_POST['numberofposts'];
			}

			if(isset($_POST['titlelink'])){
				$shortcode .= ", titlelink=".$_POST['titlelink'];
			}

			$shortcode .= "]";
			return $shortcode;
		}
		
		//get all available blogs except current
		function cnp_get_blogs(){
			//set wp daabase object
			global $wpdb;
			$blogId = get_current_blog_id();
			//create sql string, get blog id and path from __blogs where it is not the current blog, it is part of the current network, it is not marked as spam, deleted or archived
			$sql = $wpdb->prepare(
				"SELECT blog_id,path FROM {$wpdb->blogs} 
				WHERE blog_id != %d 
				AND site_id = %s 
				AND spam = '0' 
				AND deleted = '0' 
				AND archived = '0' 
				order by blog_id", $blogId, $wpdb->siteid);
			
			//get results as array
			$result = $wpdb->get_results( $sql); 
			return $result;
		}
		
		//get content (post or posts in category)
		function cnp_get_content($blogid, $type){
			//set some parameters before switching
			$author_restriction = cnp_plugin_options::cnp_author_restriction();
						
			//switch to target blog/website
			if(!ms_is_switched()){
				if( function_exists('switch_to_blog')){ 
					switch_to_blog($blogid);
				}else{
					echo 'switch_to_blog does not exist';
				}
			}
			
			//does the other blog allow me to get posts?
			$cnp_notallowed = cnp_plugin_options::cnp_notallowed();
			
			//set arguments for query if type is category
			if($type == 'cat'){
				$args = array(				
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'category',
				'pad_counts'               => false );
				
				$result = get_categories( $args );
			}
			//set arguments for query if type is post
			if($type == 'post'){
				$args = array(
				'posts_per_page'  => -1,
				'category'        => '',
				'orderby'         => 'post_date',
				'order'           => 'DESC',
				'include'         => '',
				'exclude'         => '',
				'meta_key'        => '',
				'meta_value'      => '',
				'post_type'       => 'post',
				'post_mime_type'  => '',
				'post_parent'     => '',
				'post_status'     => 'publish',
				'suppress_filters' => true);
				
				//todo: prevent author check if enabled for posts
				
				if($author_restriction && !current_user_can('manage_options')){
					$args = array_merge($args,array('author' => get_current_user_id()));
				}
				$result = get_posts($args);
			}
			//switch back
			if(ms_is_switched()){
				if( function_exists('restore_current_blog')){ restore_current_blog();}
			}else{
				$result .= 'restore_current_blog does not exist';
			}
			if($cnp_notallowed){
				$result = "";
				$result = "Not allowed";
			}
			
			return $result;	
		}
	}
}

?>