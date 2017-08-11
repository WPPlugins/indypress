jQuery(document).ready(function($) {
	var upload_dlg_content = $('<div></div>')
		.html(''
		+ '<form action="' + params.ajaxurl + '" method="post" id="upload-form">'
		+ '<fieldset style="border:none;">'
		+ '<label for="indypress_upload_file">Seleziona file</label><br/><input type="file" name="indypress_upload_file" id="upload-form-file" /><br/>'
		+ '<label for="alt">Didascalia</label><br/><input name="alt" value="" placeholder="Una didascalia (opzionale)" id="upload-form-alt" /><br/>'
		+ '<input type="hidden" name="type" value="image" />'
		+ '<input type="hidden" name="action" value="indypressupload" />'
		+ '</fieldset>'
		+ '</form>'
		)
		.dialog({
			autoOpen: false,
			modal: true,
			width: 'auto',
			title: 'Upload file',
			buttons: {
				"Cancel" : function() {
					$(this).dialog('close');
				},
				"Upload": function() {
					$('#upload-form').ajaxSubmit(on_upload_submit);
				}
			}
		});
	function on_upload_submit(responseText, statusText, xhr, form)  { 
		/*It will be automatically parsed, thanks to the json content-type in php */
		responseobj = $.parseJSON(responseText);
		editor = tinymce.editors[params.editor];
		 if(responseobj.type == 'image') {
			 var url = responseobj.url;
			 var alt = $('#upload-form-alt').fieldValue()[0];
			 var html = '<a href="' + responseobj.url + '" _mce_href="' + responseobj.url +
			 '"><img src="' + responseobj.url +
			 '" class="size-medium" style="max-width:300px;"' +
			 'alt="' + alt + '" title="' + alt + '" ' +
			 '/></a>';
		 }
		 else {
			 var html = '';
			 $('<div></div>').html('Error when uploading: unknown type for ' + responseobj.url)
				 .dialog({
				 dialogClass: 'alert',
				 modal: true,
				 width: 'auto',
				 title: 'Error: unsupported type'
			 });
		 }
		editor.setContent(editor.getContent() + html);
		$('#post_attachments').removeClass('invisible');
		$('#post_attachments > ul').append('<li>' + alt + ': ' + responseobj.url.replace(/.*\//, '') + '</li>');
		return false;
	}
	$('#upload-form').ajaxForm(on_upload_submit);
	$('#upload-form').ajaxSend(function() {
		upload_dlg_content.dialog('close');
		$('#upload-button').button('option', 'label', 'uploading...').button('disable');
	});
	$('#upload-form').ajaxComplete(function() {
		$('#upload-button').button('option', 'label', 'Upload<br/>images').button('enable');
	});
	$('#upload-form').ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
		$('<div></div>').html('Error uploading: ' + thrownError);
	});

	$('#upload-button').button({icons: {primary: 'ui-icon-image'} });
	$('#upload-button').click(function() {
		upload_dlg_content.dialog('open');
		return false;
	});
});
