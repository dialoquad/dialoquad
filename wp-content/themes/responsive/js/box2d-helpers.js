/*
 *
 * Helper Funcitons
 *
 */

//Having to type 'Box2D.' in front of everything makes porting
//existing C++ code a pain in the butt. This function can be used
//to make everything in the Box2D namespace available without
//needing to do that.
function using(ns, pattern) {    
    if (pattern == undefined) {
        // import all
        for (var name in ns) {
            this[name] = ns[name];
        }
    } else {
        if (typeof(pattern) == 'string') {
            pattern = new RegExp(pattern);
        }
        // import only stuff matching given pattern
        for (var name in ns) {
            if (name.match(pattern)) {
                this[name] = ns[name];
            }
        }       
    }
}
    
var e_shapeBit = 0x0001;
var e_jointBit = 0x0002;
var e_aabbBit = 0x0004;
var e_pairBit = 0x0008;
var e_centerOfMassBit = 0x0010;


//to replace original C++ operator =
function copyVec2(vec) {
    return new b2Vec2(vec.get_x(), vec.get_y());
}

//to replace original C++ operator * (float)
function scaleVec2(vec, scale) {
    vec.set_x( scale * vec.get_x() );
    vec.set_y( scale * vec.get_y() );            
}

//to replace original C++ operator *= (float)
function scaledVec2(vec, scale) {
    return new b2Vec2(scale * vec.get_x(), scale * vec.get_y());
}


// http://stackoverflow.com/questions/12792486/emscripten-bindings-how-to-create-an-accessible-c-c-array-from-javascript
function createChainShape(vertices, closedLoop) {
    var shape = new b2ChainShape();            
    var buffer = Box2D.allocate(vertices.length * 8, 'float', Box2D.ALLOC_STACK);
    var offset = 0;
    for (var i=0;i<vertices.length;i++) {
        Box2D.setValue(buffer+(offset), vertices[i].get_x(), 'float'); // x
        Box2D.setValue(buffer+(offset+4), vertices[i].get_y(), 'float'); // y
        offset += 8;
    }            
    var ptr_wrapped = Box2D.wrapPointer(buffer, Box2D.b2Vec2);
    if ( closedLoop )
        shape.CreateLoop(ptr_wrapped, vertices.length);
    else
        shape.CreateChain(ptr_wrapped, vertices.length);
    return shape;
}

function createPolygonShape(vertices) {
    var shape = new b2PolygonShape();            
    var buffer = Box2D.allocate(vertices.length * 8, 'float', Box2D.ALLOC_STACK);
    var offset = 0;
    for (var i=0;i<vertices.length;i++) {
        Box2D.setValue(buffer+(offset), vertices[i].get_x(), 'float'); // x
        Box2D.setValue(buffer+(offset+4), vertices[i].get_y(), 'float'); // y
        offset += 8;
    }            
    var ptr_wrapped = Box2D.wrapPointer(buffer, Box2D.b2Vec2);
    shape.Set(ptr_wrapped, vertices.length);
    return shape;
}

function createRandomPolygonShape(radius) {
    var numVerts = 3.5 + Math.random() * 5;
    numVerts = numVerts | 0;
    var verts = [];
    for (var i = 0; i < numVerts; i++) {
        var angle = i / numVerts * 360.0 * 0.0174532925199432957;
        verts.push( new b2Vec2( radius * Math.sin(angle), radius * -Math.cos(angle) ) );
    }            
    return createPolygonShape(verts);
}

/*
 *
 * DebugDraw Functions
 *
 */ 

function drawAxes(ctx) {
    ctx.strokeStyle = 'rgb(192,0,0)';
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(1, 0);
    ctx.stroke();
    ctx.strokeStyle = 'rgb(0,192,0)';
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(0, 1);
    ctx.stroke();
}

function setColorFromDebugDrawCallback(color) {            
    var col = Box2D.wrapPointer(color, b2Color);
    var red = (col.get_r() * 255)|0;
    var green = (col.get_g() * 255)|0;
    var blue = (col.get_b() * 255)|0;
    var colStr = red+","+green+","+blue;
    context.fillStyle = "rgba("+colStr+",0.5)";
    context.strokeStyle = "rgb("+colStr+")";
}

