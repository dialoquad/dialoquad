
var Box2d_Tagfog = function() {
};

Box2d_Tagfog.prototype.setup = function() {

    // Create ground body.
    var fixDef = new b2FixtureDef; 
    fixDef.set_density(5);
    fixDef.set_friction(0.0);
    fixDef.set_restitution(1.0);

    var bodyDef = new b2BodyDef();
    bodyDef.set_type(b2_dynamicBody);
    p = new b2Vec2();
    var circle = new b2CircleShape();
    circle.set_m_radius(0.1);
    fixDef.set_shape(circle);
	var w = $(".post-entry .tagcloud").width();
	var h = $(".post-entry .tagcloud").height();
	for(var i = 0; i < 200; ++i) {
        p.set_x(Math.random() * w/SCALE); 
        p.set_y(Math.random() * h/SCALE); 
        bodyDef.set_position(p);
        p.set_x( 1 * (Math.random() * w/SCALE - w/SCALE/2.0)); 
        p.set_y( 1 * (Math.random() * h/SCALE - h/SCALE/2.0)); 
		bodyDef.set_linearVelocity(p);
        world.CreateBody(bodyDef).CreateFixture(fixDef);
    }

}

function UserData(domObj, width, height, left, top){
	this.obj = domObj;
	this.w = width;
	this.h = height;
	this.l = left;
	this.t = top;
}

function createDOMObjects() {
	//iterate all div elements and create them in the Box2D system
	var $tagcloud = $(".post-entry .tagcloud");
	var tcw = $tagcloud.outerWidth();
	var tch = $tagcloud.outerHeight();
	$tagcloud.css('width',tcw).css('height',tch);
	b2Fixture.prototype.SetData = function(data){
		this.userData = data;
	}
	b2Fixture.prototype.GetData = function(){
		return this.userData;
	}
	var box = $(".post-entry .tagcloud");
	var W = box.parent().parent().width();
	var H = box.parent().parent().height() + box.parent().parent().offset().top - box.offset().top;
	var offsetX = box.parent().parent().offset().left - box.offset().left;
	window.tX = function(x){
		return x + offsetX;
	};
	window.tY = function(y){
		return y;
	};

	$(".post-entry .tagcloud a").each(function () {
		var domObj = $(this);
		var domPos = $(this).position();
		var width = domObj.outerWidth() / 2.0;
		var height = domObj.outerHeight() / 2.0;
		var x = (domPos.left);
		var y = (domPos.top);
		dx = tX(x*W/tcw) - x;
		dy = y*H/tch - y;
		var css = {'-webkit-transform':'translate(' + dx + 'px,' + dy + 'px)', '-moz-transform':'translate(' + dx + 'px,' + dy + 'px)', '-ms-transform':'translate(' + dx + 'px,' + dy + 'px)'  , '-o-transform':'translate(' + dx + 'px,' + dy + 'px)', 'transform':'translate(' + dx + 'px,' + dy + 'px)'};
		$(this).css(css);

		x = x*W/tcw + width;
		y = y*H/tch + height;
		var body = createBox(x,y,width,height);
		var userData = new UserData(domObj,width,height,domPos.left,domPos.top);
		body.SetData(userData);
	});
	

}

function createBox(x,y,width,height) {
	var bodyDef = new b2BodyDef;
	bodyDef.set_type(b2_dynamicBody);
	var Vec2 = new b2Vec2(x / SCALE, y / SCALE)
	bodyDef.set_position(Vec2);
	bodyDef.set_fixedRotation(true); 
	

	var fixDef = new b2FixtureDef;
    fixDef.set_density(1.0);
    fixDef.set_friction(0.0);
    fixDef.set_restitution(1.0);

	var shape = new b2PolygonShape;
	shape.SetAsBox(width / SCALE, height / SCALE);
	fixDef.set_shape(shape);
	return world.CreateBody(bodyDef).CreateFixture(fixDef);
}

