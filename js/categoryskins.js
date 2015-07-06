function CategorySkins($) {
	'use strict';

	this.init = function() {
		$('.cs_prefix').on('focus', skins.highlightPrefix);
		$('.cs_suffix').on('focus', skins.highlightSuffix);


		var prefix = $('.cs_prefix input').val();
		var suffix = $('.cs_suffix input').val();

		$('.cs_page_example').html('<span id="skin_prefix">'+prefix+'</span>Example Page Title<span id="skin_suffix">'+suffix+'</span>');
	};

	var skins = {
		highlightPrefix: function() {
			$('#skin_suffix').removeAttr('style');
			$('#skin_prefix').css('font-weight', 'bold');
			$(this).keyup(function() {
				$('#skin_prefix').html($(this).val());
			});
		},

		highlightSuffix: function() {
			$('#skin_prefix').removeAttr('style');
			$('#skin_suffix').css('font-weight', 'bold');
			$(this).keyup(function() {
				$('#skin_suffix').html($(this).val());
			});
		}
	};
}

var CS = new CategorySkins(jQuery);
jQuery(document).ready(CS.init);