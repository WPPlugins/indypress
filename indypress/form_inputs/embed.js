jQuery(document).ready(function($) {
	var embed_dlg_content = $('<div></div>')
		.html(''
		+ '<label for="url">URL del contenuto</label><br/><input name="url" value="" placeholder="https://www.youtube.com/watch?v=WS32iAnPUAo" id="embed-form-url" />'
		)
		.dialog({
			autoOpen: false,
			modal: true,
			width: 'auto',
			title: 'embed file',
			buttons: {
				"Cancel" : function() {
					$(this).dialog('close');
				},
				"Embed": on_embed_submit
			}
		});
	$("#embed-form-url").keypress(function(e) {
			if(e.keyCode == 13) {
				on_embed_submit();
				e.preventDefault();
			}
	});
	function on_embed_submit() {
		var editor = tinymce.editors[embed_params.editor];
		var url = $('#embed-form-url').fieldValue()[0];
		html = '<img title="' + url + '" src="' + embed_params.imageurl + '" data-indy-embed="' + url + '" />';
		editor.setContent(editor.getContent() + html);
		embed_dlg_content.dialog('close');
		return false;
	}
	$('#embed-button').button({icons: {primary: 'ui-icon-video'}})
	$('#embed-button').click(function() {
		embed_dlg_content.dialog('open');
		return false;
	});
});