function drawSegment(vert1, vert2) {
    var vert1V = Box2D.wrapPointer(vert1, b2Vec2);
    var vert2V = Box2D.wrapPointer(vert2, b2Vec2);                    
    context.beginPath();
    context.moveTo(vert1V.get_x(),vert1V.get_y());
    context.lineTo(vert2V.get_x(),vert2V.get_y());
    context.stroke();
}

function drawPolygon(vertices, vertexCount, fill) {
    context.beginPath();
    for(tmpI=0;tmpI<vertexCount;tmpI++) {
        var vert = Box2D.wrapPointer(vertices+(tmpI*8), b2Vec2);
        if ( tmpI == 0 )
            context.moveTo(vert.get_x(),vert.get_y());
        else
            context.lineTo(vert.get_x(),vert.get_y());
    }
    context.closePath();
    if (fill)
        context.fill();
    context.stroke();
}

function drawCircle(center, radius, axis, fill) {                    
	var centerV = Box2D.wrapPointer(center, b2Vec2);
    var axisV = Box2D.wrapPointer(axis, b2Vec2);
    
    context.beginPath();
    context.arc(centerV.get_x(),centerV.get_y(), radius, 0, 2 * Math.PI, false);
    if (fill)
        context.fill();
    context.stroke();
    
    if (fill) {
        //render axis marker
        var vert2V = copyVec2(centerV);
		var sVec2 =  scaledVec2(axisV, radius);
        vert2V.op_add( sVec2 );
        context.beginPath();
        context.moveTo(centerV.get_x(),centerV.get_y());
        context.lineTo(vert2V.get_x(),vert2V.get_y());
        context.stroke();
		Box2D.destroy(vert2V);
		Box2D.destroy(sVec2);
    }
}

function drawTransform(transform) {
    var trans = Box2D.wrapPointer(transform,b2Transform);
    var pos = trans.get_p();
    var rot = trans.get_q();
    
    context.save();
    context.translate(pos.get_x(), pos.get_y());
    context.scale(0.5,0.5);
    context.rotate(rot.GetAngle());
    context.lineWidth *= 2;
    drawAxes(context);
    context.restore();
}

function getCanvasDebugDraw() {
    var debugDraw = new Box2D.b2Draw();
        
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawSegment,
    replacement:
        function(ths, vert1, vert2, color) {                    
            setColorFromDebugDrawCallback(color);                    
            drawSegment(vert1, vert2);
        }
    }]);
    
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawPolygon,
    replacement:
        function(ths, vertices, vertexCount, color) {                    
            setColorFromDebugDrawCallback(color);
            drawPolygon(vertices, vertexCount, false);                    
        }
    }]);
    
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawSolidPolygon,
    replacement:
        function(ths, vertices, vertexCount, color) {                    
            setColorFromDebugDrawCallback(color);
            drawPolygon(vertices, vertexCount, true);                    
        }
    }]);
    
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawCircle,
    replacement:
        function(ths, center, radius, color) {                    
            setColorFromDebugDrawCallback(color);
            var dummyAxis = b2Vec2(0,0);
            drawCircle(center, radius, dummyAxis, false);
        }
    }]);
    
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawSolidCircle,
    replacement:
        function(ths, center, radius, axis, color) {                    
            setColorFromDebugDrawCallback(color);
            drawCircle(center, radius, axis, true);
        }
    }]);
    
    Box2D.customizeVTable(debugDraw, [{
    original: Box2D.b2Draw.prototype.DrawTransform,
    replacement:
        function(ths, transform) {
            drawTransform(transform);
        }
    }]);
    
    return debugDraw;
}

/*
 *
 * Testbed
 *
 */

// The scale between Box2D units and pixels
var SCALE = 30.0;

// Multiply to convert degrees to radians.
var D2R = Math.PI / 180;

// Multiply to convert radians to degrees.
var R2D = 180 / Math.PI;

// 360 degrees in radians.
var PI2 = Math.PI * 2;
var interval;


var PTM = 30;

