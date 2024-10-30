<?php
/*
Plugin Name: Cross-network posts
Plugin URI: http://wordpress.org/extend/plugins/cross-network-posts/
Description: Embed a post or a category of posts that is within another website on the same Wordpress network (multisite). Use the shortcodebuilder above the editor or manually as shortcode: [cnp blogid=1 postid=1]. More info on attributes on the plugin page.
Version: 2.0
Author: DanielTulp
License: GPLv2 or later
*/
if ( ! is_multisite() )
	wp_die( __( 'Multisite support is not enabled.' ) );

//http://wakeusup.com/2011/11/how-to-create-plugin-options-page-in-wordpress/	
if(!class_exists('cnp_plugin_options')):
	// DEFINE PLUGIN ID
	define('CNPPLUGINOPTIONS_ID', 'cnppluginoptions');
	// DEFINE PLUGIN NICK
	define('CNPPLUGINOPTIONS_NICK', 'CNP options');

	class cnp_plugin_options
    {
		/** function/method
		* Usage: hooking the plugin options/settings
		* Arg(0): null
		* Return: void
		*/
		public static function register()
		{
			register_setting(CNPPLUGINOPTIONS_ID.'_options', 'cnp_builder');
			register_setting(CNPPLUGINOPTIONS_ID.'_options', 'cnp_author_restriction');
			register_setting(CNPPLUGINOPTIONS_ID.'_options', 'cnp_plugin_link');
			register_setting(CNPPLUGINOPTIONS_ID.'_options', 'cnp_notallowed');
		}
		/** function/method
		* Usage: hooking (registering) the plugin menu
		* Arg(0): null
		* Return: void
		*/
		public static function menu()
		{
			// Create menu tab
			add_options_page(CNPPLUGINOPTIONS_NICK.' Plugin Options', CNPPLUGINOPTIONS_NICK, 'manage_options', CNPPLUGINOPTIONS_ID.'_options', array('cnp_plugin_options', 'options_page'));
		}
		/** function/method
		* Usage: show options/settings form page
		* Arg(0): null
		* Return: void
		*/
		public static function options_page()
		{
			if (!current_user_can('manage_options'))
			{
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}

			$plugin_id = CNPPLUGINOPTIONS_ID;
			// display options page
			include(plugin_dir_path(__FILE__).'/options.php');
		}
		/** function/method
		* Usage: filtering the content
		* Arg(1): string
		* Return: string
		*/
		public static function cnp_show_builder()
		{
			//negative question on options page, so opposite result of bool
			if(get_option('cnp_builder')){
				return false;
			}else{
				return true;
			}
		}
		public static function cnp_author_restriction(){
			if(get_option('cnp_author_restriction')){
				return true;
			}else{
				return false;
			}
		}
		public static function cnp_plugin_link(){
			if(get_option('cnp_plugin_link')){
				return true;
			}else{
				return false;
			}
		}
		public static function cnp_notallowed(){
			if(get_option('cnp_notallowed')){
				return true;
			}else{
				return false;
			}
		}
    }
	if(is_admin()){
        add_action('admin_init', array('cnp_plugin_options', 'register'));
        add_action('admin_menu', array('cnp_plugin_options', 'menu'));
	}
endif;
	
include(plugin_dir_path(__FILE__).'/cnp-includes.php');

//enqueue javascript
add_action( 'admin_enqueue_scripts', 'cnp_enqueue' );

/*function localize_vars() { 
	return array( 
		'SiteUrl' => get_bloginfo('url'), 
		'MyAjax' => plugin_dir_url( __FILE__ ) . 'cnp_ajax_processor.php'
	);
}*/
function cnp_enqueue($hook) {
	wp_enqueue_script('cnp_ajax', plugins_url('cnp-ajax.js', __FILE__), array('jquery'), '1.0.0');
	wp_localize_script( 'cnp_ajax', 'cnp_build_shortcut','');
}

//add ajax call back
add_action('wp_ajax_getshortcode', 'ajax_get_shortcode');

function ajax_get_shortcode(){
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
	
	$response = array(
		'what' => 'cnp_get_shortcode',
		'data' => cnp_get_shortcode()
	);
	
	$xmlResponse = new WP_Ajax_Response($response);
	$xmlResponse->send();
}

