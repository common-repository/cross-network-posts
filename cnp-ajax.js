jQuery(function(){
	var $ = jQuery;
	$('.cnp-type').on('click',function(){
		if($('.cnp-type:checked').val() == 'post'){
			$('.cnp-categories').hide();
			$('.cnp-posts').show();
			$('.cnp-error').show();
		}else if($('.cnp-type:checked').val() == 'category'){
			$('.cnp-categories').show();
			$('.cnp-posts').hide();
			$('.cnp-error').show();
		}
	});
	
	$('.cnp-use-schortcode').on('click', function(){
		var content = $('.cnp-shortcode').text();
		console.log(content);
		if($('textarea.wp-editor-area').css('display') == 'none'){ 
			tinyMCE.execCommand('mceInsertContent',false,'\r\n' + content); 
		} else {  
			$('textarea.wp-editor-area').append('\r\n' + content); 
		}
		window.parent.tb_remove()
	});

	$('.cnp-build-schortcode').on('click', function(){
		$('.cnp-shortcode').text('Loading, please wait...');
		var _type = $('.cnp-type:checked').val();
		var _blogid = $('.cnp-blogid:checked').val();
		var _postid = $('.cnp-postid-'+_blogid+' option:selected').val();
		var _catid = $('.cnp-categoryid-'+_blogid+' option:selected').val();
		var _excerpt = $('#cnp-excerpt:checked').val();
		var _titlelink = $('#titlelink:checked').val();
		var _numberofposts = $('#numberofposts').val();
		if(_numberofposts == ""){
			_numberofposts = null;
		}
		var _header = $('#header').val();
		if(_header == ""){
			_header = null;
		}
		
		$.ajax({
			response: 'ajax-response',
			type: "POST",
			url: ajaxurl,
			data: {action: 'getshortcode', type: _type, blogid: _blogid, postid: _postid, catid: _catid, excerpt: _excerpt, titlelink: _titlelink, numberofposts: _numberofposts, header: _header }
		}).done(function(r){
			var res = wpAjax.parseAjaxResponse(r,this.response);
			var shortcode = '';
			$.each(res.responses, function() {
				if(this.what == 'cnp_get_shortcode'){
					shortcode += this.data;
				}
				if(this.errors){
					shortcode = "Something went wrong";
				}
			});
			$('.cnp-shortcode').text(shortcode);
		});
	});

});