var world = null;
var mouseJointGroundBody;
var canvas = document.getElementById("canvas");
var context;
var myDebugDraw;        
var myQueryCallback;
var mouseJoint = null;        
var run = true;
var frameTime60 = 0;
var statusUpdateCounter = 0;
var showStats = false;        
var mouseDown = false;
var shiftDown = false;
var tX, tY;
var mousePosPixel = {
    x: 0,
    y: 0
};
var prevMousePosPixel = {
    x: 0,
    y: 0
};        
var mousePosWorld = {
    x: 0,
    y: 0
};        
var canvasOffset = {
    x: 0,
    y: 0
};        
var viewCenterPixel = {
    x:320,
    y:240
};
var currentTest = null;

function myRound(val,places) {
    var c = 1;
    for (var i = 0; i < places; i++)
        c *= 10;
    return Math.round(val*c)/c;
}
        
function getWorldPointFromPixelPoint(pixelPoint) {
    return {                
        x: (pixelPoint.x - canvasOffset.x)/PTM,
        y: (pixelPoint.y - canvasOffset.y)/PTM //(canvas.height - canvasOffset.y))/PTM
    };
}

function updateMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();
    mousePosPixel = {
        x: evt.clientX - rect.left,
        y: evt.clientY - rect.top//canvas.height - (evt.clientY - rect.top)
    };
    mousePosWorld = getWorldPointFromPixelPoint(mousePosPixel);
}

function setViewCenterWorld(b2vecpos, instantaneous) {
    var currentViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    var toMoveX = b2vecpos.get_x() - currentViewCenterWorld.x;
    var toMoveY = b2vecpos.get_y() - currentViewCenterWorld.y;
    var fraction = instantaneous ? 1 : 0.25;
    canvasOffset.x -= myRound(fraction * toMoveX * PTM, 0);
    canvasOffset.y += myRound(fraction * toMoveY * PTM, 0);
}

function onMouseMove(canvas, evt) {
    prevMousePosPixel = mousePosPixel;
    updateMousePos(canvas, evt);
    updateStats();
    if ( shiftDown ) {
        canvasOffset.x += (mousePosPixel.x - prevMousePosPixel.x);
        canvasOffset.y -= (mousePosPixel.y - prevMousePosPixel.y);
        draw();
    }
    else if ( mouseDown && mouseJoint != null ) {
        mouseJoint.SetTarget( new b2Vec2(mousePosWorld.x, mousePosWorld.y) );
    }
}

function startMouseJoint() {
    
    if ( mouseJoint != null )
        return;
    
    // Make a small box.
    var aabb = new b2AABB();
    var d = 0.001;            
    aabb.set_lowerBound(new b2Vec2(mousePosWorld.x - d, mousePosWorld.y - d));
    aabb.set_upperBound(new b2Vec2(mousePosWorld.x + d, mousePosWorld.y + d));
    
    // Query the world for overlapping shapes.            
    myQueryCallback.m_fixture = null;
    myQueryCallback.m_point = new b2Vec2(mousePosWorld.x, mousePosWorld.y);
    world.QueryAABB(myQueryCallback, aabb);
    
    if (myQueryCallback.m_fixture)
    {
        var body = myQueryCallback.m_fixture.GetBody();
        var md = new b2MouseJointDef();
        md.set_bodyA(mouseJointGroundBody);
        md.set_bodyB(body);
        md.set_target( new b2Vec2(mousePosWorld.x, mousePosWorld.y) );
        md.set_maxForce( 1000 * body.GetMass() );
        md.set_collideConnected(true);
        
        mouseJoint = Box2D.castObject( world.CreateJoint(md), b2MouseJoint );
        body.SetAwake(true);
    }
}

function onMouseDown(canvas, evt) {            
    updateMousePos(canvas, evt);
    if ( !mouseDown )
        startMouseJoint();
    mouseDown = true;
    updateStats();
}

function onMouseUp(canvas, evt) {
    mouseDown = false;
    updateMousePos(canvas, evt);
    updateStats();
    if ( mouseJoint != null ) {
        world.DestroyJoint(mouseJoint);
        mouseJoint = null;
    }
}

function onMouseOut(canvas, evt) {
    onMouseUp(canvas,evt);
}