//do something with the shortcode
function GetPostFromBlog($atts, $content) {
	//to make sure the content is placed correctly in the post
	ob_start();
	
	//determing current blog
	global $blog_id;
	//$current_blog_id = $blog_id; don't need it anymore?
	
	//extract attributes from shortcode
	extract(shortcode_atts( array('blogid' => '', 'postid' => '','catid'=>'','header' => '','excerpt'=>'','numberofposts' => '','titlelink'=>''), $atts));
	
	//print_r($atts);
	
	//defaul header is h2
	if($header == null || $header == ''){
		$header=2;
	}
	
	//create the user id value outside of the scope of the sub switched content
	if(cnp_plugin_options::cnp_author_restriction()){
		$current_author_id = get_current_user_id();
	}
	
	
	//set some parameters before we switch
	$author_restriction = cnp_plugin_options::cnp_author_restriction();
	
	//check for not being the current blog and then switch to fetch content
	if(!ms_is_switched()){
		if( function_exists('switch_to_blog')){ 
			switch_to_blog($blogid);
		}else{
			echo 'switch_to_blog does not exist';
		}
	}
	
	//set the main author before we are in the loop
	$main_author = get_the_author();
	$main_author_level = get_the_author_meta('user_level');
	
	//does the other blog allow me to get posts?
	$cnp_notallowed = cnp_plugin_options::cnp_notallowed();
		
	//if to display a post
	if($catid == null || $catid == ""){
		$args = array (
			'p' => $postid,
			'posts_per_page' => 1
		); 
	}else{
	//if to display a category
		//default value of number of posts is 5
		if($numberofposts == null || $numberofposts == ""){
			$numberofposts = 5;
		}
		$args = array (
			'cat' => $catid,
			'posts_per_page' => $numberofposts
		);
	}	
		
	//do query to DB
	$query = new WP_Query($args);
	
	//start the loop
	while ($query->have_posts()) : $query->the_post();
	//if nog met check op bolean
		if($cnp_notallowed):?>
			<p>The administrator of the website this page is trying to embed a post from does not allow this.</p>
		<?php 
		else:
			if(!$author_restriction || $main_author == get_the_author() || $main_author_level >= 8 ):
				//do not display the header if 0
				if($header != 0):?>
					<h<?php echo $header;?>>
						<?php
						//only create link on title if not set as false
						if(!$titlelink):?>
						<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
						<?php endif;
							the_title(); 
						if(!$titlelink):?>	
						</a>
						<?php endif;?>
					</h<?php echo $header;?>>
				<?php
				endif;
				//if excerpt is true, show excerpt not full post
				if($excerpt):?>
				 	<div class="entry">
					   <?php the_excerpt(); ?>
					</div>
				<?php
				else:
				//else show full post
				?>
					<div class="entry">
					   <?php the_content(); ?>
					</div>
				<?php
				endif;
			elseif($author_restriction && $main_author != get_the_author()):?>
				<p>CNP is restricted to only posts where the author is the same as the author of the original author (or administrators). You can change this in the dashboard: Settings -> CNP options.</p>
			<?php 
			endif;
		endif;
		wp_reset_postdata();
	endwhile;
	
	if(cnp_plugin_options::cnp_plugin_link()):?>
		<!--<a href='http://wordpress.org/extend/plugins/cross-network-posts/' title='Thanks to CNP: Cross Network Posts plugin for Wordpress'>Thanks to CNP: Cross Network Posts plugin for Wordpress</a>-->
	<?php
	endif;
	
	//gather all contents that is created in this ob
	$output_string=ob_get_contents();
	//end ob
	ob_end_clean();
	
	//return output
	return $output_string;
	
	//switch back to current blog
	if(ms_is_switched()){
		if( function_exists('restore_current_blog')){ restore_current_blog();}
	}
}
//add shortcode and link to function
add_shortcode('cnp', 'GetPostFromBlog');

//credits for this piece of code go to http://themergency.com/adding-custom-buttons-to-the-wordpress-content-editor-part-1/
//add a button to the content editor, next to the media button
if(cnp_plugin_options::cnp_show_builder()){
	//this button will show a popup that contains inline content
	add_action('media_buttons_context', 'add_my_custom_button');
	//add some content to the bottom of the page 
	//This will be shown in the inline modal
	add_action('admin_footer', 'add_cnp_inline_popup_content');	
}

