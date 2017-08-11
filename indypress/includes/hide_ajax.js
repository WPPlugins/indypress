jQuery('.hide-comment-ajax').live('click', function() {
	cid = jQuery(this).attr('comment_id');
	jQuery.post(
		args.url,
		{
			action: 'change_comment_status',
			status: 'hidden',
			comment_id: cid,
			nonce: args.comment_nonce
		},
		function( response ) {
			res = jQuery.parseJSON(response);
//      if( res[0] == 0 )
			jQuery('#comment-actions-' + cid).html('<a class="normal-comment-ajax" comment_id="' + cid + '">Normal</a>');
		}
	);
});
jQuery('.normal-comment-ajax').live('click', function() {
	cid = jQuery(this).attr('comment_id');
	jQuery.post(
		args.url,
		{
			action: 'change_comment_status',
			status: 'normal',
			comment_id: cid,
			nonce: args.comment_nonce
		},
		function( response ) {
			res = jQuery.parseJSON(response);
			if( res[0] == 0 )
				jQuery('#comment-actions-' + cid).html('<a class="hide-comment-ajax" comment_id="' + cid + '">Hide</a> | <a class="promoted-comment-ajax" comment_id="' + cid + '">Promote</a>');
		}
	);
});
jQuery('.promoted-comment-ajax').live('click', function() {
	cid = jQuery(this).attr('comment_id');
	jQuery.post(
		args.url,
		{
			action: 'change_comment_status',
			status: 'promoted',
			comment_id: cid,
			nonce: args.comment_nonce
		},
		function( response ) {
			res = jQuery.parseJSON(response);
			if( res[0] == 0 )
				jQuery('#comment-actions-' + cid).html('<a class="normal-comment-ajax" comment_id="' + cid + '">Normal</a>'); 
		}
	);
});


jQuery('.premoderate-post-ajax').live('click', function() {
	pid = jQuery(this).attr('post_id');
	jQuery.post(
		args.url,
		{
			action : 'change_post_status',
			status: 'premoderate',
			post_id : pid,
			nonce : args.post_nonce
		},
		function( response ) {
			res = jQuery.parseJSON( response );
			if( res[0] == -1 )
				alert( 'not authorized' );
			else
				jQuery('#post-hide-' + pid).html('Now in premoderation');
		}
	);
});
jQuery('.normal-post-ajax').live('click', function() {
	pid = jQuery(this).attr('post_id');
	jQuery.post(
		args.url,
		{
			action : 'change_post_status',
			status: 'normal',
			post_id : pid,
			nonce : args.post_nonce
		},
		function( response ) {
			res = jQuery.parseJSON( response );
			if( res[0] == -1 )
				alert( 'not authorized' );
			else
				jQuery('#post-hide-' + pid).html('Now promoted');
		}
	);
});
jQuery('.hide-post-ajax').live('click', function() {
	pid = jQuery(this).attr('post_id');
	jQuery.post(
		args.url,
		{
			action : 'change_post_status',
			status: 'hide',
			post_id : pid,
			nonce : args.post_nonce
		},
		function( response ) {
			res = jQuery.parseJSON( response );
			if( res[0] == -1 )
				alert( 'not authorized' );
			else {
				jQuery('#post-hide-' + pid).html('Now hidden');
			}
		}
	);
});