//Animate DOM objects
function drawDOMObjects() {
	var i = 0;
	for (var b = world.GetBodyList(); b.a != 0 ; b = b.GetNext()) {
		for (var f = b.GetFixtureList(); f.a != 0; f = f.GetNext()) {
			if (f.GetData()) {
				//Retrieve positions and rotations from the Box2d world
				var x = tX(Math.floor((f.GetBody().GetPosition().get_x() * SCALE) - f.GetData().w)) - f.GetData().l;
				var y = Math.floor((f.GetBody().GetPosition().get_y() * SCALE) - f.GetData().h) - f.GetData().t;
				var css = {'-webkit-transform':'translate(' + x + 'px,' + y + 'px)', '-moz-transform':'translate(' + x + 'px,' + y + 'px)', '-ms-transform':'translate(' + x + 'px,' + y + 'px)'  , '-o-transform':'translate(' + x + 'px,' + y + 'px)', 'transform':'translate(' + x + 'px,' + y + 'px)'};

				f.GetData().obj.css(css);
			}
		}
	}
};

//Keep the canvas size correct for debug drawing
function resizeHandler() {
	var box = $(".post-entry .tagcloud");
	canvas.width = box.parent().parent().width();
	canvas.height = box.parent().parent().height();
	$("#canvas").css('left', box.parent().parent().offset().left - box.offset().left + 'px');
}

function createEdge(){
	var bodyDef = new b2BodyDef();
    bodyDef.set_type(b2_staticBody);
    var shape0 = new b2EdgeShape();
	var vec1 = new b2Vec2();
	var vec2 = new b2Vec2();
	var box = $(".post-entry .tagcloud");
	var w = box.parent().parent().width();
	var h = box.parent().parent().height() + box.parent().parent().offset().top - box.offset().top;
    shape0.Set(new b2Vec2(0, 0), new b2Vec2(w/SCALE, 0));
    world.CreateBody(bodyDef).CreateFixture(shape0, 0.0);
    shape0.Set(new b2Vec2(0, 0), new b2Vec2(0, h/SCALE));
    world.CreateBody(bodyDef).CreateFixture(shape0, 0.0);
    shape0.Set(new b2Vec2(w/SCALE, 0), new b2Vec2(w/SCALE, h/SCALE));
    world.CreateBody(bodyDef).CreateFixture(shape0, 0.0);
    shape0.Set(new b2Vec2(0, h/SCALE), new b2Vec2(w/SCALE, h/SCALE));
    world.CreateBody(bodyDef).CreateFixture(shape0, 0.0);
}

function update(){
	world.Step(
		1 / 60 //frame-rate
		, 10 //velocity iterations
		, 10 //position iterations
	);
    
	drawDOMObjects();
}

function debugUpdate(){
   	step(); 
	drawDOMObjects();
}
//Create DOB OBjects
window.onload = function(){
	var state = {
		isdone : false,
		Intid : 0,
		iteration: 0
	};
	$.fn.toggleClick = function(){
		var functions = arguments;
		return this.click(function(){
			functions[state.iteration].apply(this,arguments);
			state.iteration = (state.iteration + 1) % functions.length;
		});
	};

	var fn1 = function(id){
		if( !state.isdone ){
			state.isdone = true;
			using(Box2D, "b2.+");
    		init();
			resizeHandler();
    		createWorld();
			createEdge();
			createDOMObjects();
			$(window).bind('resize', resizeHandler);
			state.Intid = setInterval(update,1000.0/20.0);
		}else{
			clearInterval(state.Intid);
			$("#canvas").css('display','none');
			state.Intid = setInterval(update, 1000.0/20.0);
		}
	};

	var fn2 = function(){
		clearInterval(state.Intid);
	};
	
	var fn3 = function(){
		$("#canvas").css('display','block');
		clearInterval(state.Intid);
		state.Intid = setInterval(debugUpdate, 1000.0/60.0);
	};

	//Make sure that the screen canvas for debug drawing matches the window size
    $(".brownian a").toggleClick(function(){fn1();},
		function(){fn2();},
		function(){fn3();
	});
    
	$(".fog-restore a").click(function(){
		clearInterval(state.Intid);
		$("#canvas").css('display','none');
		state.iteration = 0;
		for (var b = world.GetBodyList(); b.a != 0 ; b = b.GetNext()) {
			for (var f = b.GetFixtureList(); f.a != 0; f = f.GetNext()) {
				if (f.GetData()) {
					//Retrieve positions and rotations from the Box2d world
					var x =  f.GetData().l;
					var y =  f.GetData().t;
					var css = {'-webkit-transform':'translate(0px,0px)', '-moz-transform':'translate(0px,0px)', '-ms-transform':'translate(0px,0px)','-o-transform':'translate(0px,0px)', 'transform':'translate(0px,0px)'};

					f.GetData().obj.css(css);
				}
			}
		}

	});
};
