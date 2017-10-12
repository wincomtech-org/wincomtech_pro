/*
 * list左侧分类控制
*/
$(document).delegate('.offter_tit_item h4','click',function(){
	var self=$(this);
	
	if(self.children('span').hasClass('arr')){
		self.children('span').removeClass('arr').addClass('arr1');
	}else{
		self.children('span').removeClass('arr1').addClass('arr');
	}
	self.siblings('ol').toggle();
});

$('.offter_tit_item a').css({'color':'black','font-size':'14px'});
$('.offter_tit_subItem .active').parent().show();
$('.offter_tit_item a.activea').css('color','red');
$('.offter_tit_item .ff').find('ol').show();
$('.offter_tit_item a.activea').parents('.offter_tit_item').find('ol').show();

$('.temp_list a.active').css('color','red');

//
$('.show_pro_item .img').hover(function(){
	$(this).children('.icon').show();
},function(){
	$(this).children('.icon').hide();
});
/*
 详情页购物
*/
$(".tech .left1").click(function(){  
	 
	$(this).addClass('red').siblings().removeClass('red'); 
	var price=$(this).find('.prices').val(); 
	 
	$(this).parents('.main').find('.price span').html(price);
	
});





