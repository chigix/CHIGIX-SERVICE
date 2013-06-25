//@require:jquery,bootstrap,backbone,underscore,chigiThis('CHIJI/jqueryDefaultValue'),CGA
var view = Backbone.View.extend({
	el: '#chigiThis',
	events: {
		'click .input-append-dropdown-item': 'refreshDropdown',
		'click #normalForm_submit': 'submit',
		'click #normalForm_reset': 'reset',
	},
	initialize: function() {
		this.reset();
	},
	reset: function() {
		$("#CHIJI_RC_DIR").DefaultValue(CGA.chigiThis.default_RC_DIR);
		$("#CHIJI_RC_URL").DefaultValue(CGA.chigiThis.default_RC_URL);
		$("#ching_domain").DefaultValue('null');
		$("#ching_pool").DefaultValue(CGA.chigiThis.default_ching_pool);
		$("#ching_expire").DefaultValue('900');
		$("#domain").DefaultValue(CGA.chigiThis.default_domain);
		$("#default_theme").DefaultValue('Default');
		$("#success_redirect").DefaultValue('Index/index');
		$("#error_redirect").DefaultValue('Index/index');
	},
	refreshDropdown: function(event) {
		var currentTarget = $(event.currentTarget);
		currentTarget.parent().parent().prev().html(currentTarget.text() + ' <span class="caret"></span>');
	},
	submit: function(e) {
		e.preventDefault();
		$.post(CGA.chigiThis.redirect_link, this.$el.serialize(), $.proxy(function(data) {
			this.$el.parent().html('<h1>安装成功</h1>');
		}, this));
	}
});

return new view();