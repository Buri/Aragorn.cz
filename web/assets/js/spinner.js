/*
---
description: DynSpinner classes.

license: MIT-style

authors:
- Francois Cartegnie

requires:
- core/1.2.3: '*'

provides: [DynSpinner.Canvas.Flower, DynSpinner.Canvas.Bubbles,DynSpinner.SVG.Flower, DynSpinner.SVG.Bubbles]

...
*/

var DynSpinner = new Class({
	Implements: [Options, Events],

	options: {
		nb_subdivision: 9,
		duration: Fx.Durations.short,
		max_size: 0.6,
		opacity: 0.8,
		fps: 5,
		inner_ratio: 0.2,
		spacing: 0.25,
		rotation: 1,
		storage: null,
		gradient: ['#808080', '#C0C0C0'],
		stroke: '#000000'
	},

	primitives: {
		init: function() {},
		clear: function(w,h) {},
		setpalette: function(p,c) {},
		startelement: function(a) {},
		endelement: function() {},
		scale: function(s, draw, drawp) {},
		pie: function(center, inner, radius, startangle, angle) {},
		circle: function(center, inner, radius, startangle, angle) {}
	},

	initialize: function(el, options){
		this.setOptions(options);
		this.path_index = 0;
		this.active_segment = 1;
		this.target = null;
		this.timer = null;
		this.options.storage = document.id(this.options.storage) || document.id(document.body);
		this.root_element = new Element('div',{
			'class': 'dynspinner_layer',
			'styles': {
				'position': 'absolute',
				'top': 0,
				'left': 0,
				'z-index': 10000
			}
		});
		this.primitives.init.apply(this, []);

		this.root_element.grab(this.container_element);

		this.setTarget(el);
		this.options.storage.grab(this.root_element, 'bottom');
		window.addEvent('resize', this.adaptSize.bind(this));
		this.root_element.setStyle('visibility', 'hidden');
	},

	setTarget: function(el) {
		var adaptsize = this.adaptSize.bind(this);
		/* clean up if we reattach somewhere else */
		if (this.target !== null) {this.target.removeEvent('resize', adaptsize);}
		this.target = document.id(el);
		adaptsize();
		this.target.addEvent('resize', adaptsize);
		return this;
	},

	adaptSize: function() {
		var referenceCoords = this.target.getCoordinates(this.options.storage);
		this.width = referenceCoords.width;
		this.height = referenceCoords.height;
		this.root_element.setStyles(referenceCoords);

		var bestsize = Math.min(this.width, this.height) * this.options.max_size;
		this.container_element.set({
			'width': bestsize,
			'height': bestsize
		});
		this.container_element.setStyles({
			'margin-left': Math.floor((this.width - bestsize) / 2),
			'margin-right': Math.floor((this.width - bestsize) / 2),
			'margin-top': Math.floor((this.height - bestsize) / 2)
		});
		return this;
	},

	spinHandler: function(){
		if (!this.width || !this.height) { return; }
		var scaling = (this.width < 50 || this.height < 50)?Math.ceil(50 / Math.min(this.width, this.height)):1.0;
		var bestsize = Math.min(this.width, this.height) * this.options.max_size * scaling - 2; /* stroke = 1px */
		var center = {'x': bestsize / 2 + 1, 'y': bestsize / 2 + 1};  /* stroke = 1px */
		var radius = bestsize / 2;
		this.primitives.scale.apply(this, [scaling, this.draw, [center, bestsize, radius]]);
	},

	draw: function(center, bestsize, radius){
		var arc_start = 0;
		var segment;
		var inner = radius * this.options.inner_ratio;
		this.primitives.clear.apply(this, [bestsize, bestsize]);
		/* FIXME: rebuild palette only when resized */
		this.primitives.setpalette.apply(this, [[center.x, center.y, inner, center.x, center.y, radius], this.options.gradient]);
		var arc_angle = ((Math.PI * 2) / this.options.nb_subdivision);
		var spacing = this.options.spacing;
		if (this.active_segment++ >= this.options.nb_subdivision) { this.active_segment = 1; }
		for (segment = 0; segment < this.options.nb_subdivision ; segment++ ) {
			drawangle_start = arc_start + arc_angle * spacing;
			drawangle_end = arc_start + arc_angle * (1-spacing);
			drawangle = arc_angle * (1 - 2*spacing);
			var drawseg = segment + this.active_segment;
			if (drawseg > this.options.nb_subdivision) {	drawseg -= this.options.nb_subdivision; }
			this.primitives.startelement.apply(this, [1.0 - (1.0 * drawseg / this.options.nb_subdivision)]);			
			this.primitives[this.shape].apply(this, [center, inner, radius, drawangle_start, drawangle]);
			this.primitives.endelement.apply(this, []);
			arc_start -= arc_angle * this.options.rotation;
		}
	},

	startSpin: function() {
		if (this.timer === null) {
			this.adaptSize();
			this.root_element.setStyles({'opacity': 0.0, 'visibility': 'visible'});
			this.root_element.tween('opacity', 0.0, this.options.opacity);
			this.timer = this.spinHandler.periodical(this.options.duration, this);
			return true;
		} else { return false; }
	},

	stopSpin: function() {
		if (this.timer !== null) {
			this.timer = $clear(this.timer);
			this.root_element.tween('opacity', this.options.opacity, 0.0);
			(function(){ this.root_element.setStyle('visibility', 'hidden'); }.bind(this)).delay(Fx.Durations.short);
			return true;
		} else { return false; }
	}
});//!Class

