// JavaScript Document
$(function() {

	/*-----------------------------------------------------loading-----------------------------------------------------*/
	 NProgress.start();
    setTimeout(function() { NProgress.done(); $('.fade').removeClass('out'); }, 5);

	/*-----------------------------------------------------平滑滚动-----------------------------------------------------*/
	/*$.srSmoothscroll({
		step: 100,
   		speed: 1200,
   		ease: 'easeOutExpo',
       	target: $('body'),
      	container: $('smoothscroll')
        });*/

	/*-----------------------------------------------------header-----------------------------------------------------*/
	
	
	
	$(window).scroll(function() {
		if($(window).scrollTop() > 40 ) {
			$(".header").addClass('fixed-style');
		} else {
			$(".header").removeClass('fixed-style');
		}
		var windowScrollTop = $(window).scrollTop();
		
	});


	/*-----------------------------------------------------nav-----------------------------------------------------*/	
	
	$(".nav li.nav-sub:first").css("margin-left", "0");
	$(".nav-down-menu").each(function(){
    	$(this).find("div ul li:last").css("background", "none");
 	});
	
	$(".navigation-inner ul li").hover(function() {
		
		$(this).find("div.nav-line").stop().animate({width : 100 + '%'}, 300);
	}, function() {
		$(this).find("div.nav-line").stop().animate({
				width: 0,
				left: 0
			}, 200);
	});
	
	$(".navigation-down-inner ul li").hover(function() {
		$(this).find("div.nav-line-d").stop().animate({width : 100 + '%'}, 300);
	}, function() {
		$(this).find("div.nav-line-d").stop().animate({
				width: 0,
				left: 0
			}, 200);
	});

	var qcloud = {};
	$('[_t_nav]').hover(function() {
		var _nav = $(this).attr('_t_nav');
		clearTimeout(qcloud[_nav + '_timer']);
		qcloud[_nav + '_timer'] = setTimeout(function() {
			$('[_t_nav]').each(function() {
				$(this)[_nav == $(this).attr('_t_nav') ? 'addClass' : 'removeClass']('nav-up-selected');
			});
			$('#' + _nav).stop(true, true).slideDown(300);
		}, 150);

	}, function() {
		var _nav = $(this).attr('_t_nav');
		clearTimeout(qcloud[_nav + '_timer']);
		qcloud[_nav + '_timer'] = setTimeout(function() {
			$('[_t_nav]').removeClass('nav-up-selected');
			$('#' + _nav).stop(true, true).slideUp(100);
		}, 150);

	});
	
	/*-----------------------------------------------------banner-----------------------------------------------------*/
	$(".animation").each(function(){
        var i=0;
        var timer=0;
        var prev=$(this).find(".banner-btn a.prev");
        var next=$(this).find(".banner-btn a.next");
        var pageI=$(this).find("ol li");
        var imgLi=$(this).find("ul li");



        function right() {
            i++;
            if (i == imgLi.length) {
                i = 0
            }
          

        }
        function left() {
            i--;
            if (i < 0) {
                i = imgLi.length - 1
            }
        }
        function run(){
            pageI.eq(i).addClass("active").siblings().removeClass("active");
            imgLi.eq(i).fadeIn(1000).siblings().fadeOut(1000).hide();
        }
        pageI.each(function(index){
            $(this).click(function(){
                i=index;
                run();
            });
        }).eq(0).trigger("click");
        function runn(){
            right();
            run();
        }
		
     timer= setInterval(runn, 6000);
        $(".animation").hover(function(){
            clearInterval(timer);
            $(".banner-btn a").fadeIn(1000);
        },function(){
            timer = setInterval(runn, 6000);
            $(".banner-btn a").fadeOut(1000);
        });
		
		 /* 
		$(".animation").hover(function(){
            $(".banner-btn a").fadeIn(600);
        },function(){
            $(".banner-btn a").fadeOut(600);
        });*/
		
        prev.click(function(){
            left();
            run();
        });
        next.click(function(){
            right();
            run();
        });
    })
	
	
	$('#scroll-down').click(function(){
 		$('body').animate({scrollTop: $('.info').offset().top-80}, 1000);
	});
		

	
	/*-----------------------------------------------------CanvasParticles-----------------------------------------------------*/
	/*window.onload = function(){
			var config = {
				vx: 4,
				vy: 4,
				height: 2,
				width: 2,
				count:100,
				color: "220, 220, 220",
				stroke: "220, 220, 220",
				dist: 6000,
				e_dist: 20000,
				max_conn:5
			}
			CanvasParticle(config);
	}*/
	
	
	/*-----------------------------------------------------notice-----------------------------------------------------*/
	/*
	 * $(".txtScroll-top").slide({mainCell:".bd ul",autoPage:true,effect:"top",autoPlay:true,vis:1,delayTime:1000});
	*/
	
	/*-----------------------------------------------------product-----------------------------------------------------*/
	$(".pul li").hover(function(){
    		$(this).find("p.desc").stop().animate({opacity:"0",marginTop:"90px"},300,"swing");
			$(this).find("div.btn-g1").stop().animate({marginTop:"-150px",opacity:"1"},300,"swing");
			$(this).find("div.btn-g2").stop().animate({marginTop:"-156px",opacity:"1"},300,"swing");
			$(this).find("div.btn-g3").stop().animate({marginTop:"-156px",opacity:"1"},300,"swing");
			$(this).find("div.btn-g4").stop().animate({marginTop:"-156px",opacity:"1"},300,"swing");
    },function(){
    		$(this).find("p.desc").stop().animate({marginTop:"0",opacity:"1"},300,"swing");
			$(this).find("div.btn-group").stop().animate({opacity:"0",marginTop:"0"},300,"swing");
  	});
	
	
	/*-----------------------------------------------------case-----------------------------------------------------*/
	/*
	 * $(".case-info").slide({titCell:".hd ul",mainCell:".bd .ul-wrap",autoPage:true,effect:"left",autoPlay:false,easing:"swing"});
	*/
	$(".case-info .bd ul li:nth-child(3n+1)").css("margin-left", "0");
	
	var imgWid = 0 ;
	var imgHei = 0 ; 
	var big = 1.1;
	$(".case-info .bd ul li").hover(function(){
		$(this).find("img.pic-img").stop(true,true);
		var imgWid2 = 0;
		var imgHei2 = 0;
		imgWid = $(this).find("img").width();
		imgHei = $(this).find("img").height();
		imgWid2 = imgWid * big;
		imgHei2 = imgHei * big;
		$(this).find("img.pic-img").stop().animate({"width":imgWid2,"height":imgHei2,"margin-left":-imgWid2/2,"margin-top":-imgHei2/2},"swing");
		$(this).find("span.pic-img-bg").stop().animate({"opacity":"0.2"});
	},function(){
		$(this).find("img.pic-img").stop().animate({"width":imgWid,"height":imgHei,"margin-left":-imgWid/2,"margin-top":-imgHei/2},"swing");
		$(this).find("span.pic-img-bg").stop().animate({"opacity":"0"});
	});
	
	
	
	/*-----------------------------------------------------advantage-----------------------------------------------------*/
	$(".ad_info ul li:nth-child(2n+1)").css("margin-right", "100px");
	
	
	/*-----------------------------------------------------advantage-----------------------------------------------------*/
	$(".news-headline").hover(function(){
		$(this).find("p").stop().animate({bottom:"0",opacity:"0.9"},300);
	},function(){
		$(this).find("p").stop().animate({opacity:"0",bottom:"-36px"},300);
	});
	
	/*-----------------------------------------------------kf-----------------------------------------------------*/
	$(window).scroll(function() {
	if($(window).scrollTop() > 120) {
		$("#side-bar .gotop").fadeIn();
	} else {
		$("#side-bar .gotop").fadeOut();
	}
	});
	$("#side-bar .gotop").click(function() {
		$('html,body').animate({
		'scrollTop': 0
		}, 500);
	});

	$(document).ready(function($){
		$('.weixin2').click(function(){
			$('.theme-mask').show();
			$('.theme-mask').height($(document).height());
			$('.popover1').slideDown(200);
		});
		$('.close').click(function(){
		$('.theme-mask').hide();
		$('.popover1').slideUp(200);
		});
	});
	
	/*-----------------------------------------------------位移-----------------------------------------------------*/
/*	$('#scene1').parallax();
	$('#scene2').parallax();	
	$('#scene3').parallax();	
	$('#scene4').parallax();	
	$('#scene5').parallax();
	$('#scene6').parallax();*/

});




	