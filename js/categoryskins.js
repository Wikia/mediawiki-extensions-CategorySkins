function CategorySkins($) {
	'use strict';

	this.init = function() {
		var prefixField = $('.cs_prefix input'),
		    suffixField = $('.cs_suffix input');

		$('.cs_page_example').html('<span id="skin_prefix"></span>Example Page Title<span id="skin_suffix"></span>');

		// load data fields so events know which preview too update
		prefixField.data('preview', $('.cs_page_example #skin_prefix'));
		suffixField.data('preview', $('.cs_page_example #skin_suffix'));

		// add event listeners
		prefixField.add(suffixField)
			.on('focus',    titlePreview.highlight)
			.on('focusout', titlePreview.unhighlight)
			.on('keyup',    titlePreview.update);

		// do initial update from initial values
		titlePreview.update.call(prefixField);
		titlePreview.update.call(suffixField);
	};

	var titlePreview = {
		highlight: function() {
			$(this).data('preview').css('font-weight', 'bold');
		},

		unhighlight: function() {
			$(this).data('preview').removeAttr('style');
		},

		update: function() {
			$(this).data('preview').text($(this).val());
		},
	};
}

var CS = new CategorySkins(jQuery);
jQuery(document).ready(CS.init);
