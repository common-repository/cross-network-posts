<div class="wrap">

    <?php screen_icon(); ?>

	<form action="options.php" method="post" id="<?php echo $plugin_id; ?>_options_form" name="<?php echo $plugin_id; ?>_options_form">

	<?php settings_fields($plugin_id.'_options'); ?>

    <h2>Cross network posts Settings</h2>
    
		   
                 <label for="cnp_builder">
                     <p>Do <strong>NOT</strong> show the shortcode builder above the content editor in this website?
					 <input type="checkbox" id="cnp_builder" name="cnp_builder" <?php if(get_option('cnp_builder')){echo 'checked';} ?> /></p>
                 </label>
             
                 <label for="cnp_author_restriction">
                     <p>Restrict embedding content to only the original author or administrator?
					 <input type="checkbox" id="cnp_author_restriction" name="cnp_author_restriction" <?php if(get_option('cnp_author_restriction')){echo 'checked';} ?> /></p>
                 </label>
				 <label for="cnp_notallowed">
                     <p>Do <strong>NOT</strong> allow other websites in this network to embed posts from this website?
					 <input type="checkbox" id="cnp_notallowed" name="cnp_notallowed" <?php if(get_option('cnp_notallowed')){echo 'checked';} ?> /></p>
                 </label>
				 
                 <label for="cnp_plugin_link">
                     <p>Add a link to this awesome plugin to your HTML code?
					 <input type="checkbox" id="cnp_plugin_link" name="cnp_plugin_link" <?php if(get_option('cnp_plugin_link')){echo 'checked';} ?> /></p>
                 </label>
				 
			<p class="submit">
            	<input type="submit" name="submit" value="Save changes" class="button button-primary" />
			</p>	

	</form>

</div>
