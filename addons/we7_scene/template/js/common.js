$(function(){
	/*tooltipster显示*/
	$('.tooltip').tooltipster({delay:0});
	/*header部分列表轮播*/
	var scrollAnimate = $( '.scrollbox' );
	var	horn = scrollAnimate.find( 'ul' );
	var	speed = 10;
	var	timer = 3000;
	scrollUp = function(){
		horn.animate({
			marginTop:-scrollAnimate.height()},
			speed,
			function(){
				horn.find('li:first').remove().appendTo(horn);
				horn.css({marginTop:0});
			}
		);
	}
	!scrollAnimate.length > 0 || setInterval( scrollUp, timer );

	/*container 部分列表轮播*/
	var num = 3;
	function scrollUserList(){
		var scrollBox = $('#container .scrollBox');
		scrollBox.flag = true;////判断点击按钮的时候一次动画是否运动完成
		var userList = scrollBox.find('.user-list');
		var liH = $('.user-list .user-list-item').height();
		var btnNext = $('#footer .btn-next');
		var btnPrev = $('#footer .btn-prev');
		var btnOldest = $('#footer .btn-old');
		var btnNewest = $('#footer .btn-new');
		var len = userList.find('.user-list-item').length;
		function moves(){
			userList.animate({top:-liH * num-45},500,function(){
					for (var i=0; i<num; i++) {
						userList.find('li:first').remove().appendTo(userList);
					}
					userList.css({top:0});
				});
		}
		if(len>3){
			scrollBox.t = setInterval(moves,3000);
		}

		/*上一条*/
		btnPrev.click(function(){

		});

		/*下一条*/
		btnNext.click(function(){

		});

		/*最旧屏*/
		btnOldest.click(function(){

		});

		/*最新屏*/
		btnNewest.click();
	}
	scrollUserList();

	/*倒计时*/
	var canvas = document.getElementById("cirProgress");
	var canvasW = canvas.width;
	var canvasH = canvas.height;
	var context = canvas.getContext('2d');
	var step,startAngle,endAngle,add=Math.PI*2/100;
	context.lineWidth = 3.0;
	counterClockwise = false;
	var x;
	var y;
	var radius;
	var animation_interval = 20,n = 100;
	var circle;
	/*白色圈*/
	var canvasCircle = document.getElementById('circle');
	var ctx = canvasCircle.getContext("2d");
	ctx.beginPath();
	ctx.strokeStyle="#FFF";
	ctx.arc(25, 25, 18, -Math.PI/2, Math.PI*3/2, counterClockwise);
	ctx.lineWidth = 2.0;
	ctx.stroke();
	/*画倒计时*/
	function actiondo(){
		step=1;
		startAngle=-Math.PI/2;
		context.strokeStyle ='#EA3873';
		//圆心位置
		x=canvasW/2;
		y=canvasH/2;
		radius = 18;
		circle= setInterval(animation, animation_interval);
		context.clearRect(0,0,canvas.width,50)
	}
	var animation = function () {
		if (step <= n) {
			endAngle = startAngle + add ;
			drawArc(startAngle, endAngle);
			startAngle = endAngle;
			step++;
		} else {
			clearInterval(circle);
		}

	};
	function drawArc(s, e) {
		context.beginPath();
		context.arc(x, y, radius, s, e, counterClockwise);
		context.lineWidth = 2.0;
		context.stroke();
	}
	setInterval(actiondo,3000);
	
	/*全屏显示*/
	$('#footer .btnFullScreen').click(function(){
		var el = document.documentElement;
		var rfs = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen || el.msRequestFullScreen;
		if (typeof rfs != "undefined" && rfs) {
			rfs.call(el);
		} else if (typeof window.ActiveXObject != "undefined"){
			// for Internet Explorer
			var wscript = new ActiveXObject("WScript.Shell");
			if (wscript != null) {
				wscript.SendKeys("{F11}");
			}
		}
	});

});