function onKeyDown(canvas, evt) {
    //console.log(evt.keyCode);
    if ( evt.keyCode == 80 ) {//p
        pause();
    }
    else if ( evt.keyCode == 82 ) {//r
        resetScene();
    }
    else if ( evt.keyCode == 83 ) {//s
        step();
    }
    else if ( evt.keyCode == 88 ) {//x
        zoomIn();
    }
    else if ( evt.keyCode == 90 ) {//z
        zoomOut();
    }
    else if ( evt.keyCode == 37 ) {//left
        canvasOffset.x += 32;
    }
    else if ( evt.keyCode == 39 ) {//right
        canvasOffset.x -= 32;
    }
    else if ( evt.keyCode == 38 ) {//up
        canvasOffset.y += 32;
    }
    else if ( evt.keyCode == 40 ) {//down
        canvasOffset.y -= 32;
    }
    else if ( evt.keyCode == 16 ) {//shift
        shiftDown = true;
    }
    
    if ( currentTest && currentTest.onKeyDown )
        currentTest.onKeyDown(canvas, evt);
    
    draw();
}

function onKeyUp(canvas, evt) {
    if ( evt.keyCode == 16 ) {//shift
        shiftDown = false;
    }
    
    if ( currentTest && currentTest.onKeyUp )
        currentTest.onKeyUp(canvas, evt);
}

function zoomIn() {
    var currentViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    PTM *= 1.1;
    var newViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    canvasOffset.x += (newViewCenterWorld.x-currentViewCenterWorld.x) * PTM;
    canvasOffset.y -= (newViewCenterWorld.y-currentViewCenterWorld.y) * PTM;
    draw();
}

function zoomOut() {
    var currentViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    PTM /= 1.1;
    var newViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    canvasOffset.x += (newViewCenterWorld.x-currentViewCenterWorld.x) * PTM;
    canvasOffset.y -= (newViewCenterWorld.y-currentViewCenterWorld.y) * PTM;
    draw();
}
        
function updateDebugDrawCheckboxesFromWorld() {
    var flags = myDebugDraw.GetFlags();
}

function updateWorldFromDebugDrawCheckboxes() {
    var flags = 0;
    if ( document.getElementById('drawShapesCheck').checked )
        flags |= e_shapeBit;
    if ( document.getElementById('drawJointsCheck').checked )
        flags |= e_jointBit;
    if ( document.getElementById('drawAABBsCheck').checked )
        flags |= e_aabbBit;
    /*if ( document.getElementById('drawPairsCheck').checked )
        flags |= e_pairBit;*/
    if ( document.getElementById('drawTransformsCheck').checked )
        flags |= e_centerOfMassBit;
    myDebugDraw.SetFlags( flags );
}

function updateContinuousRefreshStatus() {
    showStats = ( document.getElementById('showStatsCheck').checked );
    if ( !showStats ) {
        var fbSpan = document.getElementById('feedbackSpan');
        fbSpan.innerHTML = "";
    }
    else
        updateStats();
}

function init() {
    
    //canvas = document.getElementById("canvas");
	//canvas.width = $(".post-entry .tagcloud").width();
	//canvas.height = $(".post-entry .tagcloud").height();
	context = canvas.getContext( '2d' );
    
    //canvasOffset.x = canvas.width/2.0;
    //canvasOffset.y = canvas.height/2.0;
    
    canvas.addEventListener('mousemove', function(evt) {
        onMouseMove(canvas,evt);
    }, false);
    
    canvas.addEventListener('mousedown', function(evt) {
        onMouseDown(canvas,evt);
    }, false);
    
    canvas.addEventListener('mouseup', function(evt) {
        onMouseUp(canvas,evt);
    }, false);
    
    canvas.addEventListener('mouseout', function(evt) {
        onMouseOut(canvas,evt);
    }, false);
    
    canvas.addEventListener('keydown', function(evt) {
        onKeyDown(canvas,evt);
    }, false);
    
    canvas.addEventListener('keyup', function(evt) {
        onKeyUp(canvas,evt);
    }, false);
    
    myDebugDraw = getCanvasDebugDraw();            
    myDebugDraw.SetFlags(e_shapeBit);

    myQueryCallback = new b2QueryCallback();
    
    Box2D.customizeVTable(myQueryCallback, [{
    original: Box2D.b2QueryCallback.prototype.ReportFixture,
    replacement:
        function(thsPtr, fixturePtr) {
            var ths = Box2D.wrapPointer( thsPtr, b2QueryCallback );
            var fixture = Box2D.wrapPointer( fixturePtr, b2Fixture );
            if ( fixture.GetBody().GetType() != Box2D.b2_dynamicBody ) //mouse cannot drag static bodies around
                return true;
            if ( ! fixture.TestPoint( ths.m_point ) )
                return true;
            ths.m_fixture = fixture;
            return false;
        }
    }]);
}

