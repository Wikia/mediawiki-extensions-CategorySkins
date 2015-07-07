function CategorySkins($) {
	'use strict';

	this.init = function() {
		$('.cs_prefix').on('focus', skins.highlightPrefix);
		$('.cs_suffix').on('focus', skins.highlightSuffix);
		$('.cs_prefix').on('focusout', skins.removePrefixBold);
		$('.cs_suffix').on('focusout', skins.removeSuffixBold);
		$('.cs_prefix input').on('keyup', skins.updatePrefix);
		$('.cs_suffix input').on('keyup', skins.updateSuffix);


		var prefix = $('.cs_prefix input').val();
		var suffix = $('.cs_suffix input').val();

		$('.cs_page_example').html('<span id="skin_prefix">'+prefix+'</span>Example Page Title<span id="skin_suffix">'+suffix+'</span>');
	};

	var skins = {
		highlightPrefix: function() {
			$('#skin_prefix').css('font-weight', 'bold');

		},

		highlightSuffix: function() {
			$('#skin_suffix').css('font-weight', 'bold');
		},

		updatePrefix: function() {
			$(this).keyup(function() {
				$('#skin_prefix').html($(this).val());
			});
		},

		updateSuffix: function() {
			$(this).keyup(function() {
				$('#skin_suffix').html($(this).val());
			});
		},

		removePrefixBold: function() {
			$('#skin_prefix').removeAttr('style');
		},

		removeSuffixBold: function() {
			$('#skin_suffix').removeAttr('style');
		}
	};
}

var CS = new CategorySkins(jQuery);
jQuery(document).ready(CS.init);