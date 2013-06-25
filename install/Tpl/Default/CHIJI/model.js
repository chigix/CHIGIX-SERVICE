//@require:jquery,backbone,underscore

/**
 *
 * 千木数据抽象模型类
 *
 * @author Richard Lea <chigix@zoho.com>
 *
 **/

var model = Backbone.Model.extend({
	validate: function(reta) {
		if (reta.status && !(reta.status < 300 && reta.status >= 200)) {
			return reta.info || ("[" + reta.status || 000 + "] ERROR without any error.");
		};
	},
	parse: function(reta, options) {
		return reta.data;
	}
});
return model;