//action to add a custom button to the content editor
function add_my_custom_button($context) {
  
  //path to my icon
  $img = plugins_url( 'cnp-icon.png' , __FILE__ );
  
  //the id of the container I want to show in the popup
  $container_id = 'cnp_popup_container';
  
  //our popup's title
  $title = 'Create a Cross-Network posts shortcode';

  //create hyperlink
  $context .= "<a class='button thickbox' title='{$title}'
    href='#TB_inline?width=640&height=800&inlineId={$container_id}'><span class='cnp-icon'></span>
    CNP</a>";
  
  return $context;
}

//add inline popup
function add_cnp_inline_popup_content() {
	//instantiate class
	$cnp_includes = new CNP_Includes();
	
	//add some basic styling
	?>
	<style>
		fieldset{
			width:49%;
			float:left;
		}
		fieldset#result{
			width: 100%;
		}
		.cnp-icon{
			background: url('<?php echo plugins_url( 'cnp-icon.png' , __FILE__ );?>') no-repeat;
			width:21px;
			height: 16px;
			display: block;
			float: left;
			margin: 3px 4px 0 -3px;
		}
		.cnp-content-field select{
			width: 200px;
		}
		.cnp-content-field label{
			font-weight: bold;
		}
		.cnp-error{
			margin-left:13px;
			color:#BC3636;
		}
	</style>
	<div id="cnp_popup_container" style="display:none;">
	  <form method="post" action="">
	  	<fieldset name="content" title="Content" class="cnp-content-field">
			<h4>Choose type of content</h4>
			<input type="radio" name="cnp-type" value="post" class="cnp-type" id="cnp-type-post" /><label for="cnp-type-post">Single post</label><br />
			<input type="radio" name="cnp-type" value="category" class="cnp-type" id="cnp-type-category"/><label for="cnp-type-category">Posts from category</label>
			<h4>Select the website and content</h4>
			<?php
			$blogs = $cnp_includes->cnp_get_blogs();
			foreach($blogs as $blog):
				$categories = $cnp_includes->cnp_get_content($blog->blog_id,'cat');
				$posts = $cnp_includes->cnp_get_content($blog->blog_id,'post');
				?>
				<input type="radio" name="cnp-blogid" value="<?php echo $blog->blog_id;?>" class="cnp-blogid" id="cnp-type-blogid-<?php echo $blog->blog_id;?>" /><label for="cnp-type-blogid-<?php echo $blog->blog_id;?>"><?php echo $blog->path;?></label><br />
				<?php if(is_array($posts) && count($posts)>0):?>
					<select style="display:none;" name="cnp-categoryid" class="cnp-categories cnp-categoryid-<?php echo $blog->blog_id;?>">
					<?php
					foreach($categories as $category):?>
						<option value="<?php echo $category->cat_ID;?>"/><?php echo $category->name;?></option>
						<?php
					endforeach;?>
					</select>
					<select style="display:none;" name="cnp-postid" class="cnp-posts cnp-postid-<?php echo $blog->blog_id;?>">
					<?php
					foreach($posts as $post):
					?>
						<option value="<?php echo $post->ID;?>"/><?php echo $post->post_title;?> - <?php echo get_userdata($post->post_author)->display_name;?></option>
						<?php
					endforeach;?>
					</select>
				<?php 
				elseif($posts == "Not allowed"):
					echo "<span class='cnp-error'>Embedding not allowed</span>";
				else:
					echo "<span class='cnp-error' style='display:none;'>No posts or none by you</span>";
				endif;?>
				<br />
				<?php
			endforeach;
		?>
		</fieldset>
		<fieldset name="attributes" title="Optional attributes" class="cnp-attribute-field">
			<h4>Optional attributes</h4>
			<input type="checkbox" name="excerpt" id="cnp-excerpt" value="false" /><label for="cnp-excerpt">Only show excerpts (default is full post)</label><br />
			<label for="header">Header numer (default: 2)</label><input type="text" maxlength="1" name="header" id="header" /><br />
			<label for="numberofposts">Number of posts (default: 5)</label><input type="text" name="numberofposts" id="numberofposts" /><br />
			<label for="titlelink">Do not link post title (default: has a link)</label><input type="checkbox" maxlength="1" name="numberofposts" id="titlelink" value="false" />
		</fieldset>
		
		<fieldset name="result" id="result">
			<input type="button" value="Build shortcode" class="cnp-build-schortcode"/>
			<h3>The shortcode is:</h3>
			<div class="cnp-shortcode"></div>
			<input type="button" value="Place shortcode" class="cnp-use-schortcode"/>
		</fieldset>
	  </form>
	</div>
<?php
}
?>