DynSpinner.Canvas = new Class({
	Extends: DynSpinner,
	primitives: {
		/* Our canvas drawing primitives */
		init: function() {
			this.container_element = new Element('canvas',{
				'class': 'dynspinner_container'
			});
			this.ctx = this.container_element.getContext('2d');
		},
		clear: function(w,h) { this.ctx.clearRect(0, 0, w, h); },
		setpalette: function(p,c) {
			var grad = this.ctx.createRadialGradient.apply(this.ctx, p);
			c.each( function(col,i){ grad.addColorStop(i, col); } );
			this.ctx.fillStyle = grad;
			this.ctx.strokeStyle = this.options.stroke;
		},
		startelement: function(a) {
			this.ctx.beginPath();
			this.ctx.globalAlpha = a;
		},
		endelement: function() { 
			this.ctx.closePath();
			this.ctx.fill();
			this.ctx.stroke();
		},
		scale: function(s, draw, drawp) {
			this.ctx.save();
			this.ctx.scale(1.0 / s, 1.0 / s); /* Use resampling for better quality */
			this.draw.apply(this, drawp);
			this.ctx.restore();
		},
		pie: function(center, inner, radius, startangle, angle){
			var halfangle = startangle + drawangle/2;
			var x = Math.floor(center.x + inner * Math.cos(startangle + angle));
			var y = Math.floor(center.y + inner * Math.sin(startangle + angle));
			var x1 = center.x + Math.cos( halfangle ) * inner;
			var y1 = center.y + Math.sin( halfangle ) * inner;
			var x2 = center.x + inner * Math.cos(drawangle_start);
			var y2 = center.y + inner * Math.sin(drawangle_start);
			this.ctx.arc(center.x, center.y, radius, startangle, startangle + angle, false);
			this.ctx.lineTo(x,y);
			this.ctx.quadraticCurveTo(x1,y1,x2,y2);
		},
		circle: function(center, inner, radius, startangle, angle) {
			var x = Math.floor(center.x + radius*3/4 * Math.cos(startangle + angle));
			var y = Math.floor(center.y + radius*3/4 * Math.sin(startangle + angle));
			this.ctx.arc(x, y, radius/5, 0, Math.PI * 2, true);
		}
	}
});//!Class

