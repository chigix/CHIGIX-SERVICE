define("PublicView_Header",["jquery"],function($){
$('#PublicView_Header .nav > li[data-action=' + PublicView_Header.pageName + ']').addClass('active');

});
requirejs(["PublicView_Header"]);

