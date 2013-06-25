//@require:jquery,backbone,underscore
/**
 *
 * 定义全局函数系列
 *
 * @author Richard Lea <chigix@zoho.com>
 *
 **/

return {
	getDigit: function(number, N) {
		return Math.floor(number / (Math.pow(10, N)) % 10);
	}
}