DynSpinner.SVG = new Class({
	Extends: DynSpinner,

	/* Need to build namespaced node */
	svgel: function(n) { return document.createElementNS('http://www.w3.org/2000/svg', n); },

	primitives: {
		/* Our svg drawing primitives */
		/* FIXME: Redrawing each frame is stupid with svg. Better have persistent objects based code */
		init: function() {
				/* WARN! SVG nodes are NOT elements */
				this.svg = this.svgel('svg');
				this.svg.set({'version': '1.1'});
				this.container_element = this.svg;
				this.g = this.svgel('g');
				this.svg.adopt(this.g);
		},
		clear: function(w,h) {
			this.g.empty();
		},
		setpalette: function(p,c) {//[center.x, center.y, inner, center.x, center.y, radius]
			var grad = this.svgel('radialGradient').set({id: 'grad', fx: p[0], fy: p[1], cx: p[0], cy: p[1], r: p[5], gradientUnits: 'userSpaceOnUse'});
			c.each( function(col,i){
				var off = Math.floor( 100 / (c.length - 1) ) * i;
				grad.adopt( this.svgel('stop').set({'offset': off + '%', 'stop-color': col}) );
			}.bind(this));
			this.g.adopt( this.svgel('defs').adopt(grad) );
		},
		startelement: function(a) { this.currentalpha = a ; },
		endelement: function() { },
		scale: function(s, draw, drawp) {
			/* TODO: fix scaling via viewbox */
			this.svg.set({'viewBox': '0 0 ' + this.width + ' ' + this.height, 'width': this.width / s, 'height': this.height / s});
			this.draw.apply(this, drawp);
		},
		pie: function(center, inner, radius, startangle, angle){
			var x = Math.floor(center.x + inner * Math.cos(startangle + angle));
			var y = Math.floor(center.y + inner * Math.sin(startangle + angle));
			var x2 = center.x + inner * Math.cos(drawangle_start);
			var y2 = center.y + inner * Math.sin(drawangle_start);
			var x3 = center.x + Math.cos( startangle ) * radius;
			var y3 = center.y + Math.sin( startangle ) * radius;
			var x4 = center.x + Math.cos( startangle + angle ) * radius;
			var y4 = center.y + Math.sin( startangle + angle ) * radius;
			var sweep = (angle >= Math.PI) ? 1:0;
			var p = this.svgel('path');
			p.set({style:'fill:url(#grad);stroke-width:1;stroke:' + this.options.stroke + ';opacity:' + this.currentalpha});
			p.set({d: 'M' + Math.floor(x3) + ',' + Math.floor(y3)
			+ ' A' + radius + ',' + radius + ' 0 ' + sweep + ',1 ' + Math.floor(x4) + ',' + Math.floor(y4)			
			+ ' L' + x + ',' + y
			+ ' A' + radius + ',' + radius + ' 0 ' + sweep + ',1 ' + Math.floor(x2) + ',' + Math.floor(y2)
			+ ' z' });
			this.g.adopt(p);
		},
		circle: function(center, inner, radius, startangle, angle) {
			var x = Math.floor(center.x + radius*3/4 * Math.cos(startangle + angle));
			var y = Math.floor(center.y + radius*3/4 * Math.sin(startangle + angle));
			var c = this.svgel('circle');
			c.set({cx: x, cy: y, r: radius/5, style:'fill:url(#grad);stroke-width:1;stroke:' + this.options.stroke + ';opacity:' + this.currentalpha });
			this.g.adopt(c);
		}
	}
});//!Class

DynSpinner.Canvas.Flower = new Class({
	Extends: DynSpinner.Canvas,
	shape: 'pie'
});//!Class

DynSpinner.Canvas.Bubbles = new Class({
	Extends: DynSpinner.Canvas,
	shape: 'circle'
});//!Class

DynSpinner.SVG.Flower = new Class({
	Extends: DynSpinner.SVG,
	shape: 'pie'
});//!Class

DynSpinner.SVG.Bubbles = new Class({
	Extends: DynSpinner.SVG,
	shape: 'circle'
});//!Class