function changeTest() {    
    resetScene();
    //if ( currentTest && currentTest.setNiceViewCenter )
       // currentTest.setNiceViewCenter();
    updateDebugDrawCheckboxesFromWorld();
    draw();
}

function createWorld() {
    
    if ( world != null ) 
        Box2D.destroy(world);
        
    world = new b2World( new b2Vec2(0.0, 0.0) );
    world.SetDebugDraw(myDebugDraw);
    
    mouseJointGroundBody = world.CreateBody( new b2BodyDef() );
    
    var v = "Box2d_Tagfog";
    
    eval( "currentTest = new "+v+"();" );
    
    currentTest.setup();
}

function resetScene() {
    createWorld();
    draw();
}

function step(timestamp) {
    
    if ( currentTest && currentTest.step ) 
        currentTest.step();
    
    if ( ! showStats ) {
        world.Step(1/60, 3, 2);
        draw();
        return;
    }
    
    var current = Date.now();
    world.Step(1/60, 3, 2);
    var frametime = (Date.now() - current);
    frameTime60 = frameTime60 * (59/60) + frametime * (1/60);
    
    draw();
    statusUpdateCounter++;
    if ( statusUpdateCounter > 20 ) {
        updateStats();
        statusUpdateCounter = 0;
    }
}

function draw() {
    
    //black background
    context.fillStyle = 'rgb(255,255,255)';
    context.fillRect( 0, 0, canvas.width, canvas.height );
    
    context.save();            
        //context.translate(canvasOffset.x, canvasOffset.y);
        //context.scale(1,-1);
		context.scale(PTM,PTM);
        context.lineWidth /= PTM;
        
        
        context.fillStyle = 'rgba(255,255,0)';
        world.DrawDebugData();
        
        if ( mouseJoint != null ) {
            //mouse joint is not drawn with regular joints in debug draw
            var p1 = mouseJoint.GetAnchorB();
            var p2 = mouseJoint.GetTarget();
            context.strokeStyle = 'rgb(204,204,204)';
            context.beginPath();
            context.moveTo(p1.get_x(),p1.get_y());
            context.lineTo(p2.get_x(),p2.get_y());
            context.stroke();
        }
        
    context.restore();
}

function updateStats() {
    if ( ! showStats )
        return;
    var currentViewCenterWorld = getWorldPointFromPixelPoint( viewCenterPixel );
    var fbSpan = document.getElementById('feedbackSpan');
    fbSpan.innerHTML =
        "Status: "+(run?'running':'paused') +
        "<br>Physics step time (average of last 60 steps): "+myRound(frameTime60,2)+"ms" +
        //"<br>Mouse down: "+mouseDown +
        "<br>PTM: "+myRound(PTM,2) +
        "<br>View center: "+myRound(currentViewCenterWorld.x,3)+", "+myRound(currentViewCenterWorld.y,3) +
        //"<br>Canvas offset: "+myRound(canvasOffset.x,0)+", "+myRound(canvasOffset.y,0) +
        "<br>Mouse pos (pixel): "+mousePosPixel.x+", "+mousePosPixel.y +
        "<br>Mouse pos (world): "+myRound(mousePosWorld.x,3)+", "+myRound(mousePosWorld.y,3);
}

window.requestAnimFrame = (function(){
    return  window.requestAnimationFrame       || 
            window.webkitRequestAnimationFrame || 
            window.mozRequestAnimationFrame    || 
            window.oRequestAnimationFrame      || 
            window.msRequestAnimationFrame     || 
            function( callback ){
              window.setTimeout(callback, 1000 / 60);
            };
})();

function animate() {
    if ( run )
        requestAnimFrame( animate );
    step();
}

function pause() {
    run = !run;
    if (run)
        animate();
    updateStats();
}
