/*
---
 
name: Mif.Tree
description: Mif.Tree base Class
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: 
  - Core:1.2.4/*
  - More/Fx.Scroll
provides: Mif.Tree
 
...
*/

if(!Mif) var Mif = {};
if(!Mif.ids) Mif.ids = {};
if(!Mif.id) Mif.id = function(id){
	return Mif.ids[id];
};

Mif.Tree = new Class({
	
	version: '1.2.6.4',

	Implements: [ Events, Options],
		
	options:{
		types: {},
		forest: false,
		animateScroll: true,
		height: 18,
		expandTo: true
	},
	
	initialize: function(options){
		this.setOptions(options);
		$extend(this, {
			types: this.options.types,
			forest: this.options.forest,
			animateScroll: this.options.animateScroll,
			dfltType: this.options.dfltType,
			height: this.options.height,
			container: $(options.container),
			UID: ++Mif.Tree.UID,
			key: {},
			expanded: []
		});
		this.defaults = {
			name: '',
			cls: '',
			openIcon: 'mif-tree-empty-icon',
			closeIcon: 'mif-tree-empty-icon',
			loadable: false,
			hidden: false
		};
		this.dfltState = {
			open: false
		};
		this.$index = [];
		this.updateOpenState();
		if(this.options.expandTo) this.initExpandTo();
		this.DOMidPrefix='mif-tree-';
		this.wrapper = new Element('div').addClass('mif-tree-wrapper').injectInside(this.container);
		this.events();
		this.initScroll();
		this.initSelection();
		this.initHover();
		this.addEvent('drawChildren', function(parent){
			var nodes = parent._toggle||[];
			for(var i = 0, l = nodes.length; i < l; i++){
				nodes[i].drawToggle();
			}
			parent._toggle = [];
		});
		var id = this.options.id;
		this.id = id;
		if(id != null) Mif.ids[id] = this;
		if (MooTools.version >= '1.2.2' && this.options.initialize) this.options.initialize.call(this);
	},
	
	bound: function(){
		Array.each(arguments, function(name){
			this.bound[name] = this[name].bind(this);
		}, this);
	},
	
	events: function(){
		this.bound('mouse', 'mouseleave', 'mousedown', 'preventDefault', 'toggleClick', 'toggleDblclick', 'focus', 'blurOnClick', 'keyDown', 'keyUp');
		
		this.wrapper.addEvents({
			mousemove: this.bound.mouse,
			mouseover: this.bound.mouse,
			mouseout: this.bound.mouse,
			mouseleave: this.bound.mouseleave,
			mousedown: this.bound.mousedown,
			click: this.bound.toggleClick,
			dblclick: this.bound.toggleDblclick,
			selectstart: this.bound.preventDefault
		});
		
		this.container.addEvent('click', this.bound.focus);
		document.addEvent('click', this.bound.blurOnClick);
		
		document.addEvents({
			keydown: this.bound.keyDown,
			keyup: this.bound.keyUp
		});
    },
    
	blurOnClick: function(event){
		var target = event.target;
		while(target){
			if(target == this.container) return;
			target = target.parentNode;
		}
		this.blur();
	},
    
	focus: function(){
		if(Mif.Focus && Mif.Focus == this) return this;
		if(Mif.Focus) Mif.Focus.blur();
		Mif.Focus = this;
		this.focused = true;
		this.container.addClass('mif-tree-focused');
		return this.fireEvent('focus');
	},
    
	blur: function(){
		Mif.Focus = null;
		if(!this.focused) return this;
		this.focused = false;
		this.container.removeClass('mif-tree-focused');
		return this.fireEvent('blur');
	},
	
	$getIndex: function(){//return array of visible nodes.
		this.$index = [];
		var node = this.forest ? this.root.getFirst() : this.root;
		var previous = node;
		while(node){
			if(!(previous.hidden && previous.contains(node))){
				if(!node.hidden) this.$index.push(node);
				previous = node;
			}
			node = node._getNextVisible();
		}
	},
	
	preventDefault: function(event){
		event.preventDefault();
	},
	
	mousedown: function(event){
		if(event.rightClick) return;
		event.preventDefault();
		this.fireEvent('mousedown');
	},
	
	mouseleave: function(){
		this.mouse.coords = {x: null,y: null};
		this.mouse.target = false;
		this.mouse.node = false;
		if(this.hover) this.hover();
	},
	
	mouse: function(event){
		this.mouse.coords = this.getCoords(event);
		var target = this.getTarget(event);
		this.mouse.target = target.target;
		this.mouse.node	= target.node;
	},
	
	getTarget: function(event){
		var target = event.target, node;
		while(!(/mif-tree/).test(target.className)){
			target = target.parentNode;
		}
		var test = target.className.match(/mif-tree-(gadjet)-[^n]|mif-tree-(icon)|mif-tree-(name)|mif-tree-(checkbox)/);
		if(!test){
			var y = this.mouse.coords.y;
			if(y == -1||!this.$index) {
				node = false;
			}else{
				node = this.$index[((y)/this.height).toInt()];
			}
			return {
				node: node,
				target: 'node'
			};
		}
		for(var i = 5; i > 0; i--){
			if(test[i]){
				var type = test[i];
				break;
			}
		}
		return {
			node: Mif.Tree.Nodes[target.getAttribute('uid')],
			target: type
		};
	},
	
	getCoords: function(event){
		var position = this.wrapper.getPosition();
		var x = event.page.x - position.x;
		var y = event.page.y - position.y;
		var wrapper = this.wrapper;
		if((y-wrapper.scrollTop > wrapper.clientHeight)||(x - wrapper.scrollLeft > wrapper.clientWidth)){//scroll line
			y = -1;
		};
		return {x: x, y: y};
	},
	
	keyDown: function(event){
		this.key = event;
		this.key.state = 'down';
		if(this.focused) this.fireEvent('keydown', [event]);
	},
	
	keyUp: function(event){
		this.key = {};
		this.key.state = 'up';
		if(this.focused) this.fireEvent('keyup', [event]);
	},
	
	toggleDblclick: function(event){
		var target = this.mouse.target;
		if(!(target == 'name' || target == 'icon')) return;
		this.mouse.node.toggle();
	},
	
	toggleClick: function(event){
		if(this.mouse.target != 'gadjet') return;
		this.mouse.node.toggle();
	},
	
	initScroll: function(){
		this.scroll = new Fx.Scroll(this.wrapper, {link: 'cancel'});
	},
	
	scrollTo: function(node){
		var position = node.getVisiblePosition();
		var top = position*this.height;
		var up = (top < this.wrapper.scrollTop);
		var down = (top > (this.wrapper.scrollTop + this.wrapper.clientHeight - this.height));
		if(position == -1 || ( !up && !down ) ) {
			this.scroll.fireEvent('complete');
			return false;
		}
		if(this.animateScroll){
			this.scroll.start(this.wrapper.scrollLeft, top - (down ? this.wrapper.clientHeight - this.height : this.height));
		}else{
			this.scroll.set(this.wrapper.scrollLeft, top - (down ? this.wrapper.clientHeight - this.height : this.height));
			this.scroll.fireEvent('complete');
		}
		return this;
	},
	
	updateOpenState: function(){
		this.addEvents({
			'drawChildren': function(parent){
				var children = parent.children;
				for(var i = 0, l = children.length; i < l; i++){
					children[i].updateOpenState();
				}
			},
			'drawRoot': function(){
				this.root.updateOpenState();
			}
		});
	},
	
	expandTo: function(node){
		if (!node) return this;
		var path = [];
		while( !node.isRoot() && !(this.forest && node.getParent().isRoot()) ){
			node = node.getParent();
			if(!node) break;
			path.unshift(node);
		};
		path.each(function(el){
			el.toggle(true);
		});
		return this;
	},
	
	initExpandTo: function(){
		this.addEvent('loadChildren', function(parent){
			if(!parent) return;
			var children = parent.children;
			for( var i = children.length; i--; ){
				var child = children[i];
				if(child.expandTo) this.expanded.push(child);
			}
		});
		function expand(){
			this.expanded.each(function(node){
				this.expandTo(node);
			}, this);
			this.expanded = [];
		};
		this.addEvents({
			'load': expand.bind(this),
			'loadNode': expand.bind(this)
		});
	}
	
});
Mif.Tree.UID = 0;

Array.implement({
	
	inject: function(added, current, where){//inject added after or before current;
		var pos = this.indexOf(current) + (where == 'before' ? 0 : 1);
		for(var i = this.length-1; i >= pos; i--){
			this[i + 1] = this[i];
		}
		this[pos] = added;
		return this;
	}
	
});


/*
---
 
name: Mif.Tree.Node
description: Mif.Tree.Node
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Node
 
...
*/

Mif.Tree.Node = new Class({

	Implements: [Events],
	
	initialize: function(structure, options) {
		$extend(this, structure);
		this.children = [];
		this.type = options.type || this.tree.dfltType;
		this.property = options.property || {};
		this.data = options.data;
		this.state = $extend($unlink(this.tree.dfltState), options.state);
		this.$calculate();
		this.UID = Mif.Tree.Node.UID++;
		Mif.Tree.Nodes[this.UID] = this;
		var id = this.id;
		if(id != null) Mif.ids[id] = this;
		this.tree.fireEvent('nodeCreate', [this]);
		this._property = ['id', 'name', 'cls', 'openIcon', 'closeIcon', 'openIconUrl', 'closeIconUrl', 'hidden'];
	},
	
	$calculate: function(){
		$extend(this, $unlink(this.tree.defaults));
		this.type = $splat(this.type);
		this.type.each(function(type){
			var props = this.tree.types[type];
			if(props) $extend(this, props);
		}, this);
		$extend(this, this.property);
		return this;
	},
	
	getDOM: function(what){
		var node = $(this.tree.DOMidPrefix+this.UID);
		if(what == 'node') return node;
		var wrapper = node.getFirst();
		if(what == 'wrapper') return wrapper;
		if(what == 'children') return wrapper.getNext();
		return wrapper.getElement('.mif-tree-'+what);
	},
	
	getGadjetType: function(){
		return (this.loadable && !this.isLoaded()) ? 'plus' : (this.hasVisibleChildren() ? (this.isOpen() ? 'minus' : 'plus') : 'none');
	},
	
	toggle: function(state) {
		if(this.state.open == state || this.$loading || this.$toggling) return this;
		var parent = this.getParent();
		function toggle(type){
			this.state.open = !this.state.open;
			if(type == 'drawed'){
				this.drawToggle();
			}else{
				parent._toggle = (parent._toggle||[])[this.state.open ? 'include' : 'erase'](this);
			}
			this.fireEvent('toggle', [this.state.open]);
			this.tree.fireEvent('toggle', [this, this.state.open]);
			return this;
		}
		if(parent && !parent.$draw){
			return toggle.apply(this, []);
		}
		if(this.loadable && !this.state.loaded) {
            if(!this.load_event){
                this.load_event = true;
                this.addEvent('load',function(){
                    this.toggle();
                }.bind(this));
            }
            return this.load();
        }
		if(!this.hasChildren()) return this;
		return toggle.apply(this, ['drawed']);
	},
	
	drawToggle: function(){
		this.tree.$getIndex();
		Mif.Tree.Draw.update(this);
	},
	
	recursive: function(fn, args){
		args=$splat(args);
		if(fn.apply(this, args) !== false){
			this.children.each(function(node){
				if(node.recursive(fn, args) === false){
					return false;
				}
			});
		}
		return this;
	},
	
	isOpen: function(){
		return this.state.open;
	},
	
	isLoaded: function(){
		return this.state.loaded;
	},
	
	isLast: function(){
		if(this.parentNode == null || this.parentNode.children.getLast() == this) return true;
		return false;
	},
	
	isFirst: function(){
		if(this.parentNode == null || this.parentNode.children[0] == this) return true;
		return false;
	},
	
	isRoot: function(){
		return this.parentNode == null ? true : false;
	},
	
	getChildren: function(){
		return this.children;
	},
	
	hasChildren: function(){
		return this.children.length ? true : false;
	},
	
	index: function(){
		if( this.isRoot() ) return 0;
		return this.parentNode.children.indexOf(this);
	},
	
	getNext: function(){
		if(this.isLast()) return null;
		return this.parentNode.children[this.index()+1];
	},
	
	getPrevious: function(){
		if( this.isFirst() ) return null;
		return this.parentNode.children[this.index()-1];
	},
	
	getFirst: function(){
		if(!this.hasChildren()) return null;
		return this.children[0];
	},
	
	getLast: function(){
		if(!this.hasChildren()) return null;
		return this.children.getLast();		
	},
	
	getParent: function(){
		return this.parentNode;
	},
	
	_getNextVisible: function(){
		var current=this;
		if(current.isRoot()){
			if(!current.isOpen() || !current.hasChildren(true)) return false;
			return current.getFirst(true);
		}else{
			if(current.isOpen() && current.getFirst(true)){
				return current.getFirst(true);
			}else{
				var parent = current;
				do{
					current = parent.getNext(true);
					if(current) return current;
					parent = parent.parentNode;
				}while(parent);
				return false;
			}
		}
	},
	
	getPreviousVisible: function(){
		var index = this.tree.$index.indexOf(this);
		return index == 0 ? null : this.tree.$index[index-1];
	},
	
	getNextVisible: function(){
		var index = this.tree.$index.indexOf(this);
		return index < this.tree.$index.length-1 ? this.tree.$index[index+1] : null;
	},
	
	getVisiblePosition: function(){
		return this.tree.$index.indexOf(this);
	},
	
	hasVisibleChildren: function(){
		if(!this.hasChildren()) return false;
		if(this.isOpen()){
			var next = this.getNextVisible();
			if(!next) return false;
			if(next.parentNode != this) return false;
			return true;
		}else{
			var child = this.getFirst();
			while(child){
				if(!child.hidden) return true;
				child = child.getNext();
			}
			return false;
		}
	},
	
	isLastVisible: function(){
		var next = this.getNext();
		while(next){
			if(!next.hidden) return false;
			next = next.getNext();
		};
		return true;
	},
		
	contains: function(node){
		while(node){
			if(node == this) return true;
			node = node.parentNode;
		};
		return false;
	},

	addType: function(type){
		return this.processType(type, 'add');
	},

	removeType: function(type){
		return this.processType(type, 'remove');
	},
	
	setType: function(type){
		return this.processType(type, 'set');
	},
	
	processType: function(type, action){
		switch(action){
			case 'add': this.type.include(type); break;
			case 'remove': this.type.erase(type); break;
			case 'set': this.type = type; break;
		}
		var current = {};
		this._property.each(function(p){
			current[p] = this[p];
		}, this);
		this.$calculate();
		this._property.each(function(p){
			this.updateProperty(p, current[p], this[p]);
		}, this);
		return this;
	},
	
	set: function(obj){
		this.tree.fireEvent('beforeSet', [this, obj]);
		var property = obj.property||obj||{};
		for(var p in property){
			var nv = property[p];
			var cv = this[p];
			this.updateProperty(p, cv, nv);
			this[p] = this.property[p] = nv;
		}
		this.tree.fireEvent('set', [this, obj]);
		return this;
	},
	
	updateProperty: function(p, cv, nv){
		if(nv == cv) return this;
		if(p == 'id'){
			delete Mif.ids[cv];
			if(nv) Mif.ids[nv]=this;
			return this;
		}
		if(!Mif.Tree.Draw.isUpdatable(this)) return this;
		switch(p){
			case 'name':
				this.getDOM('name').set('html', nv);
				return this;
			case 'cls':
				this.getDOM('wrapper').removeClass(cv).addClass(nv);
				return this;
			case 'openIcon':
			case 'closeIcon':
				this.getDOM('icon').removeClass(cv).addClass(nv);
				return this;
			case 'openIconUrl':
			case 'closeIconUrl':
				var icon = this.getDOM('icon');
				icon.setStyle('background-image', 'none');
				if(nv) icon.setStyle('background-image', 'url('+nv+')');
				return this;
			case 'hidden':
				this.getDOM('node').setStyle('display', nv ? 'none' : 'block');
				var _previous = this.getPreviousVisible();
				var _next = this.getNextVisible();
				var parent = this.getParent();
				this[p] = this.property[p]=nv;
				this.tree.$getIndex();
				var previous = this.getPreviousVisible();
				var next = this.getNextVisible();
				[_previous, _next, previous, next, parent, this].each(function(node){
					Mif.Tree.Draw.update(node);
				});
				return this;
		}
		return this;
	},
	
	updateOpenState: function(){
		if(this.state.open){
			this.state.open = false;
			this.toggle();
		}
	}
	
});

Mif.Tree.Node.UID = 0;
Mif.Tree.Nodes = {};

/*
---
 
name: Mif.Tree.Draw
description: convert javascript tree object to html
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Draw
 
...
*/

Mif.Tree.Draw = {

	getHTML: function(node,html){
		var prefix = node.tree.DOMidPrefix;
		var checkbox;
		if($defined(node.state.checked)){
			if(!node.hasCheckbox) node.state.checked='nochecked';
			checkbox = '<span class="mif-tree-checkbox mif-tree-node-'+node.state.checked+'" uid="'+node.UID+'">'+Mif.Tree.Draw.zeroSpace+'</span>';
		}else{
			checkbox = '';
		}
		html = html||[];
		html.push(
		'<div class="mif-tree-node ',(node.isLast() ? 'mif-tree-node-last' : ''),'"'+(node.hidden ? ' style="display:none"' : '')+' id="',prefix,node.UID,'">',
			'<span class="mif-tree-node-wrapper ',node.cls,(node.state.selected ? ' mif-tree-node-selected' : ''),'" uid="',node.UID,'">',
				'<span class="mif-tree-gadjet mif-tree-gadjet-',node.getGadjetType(),'" uid="',node.UID,'">',Mif.Tree.Draw.zeroSpace,'</span>',
				checkbox,
				'<span class="mif-tree-icon ',(node.closeIconUrl?'" style="background-image: url('+node.closeIconUrl+')" ': node.closeIcon+'"'),' uid="',node.UID,'">',Mif.Tree.Draw.zeroSpace,'</span>',
				'<span class="mif-tree-name" uid="',node.UID,'">',node.name,'</span>',
			'</span>',
			'<div class="mif-tree-children" style="display:none"></div>',
		'</div>'
		);
		return html;
	},
	
	children: function(parent, container){
		parent.open = true;
		parent.$draw = true;
		var html = [];
		var children = parent.children;
		for(var i = 0, l = children.length; i < l; i++){
			this.getHTML(children[i], html);
		}
		container = container || parent.getDOM('children');
		container.set('html', html.join(''));
		parent.tree.fireEvent('drawChildren',[parent]);
	},
	
	root: function(tree){
		var domRoot = this.node(tree.root);
		domRoot.inject(tree.wrapper);
		tree.$draw = true;
		tree.fireEvent('drawRoot');
	},
	
	forestRoot: function(tree){
		var container = new Element('div').addClass('mif-tree-children-root').injectInside(tree.wrapper);
		Mif.Tree.Draw.children(tree.root, container);
	},
	
	node: function(node){
		return new Element('div').set('html', this.getHTML(node).join('')).getFirst();
	},
	
	isUpdatable: function(node){
		if(
			(!node||!node.tree) ||
			(node.getParent() && !node.getParent().$draw) || 
			(node.isRoot() && (!node.tree.$draw||node.tree.forest)) 
		) return false;
		return true;
	},
	
	update: function(node){
		if(!this.isUpdatable(node)) return null;
		if(!node.hasChildren()) node.state.open = false;
		node.getDOM('gadjet').className = 'mif-tree-gadjet mif-tree-gadjet-'+node.getGadjetType();
		if (node.closeIconUrl) {
			node.getDOM('icon').setStyle('background-image', 'url('+(node.isOpen() ? node.openIconUrl : node.closeIconUrl)+')');
		} else {
			node.getDOM('icon').className = 'mif-tree-icon '+node[node.isOpen() ? 'openIcon' : 'closeIcon'];
		}
		node.getDOM('node')[(node.isLastVisible() ?'add' : 'remove')+'Class']('mif-tree-node-last');
		if(node.$loading) return null;
		var children = node.getDOM('children');
		if(node.isOpen()){
			if(!node.$draw) Mif.Tree.Draw.children(node);
			children.style.display = 'block';
		}else{
			children.style.display = 'none';
		}
		node.tree.fireEvent('updateNode', node);
		return node;
	},
	
	inject: function(node, element){
		if(!this.isUpdatable(node)) return;
		element = element || node.getDOM('node') || this.node(node);
		var previous = node.getPrevious();
		if(previous){
			element.injectAfter(previous.getDOM('node'));
			return;
		}
		var container;
		if(node.tree.forest && node.parentNode.isRoot()){
			container = node.tree.wrapper.getElement('.mif-tree-children-root');
		}else if(node.tree.root == node){
			container = node.tree.wrapper;
		}else{
			container = node.parentNode.getDOM('children');
		}
		element.inject(container, 'top');
	}
	
};

Mif.Tree.Draw.zeroSpace = Browser.Engine.trident ? '&shy;' : (Browser.Engine.webkit ? '&#8203' : '');



/*
---
 
name: Mif.Tree.Selection
description: tree nodes selection
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Selection
 
...
*/

Mif.Tree.implement({
	
	initSelection: function(){
		this.defaults.selectClass = '';
		this.wrapper.addEvent('mousedown', this.attachSelect.bindWithEvent(this));
	},
	
	attachSelect: function(event){
		if(!['icon', 'name', 'node'].contains(this.mouse.target)) return;
		var node = this.mouse.node;
		if(!node) return;
		this.select(node);
	},
	
	select: function(node) {
		if(!node) return this;
		var current = this.selected;
		if (current == node) return this;
		if (current) {
			current.select(false);
			this.fireEvent('unSelect', [current]).fireEvent('selectChange', [current, false]);
		}
		this.selected = node;
		node.select(true);
		this.fireEvent('select', [node]).fireEvent('selectChange', [node, true]);
		return this;
	},
	
	unselect: function(){
		var current = this.selected;
		if(!current) return this;
		this.selected = false;
		current.select(false);
		this.fireEvent('unSelect', [current]).fireEvent('selectChange', [current, false]);
		return this;
	},
	
	getSelected: function(){
		return this.selected;
	},
	
	isSelected: function(node){
		return node.isSelected();
	}
	
});

Mif.Tree.Node.implement({
		
	select: function(state) {
		this.state.selected = state;
		if(!Mif.Tree.Draw.isUpdatable(this)) return;
		var wrapper=this.getDOM('wrapper');
		wrapper[(state ? 'add' : 'remove')+'Class'](this.selectClass||'mif-tree-node-selected');
	},
	
	isSelected: function(){
		return this.state.selected;
	}
	
});


/*
---
 
name: Mif.Tree.Hover
description: hover(mouseover/mouseout) events/effects
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Hover
 
...
*/

Mif.Tree.implement({
	
	initHover: function(){
		this.defaults.hoverClass = '';
		this.wrapper.addEvent('mousemove', this.hover.bind(this));
		this.wrapper.addEvent('mouseout', this.hover.bind(this));
		this.defaultHoverState = {
			gadjet: false,
			checkbox: false,
			icon: false,
			name: false,
			node: false
		};
		this.hoverState = $unlink(this.defaultHoverState);
	},
	
	hover: function(){
		var cnode = this.mouse.node;
		var ctarget = this.mouse.target;
		$each(this.hoverState, function(node, target, state){
			if(node == cnode && (target == 'node'||target==ctarget)) return;
			if(node) {
				Mif.Tree.Hover.out(node, target);
				state[target] = false;
				this.fireEvent('hover', [node, target, 'out']);
			}
			if(cnode && (target == 'node'||target == ctarget)) {
				Mif.Tree.Hover.over(cnode, target);
				state[target] = cnode;
				this.fireEvent('hover', [cnode, target, 'over']);
			}else{
				state[target] = false;
			}
		}, this);
	},
	
	updateHover: function(){
		this.hoverState = $unlink(this.defaultHoverState);
		this.hover();
	}
	
});

Mif.Tree.Hover = {
	
	over: function(node, target){
		var wrapper = node.getDOM('wrapper');
		wrapper.addClass((node.hoverClass||'mif-tree-hover')+'-'+target);
		if(node.state.selected) wrapper.addClass((node.hoverClass||'mif-tree-hover')+'-selected-'+target);
	},
	
	out: function(node, target){
		var wrapper = node.getDOM('wrapper');
		wrapper.removeClass((node.hoverClass||'mif-tree-hover')+'-'+target).removeClass((node.hoverClass||'mif-tree-hover')+'-selected-'+target);
	}
	
};


/*
---
 
name: Mif.Tree.Load
description: load tree from json
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Load
 
...
*/

Mif.Tree.Load = {
		
	children: function(children, parent, tree){
	    var i, l;
	    var subChildrens = [];
		for(i = children.length; i--; ){
			var child = children[i];
			var node = new Mif.Tree.Node({
				tree: tree,
				parentNode: parent||undefined
			}, child);
			if( tree.forest || parent != undefined){
				parent.children.unshift(node);
			}else{
				tree.root = node;
			}
			var subChildren = child.children;
			if(subChildren && subChildren.length){
			    subChildrens.push({children: subChildren, parent: node});
			}
		}
		for(i = 0, l = subChildrens.length; i < l; i++) {
		    var sub = subChildrens[i];
		    arguments.callee(sub.children, sub.parent, tree);
		}
		if(parent) parent.state.loaded = true;
		tree.fireEvent('loadChildren', parent);
	}
	
};

Mif.Tree.implement({

	load: function(options){
		var tree = this;
		this.loadOptions = this.loadOptions||$lambda({});
		function success(json){
			var parent = null;
			if(tree.forest){
				tree.root = new Mif.Tree.Node({
					tree: tree,
					parentNode: null
				}, {});
				parent = tree.root;
			}
			Mif.Tree.Load.children(json, parent, tree);
			Mif.Tree.Draw[tree.forest ? 'forestRoot' : 'root'](tree);
			tree.$getIndex();
			tree.fireEvent('load');
			return tree;
		}
		options = $extend($extend({
			isSuccess: $lambda(true),
			secure: true,
			onSuccess: success,
			method: 'get'
		}, this.loadOptions()), options);
		if(options.json) return success(options.json);
		new Request.JSON(options).send();
		return this;
	}
	
});

Mif.Tree.Node.implement({
	
	load: function(options){
		this.$loading = true;
		options = options||{};
		this.addType('loader');
		var self = this;
		function success(json){
			Mif.Tree.Load.children(json, self, self.tree);
			delete self.$loading;
			self.state.loaded = true;
			self.removeType('loader');
			Mif.Tree.Draw.update(self);
			self.fireEvent('load');
			self.tree.fireEvent('loadNode', self);
			return self;
		}
		options=$extend($extend($extend({
			isSuccess: $lambda(true),
			secure: true,
			onSuccess: success,
			method: 'get'
		}, this.tree.loadOptions(this)), this.loadOptions), options);
		if(options.json) return success(options.json);
		new Request.JSON(options).send();
		return this;
	}
	
});


/*
---
 
name: Mif.Tree.KeyNav
description: Mif.Tree.KeyNav
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.KeyNav
 
...
*/

Mif.Tree.KeyNav=new Class({
	
	initialize: function(tree){
		this.tree = tree;
		this.bound = {
			action: this.action.bind(this),
			attach: this.attach.bind(this),
			detach: this.detach.bind(this)
		};
		tree.addEvents({
			'focus': this.bound.attach,
			'blur': this.bound.detach
		});
	},
	
	attach: function(){
		var event = Browser.Engine.trident || Browser.Engine.webkit ? 'keydown' : 'keypress';
		document.addEvent(event, this.bound.action);
	},
	
	detach: function(){
		var event = Browser.Engine.trident || Browser.Engine.webkit ? 'keydown' : 'keypress';
		document.removeEvent(event, this.bound.action);
	},
	
	action: function(event){
		if(!['down','left','right','up', 'pgup', 'pgdown', 'end', 'home'].contains(event.key)) return;
		var tree = this.tree;
		if(!tree.selected){
			tree.select(tree.forest ? tree.root.getFirst() : tree.root);
		}else{
			var current = tree.selected;
			switch (event.key){
				case 'down': this.goForward(current); event.stop(); break;  
				case 'up': this.goBack(current); event.stop(); break;   
				case 'left': this.goLeft(current); event.stop(); break;
				case 'right': this.goRight(current); event.stop(); break;
				case 'home': this.goStart(current); event.stop(); break;
				case 'end': this.goEnd(current); event.stop(); break;
				case 'pgup': this.goPageUp(current); event.stop(); break;
				case 'pgdown': this.goPageDown(current); event.stop(); break;
			}
		}
		tree.scrollTo(tree.selected);
	},

	goForward: function(current){
		var forward = current.getNextVisible();
		if(forward) this.tree.select(forward);
	},
	
	goBack: function(current){
		var back = current.getPreviousVisible();
		if (back) this.tree.select(back);
	},
	
	goLeft: function(current){
		if(current.isRoot()){
			if(current.isOpen()){
				current.toggle();
			}else{
				return false;
			}
		}else{
			if( current.hasChildren(true) && current.isOpen() ){
				current.toggle();
			}else{
				if(current.tree.forest && current.getParent().isRoot()) return false;
				return this.tree.select(current.getParent());
			}
		}
		return true;
	},
	
	goRight: function(current){
		if(!current.hasChildren(true) && !current.loadable){
			return false;
		}else if(!current.isOpen()){
			return current.toggle();
		}else{
			return this.tree.select(current.getFirst(true));
		}
	},
	
	goStart: function(){
		this.tree.select(this.tree.$index[0]);
	},
	
	goEnd: function(){
		this.tree.select(this.tree.$index.getLast());
	},
	
	goPageDown: function(current){
		var tree = this.tree;
		var count = (tree.container.clientHeight/tree.height).toInt() - 1;
		var newIndex = Math.min(tree.$index.indexOf(current) + count, tree.$index.length - 1);
		tree.select(tree.$index[newIndex]);
	},
	
	goPageUp: function(current){
		var tree = this.tree;
		var count = (tree.container.clientHeight/tree.height).toInt() - 1;
		var newIndex = Math.max(tree.$index.indexOf(current) - count, 0);
		tree.select(tree.$index[newIndex]);
	}
	
});

Event.Keys.extend({
	'pgdown': 34,
	'pgup': 33,
	'home': 36,
	'end': 35
});


/*
---

name: Mif.Tree.Sort
description: Mif.Tree.Sort
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Sort

...
*/

Mif.Tree.implement({
	
	initSortable: function(sortFunction){
		this.sortable = true;
		this.sortFunction = sortFunction||function(node1, node2){
			if(node1.name > node2.name){
				return 1;
			}else if(node1.name < node2.name){
				return -1;
			}else{
				return 0;
			}
		};
		this.addEvent('loadChildren', function(parent){
			if(parent) parent.sort();
		});
		this.addEvent('structureChange', function(from, to, where, type){
			from.sort();
		});
		return this;
	}
	
});


Mif.Tree.Node.implement({

	sort: function(sortFunction){
		this.children.sort(sortFunction||this.tree.sortFunction);
		return this;
	}
	
});


/*
---
 
name: Mif.Tree.Transform
description: implement move/copy/del/add actions
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Transform
 
...
*/

Mif.Tree.Node.implement({
	
	inject: function(node, where, element){//element - internal property
		where = where||'inside';
		var parent = this.parentNode;
		function getPreviousVisible(node){
			var previous = node;
			while(previous){
				previous = previous.getPrevious();
				if(!previous) return null;
				if(!previous.hidden) return previous;
			}
			return null;
		}
		var previousVisible = getPreviousVisible(this);
		var type = element ? 'copy' : 'move';
		switch(where){
			case 'after':
			case 'before':
				if( node['get' + (where == 'after' ? 'Next' : 'Previous')]() == this ) return false;
				if(this.parentNode) {
					this.parentNode.children.erase(this);
				}
				this.parentNode = node.parentNode;
				this.parentNode.children.inject(this, node, where);
				break;
			case 'inside':
				if( node.tree && node.getLast() == this ) return false;
				if(this.parentNode) {
					this.parentNode.children.erase(this);
				}
				if(node.tree){
					if(!node.hasChildren()){
						node.$draw = true;
						node.state.open = true;
					}
					node.children.push(this);
					this.parentNode = node;
				}else{
					node.root = this;
					this.parentNode = null;
					node.fireEvent('drawRoot');
				}
				break;
		}		
		var tree = node.tree || node;
		if(this == this.tree.root){
			this.tree.root = false;
		}
		if(this.tree != tree){
			var oldTree = this.tree;
			this.recursive(function(){
				this.tree = tree;
			});
		};
		tree.fireEvent('structureChange', [this, node, where, type]);
		tree.$getIndex();
		if(oldTree)	oldTree.$getIndex();
		Mif.Tree.Draw.inject(this, element);
		[node, this, parent, previousVisible, getPreviousVisible(this)].each(function(node){
			Mif.Tree.Draw.update(node);
		});
		return this;
	},
	
	copy: function(node, where){
		if (this.copyDenied) return this;
		function copy(structure){
			var node = structure.node;
			var tree = structure.tree;
			var options = $unlink({
				property: node.property,
				type: node.type,
				state: node.state,
				data: node.data
			});
			options.state.open = false;
			var nodeCopy = new Mif.Tree.Node({
				parentNode: structure.parentNode,
				children: [],
				tree: tree
			}, options);
			node.children.each(function(child){
				var childCopy = copy({
					node: child,
					parentNode: nodeCopy,
					tree: tree
				});
				nodeCopy.children.push(childCopy);
			});
			return nodeCopy;
		};
		
		var nodeCopy = copy({
			node: this,
			parentNode: null,
			tree: node.tree
		});
		return nodeCopy.inject(node, where, Mif.Tree.Draw.node(nodeCopy));
	},
	
	remove: function(){
		if (this.removeDenied) return;
		this.tree.fireEvent('remove', [this]);
		var parent = this.parentNode, previousVisible = this.getPreviousVisible();
		if(parent) {	
			parent.children.erase(this);
		}else if(!this.tree.forest){
			this.tree.root = null;
		}
		this.tree.selected = false;
		this.getDOM('node').destroy();
		this.tree.$getIndex();
		Mif.Tree.Draw.update(parent);
		Mif.Tree.Draw.update(previousVisible);
		this.recursive(function(){
			if(this.id) delete Mif.ids[this.id];
		});
		this.tree.mouse.node = false;
		this.tree.updateHover();
	}
	
});


Mif.Tree.implement({

	move: function(from, to, where){
		if(from.inject(to, where)){
			this.fireEvent('move', [from, to, where]);
		}
		return this;
	},
	
	copy: function(from, to, where){
		var copy = from.copy(to, where);
		if(copy){
			this.fireEvent('copy', [from, to, where, copy]);
		}
		return this;
	},
	
	remove: function(node){
		node.remove();
		return this;
	},
	
	add: function(node, current, where){
		if(!(node instanceof Mif.Tree.Node)){
			node = new Mif.Tree.Node({
				parentNode: null,
				tree: this
			}, node);
		};
		node.inject(current, where, Mif.Tree.Draw.node(node));
		this.fireEvent('add', [node, current, where]);
		return this;
	}
	
});


/*
---
 
name: Mif.Tree.Drag
description: implements drag and drop
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: [Mif.Tree, Mif.Tree.Transform, more:/Drag.Move]
provides: Mif.Tree.Drag
 
...
*/

Mif.Tree.Drag = new Class({
	
	Implements: [ Events, Options],
	
	Extends: Drag,
	
	options:{
		group: 'tree',
		droppables: [],
		snap: 4,
		animate: true,
		open: 600,//time to open node
		scrollDelay: 100,
		scrollSpeed: 100,
		modifier: 'control',//copy
		startPlace: ['icon', 'name'],
		allowContainerDrop: true
	},

	initialize: function(tree, options){
		tree.drag = this;
		this.setOptions(options);
		$extend(this, {
			tree: tree,
			snap: this.options.snap,
			groups: [],
			droppables: [],
			action: this.options.action
		});
		
		this.addToGroups(this.options.group);
		
		this.setDroppables(this.options.droppables);
		
		$extend(tree.defaults, {
			dropDenied: [],
			dragDisabled: false
		});
		tree.addEvent('drawRoot',function(){
			tree.root.dropDenied.combine(['before', 'after']);
		});
		
		this.pointer = new Element('div').addClass('mif-tree-pointer').injectInside(tree.wrapper);
		
		this.current = Mif.Tree.Drag.current;
		this.target = Mif.Tree.Drag.target;
		this.where = Mif.Tree.Drag.where;

		this.element = [this.current, this.target, this.where];
		this.document = tree.wrapper.getDocument();
		
		this.selection = (Browser.Engine.trident) ? 'selectstart' : 'mousedown';
		
		this.bound = {
			start: this.start.bind(this),
			check: this.check.bind(this),
			drag: this.drag.bind(this),
			stop: this.stop.bind(this),
			cancel: this.cancel.bind(this),
			eventStop: $lambda(false),
			leave: this.leave.bind(this),
			enter: this.enter.bind(this),
			keydown: this.keydown.bind(this)
		};
		this.attach();
		
		this.addEvent('start', function(){
			Mif.Tree.Drag.dropZone=this;
			this.tree.unselect();
			document.addEvent('keydown', this.bound.keydown);
			this.setDroppables();
			this.droppables.each(function(item){
				item.getElement().addEvents({mouseleave: this.bound.leave, mouseenter: this.bound.enter});
			}, this);
			Mif.Tree.Drag.current.getDOM('name').addClass('mif-tree-drag-current');
			this.addGhost();
		}, true);
		this.addEvent('complete', function(){
			document.removeEvent('keydown', this.bound.keydown);
			this.droppables.each(function(item){
				item.getElement().removeEvent('mouseleave', this.bound.leave).removeEvent('mouseenter', this.bound.enter);
			}, this);
			Mif.Tree.Drag.current.getDOM('name').removeClass('mif-tree-drag-current');
			var dropZone = Mif.Tree.Drag.dropZone;
			if(!dropZone || dropZone.where=='notAllowed'){
				Mif.Tree.Drag.startZone.onstop();
				Mif.Tree.Drag.startZone.emptydrop();
				return;
			}
			if(dropZone.onstop) dropZone.onstop();
			dropZone.beforeDrop();
		});
	},
	
	getElement: function(){
		return this.tree.wrapper;
	},
	
	addToGroups: function(groups){
		groups = $splat(groups);
		this.groups.combine(groups);
		groups.each(function(group){
			Mif.Tree.Drag.groups[group]=(Mif.Tree.Drag.groups[group]||[]).include(this);
		}, this);
	},
	
	setDroppables: function(droppables){
		this.droppables.combine($splat(droppables));
		this.groups.each(function(group){
			this.droppables.combine(Mif.Tree.Drag.groups[group]);
		}, this);
	},

	attach: function(){
		this.tree.wrapper.addEvent('mousedown', this.bound.start);
		return this;
	},

	detach: function(){
		this.tree.wrapper.removeEvent('mousedown', this.bound.start);
		return this;
	},
	
	dragTargetSelect: function(){
		function addDragTarget(){
			this.current.getDOM('name').addClass('mif-tree-drag-current');
		}
		function removeDragTarget(){
			this.current.getDOM('name').removeClass('mif-tree-drag-current');
		}
		this.addEvent('start',addDragTarget.bind(this));
		this.addEvent('beforeComplete',removeDragTarget.bind(this));
	},
	
	leave: function(event){
		var dropZone = Mif.Tree.Drag.dropZone;
		if(dropZone){
			dropZone.where = 'notAllowed';
			Mif.Tree.Drag.ghost.firstChild.className = 'mif-tree-ghost-icon mif-tree-ghost-'+dropZone.where;
			if(dropZone.onleave) dropZone.onleave();
			Mif.Tree.Drag.dropZone = false;
		}
		
		var relatedZone = this.getZone(event.relatedTarget);
		if(relatedZone) this.enter(null, relatedZone);
	},
	
	onleave: function(){
		this.tree.unselect();
		this.clean();
		$clear(this.scrolling);
		this.scrolling = null;
		this.target = false;
	},
	
	enter: function(event, zone){
		if(event) zone = this.getZone(event.target);
		var dropZone = Mif.Tree.Drag.dropZone;
		if(dropZone && dropZone.onleave) dropZone.onleave();
		Mif.Tree.Drag.dropZone = zone;
		zone.current = Mif.Tree.Drag.current;
		if(zone.onenter) zone.onenter();
	},
	
	onenter: function(){
		this.onleave();
	},
	
	getZone: function(target){//private leave/enter
		if(!target) return false;
		var parent = $(target);
		do{
			for(var l = this.droppables.length;l--;){
				var zone = this.droppables[l];
				if( parent == zone.getElement() ) {
					return zone;
				}
			}
			parent = parent.getParent();
		}while(parent);
		return false;
	},
	
	keydown: function(event){
		if(event.key == 'esc') {
			var zone = Mif.Tree.Drag.dropZone;
			if(zone) zone.where = 'notAllowed';
			this.stop(event);
		}
	},
	
	autoScroll: function(){
		var y = this.y;
		if(y == -1) return;
		var wrapper = this.tree.wrapper;
		var top = y-wrapper.scrollTop;
		var bottom = wrapper.offsetHeight-top;
		var sign = 0;
		var delta;
		if(top < this.tree.height){
			delta = top;
			sign = 1;
		}else if(bottom < this.tree.height){
			delta = bottom;
			sign = -1;
		}
		if(sign && !this.scrolling){
			this.scrolling = function(node){
				if(y != this.y){
					y = this.y;
					delta = (sign == 1 ? (y - wrapper.scrollTop) : (wrapper.offsetHeight - y + wrapper.scrollTop)) || 1;
				}
				wrapper.scrollTop = wrapper.scrollTop - sign*this.options.scrollSpeed/delta;
			}.periodical(this.options.scrollDelay, this, [sign]);
		}
		if(!sign){
			$clear(this.scrolling);
			this.scrolling = null;
		}
	},
	
	start: function(event){//mousedown
		if(event.rightClick) return;
		if (this.options.preventDefault) event.preventDefault();
		this.fireEvent('beforeStart', this.element);

		var target = this.tree.mouse.target;
		if(!target) return;
		this.current = $splat(this.options.startPlace).contains(target) ? this.tree.mouse.node : false;
		if(!this.current || this.current.dragDisabled) {
			return;
		}
		Mif.Tree.Drag.current = this.current;
		Mif.Tree.Drag.startZone = this;
		
		this.mouse = {start: event.page};
		this.document.addEvents({mousemove: this.bound.check, mouseup: this.bound.cancel});
		this.document.addEvent(this.selection, this.bound.eventStop);
	},
	
	drag: function(event){
		Mif.Tree.Drag.ghost.position({x:event.page.x+20,y:event.page.y+20});
		var dropZone = Mif.Tree.Drag.dropZone;
		if(!dropZone||!dropZone.ondrag) return;
		Mif.Tree.Drag.dropZone.ondrag(event);
	},

	ondrag: function(event){
		this.autoScroll();
		if(!this.checkTarget()) return;
		this.clean();
		var where = this.where;
		var target = this.target;
		var ghostType = where;
		if(where == 'after' && target && (target.getNext()) || where == 'before' && target.getPrevious()){
			ghostType = 'between';
		}
		Mif.Tree.Drag.ghost.firstChild.className = 'mif-tree-ghost-icon mif-tree-ghost-' + ghostType;
		if(where == 'notAllowed'){
			this.tree.unselect();
			return;
		}
		if(target && target.tree) this.tree.select(target);
		if(where == 'inside'){
			if(target.tree && !target.isOpen() && !this.openTimer && (target.loadable || target.hasChildren()) ){
				this.wrapper = target.getDOM('wrapper').setStyle('cursor', 'progress');
				this.openTimer = function(){
					target.toggle();
					this.clean();
				}.delay(this.options.open,this);
			}
		}else{
			var wrapper = this.tree.wrapper;
			var top = this.index*this.tree.height;
			if(where == 'after') top += this.tree.height;
			this.pointer.setStyles({
				left: wrapper.scrollLeft,
				top: top,
				width: wrapper.clientWidth
			});
		}
	},

	clean: function(){
		this.pointer.style.width = 0;
		if(this.openTimer){
			$clear(this.openTimer);
			this.openTimer = false;
			this.wrapper.style.cursor = 'inherit';
			this.wrapper = false;
		}
	},
	
	addGhost: function(){
		var wrapper = this.current.getDOM('wrapper');
		var ghost = new Element('span').addClass('mif-tree-ghost');
		ghost.adopt(Mif.Tree.Draw.node(this.current).getFirst())
		.injectInside(document.body).addClass('mif-tree-ghost-notAllowed').setStyle('position', 'absolute');
		new Element('span').set('html',Mif.Tree.Draw.zeroSpace).injectTop(ghost);
		ghost.getLast().getFirst().className = '';
		Mif.Tree.Drag.ghost = ghost;
	},
	
	checkTarget: function(){
		this.y = this.tree.mouse.coords.y;
		var target = this.tree.mouse.node;
		if(!target){
			if(this.options.allowContainerDrop && (this.tree.forest || !this.tree.root)){
				this.target = this.tree.$index.getLast();
				this.index = this.tree.$index.length-1;
				if(this.index == -1){
					this.where = 'inside';
					this.target = this.tree.root || this.tree;
				}else{
					this.where = 'after';
				}
			}else{
				this.target = false;
				this.where = 'notAllowed';
			}
			this.fireEvent('drag');
			return true;
		};
		if((this.current instanceof Mif.Tree.Node) && this.current.contains(target)){
			this.target = target;
			this.where = 'notAllowed';
			this.fireEvent('drag');
			return true;
		};
		this.index = Math.floor(this.y/this.tree.height);
		var delta = this.y - this.index*this.tree.height;
		var deny = target.dropDenied;
		if(this.tree.sortable){
			deny.include('before').include('after');
		};
		var where;
		if(!deny.contains('inside') && delta > (this.tree.height/4) && delta < (3/4*this.tree.height)){
			where = 'inside';
		}else{
			if(delta < this.tree.height/2){
				if(deny.contains('before')){
					if(deny.contains('inside')){
						where = deny.contains('after') ? 'notAllowed' : 'after';
					}else{
						where = 'inside';
					}
				}else{
					where = 'before';
				}
			}else{
				if(deny.contains('after')){
					if(deny.contains('inside')){
						where = deny.contains('before') ? 'notAllowed' : 'before';
					}else{
						where = 'inside';
					}
				}else{
					where = 'after';
				}
			}
		};
		if(this.where == where && this.target == target) return false;
		this.where = where; 
		this.target = target;
		this.fireEvent('drag');
		return true;
	},
	
	emptydrop: function(){
		var current = this.current, target = this.target, where = this.where;
		var scroll = this.tree.scroll;
		var complete = function(){
			scroll.removeEvent('complete', complete);
			if(this.options.animate){
				var wrapper = current.getDOM('wrapper');
				var position = wrapper.getPosition();
				Mif.Tree.Drag.ghost.set('morph',{
					duration: 'short',
					onComplete: function(){
						Mif.Tree.Drag.ghost.dispose();
						this.fireEvent('emptydrop', this.element);
					}.bind(this)
				});
				Mif.Tree.Drag.ghost.morph({left: position.x, top: position.y});
				return;
			};
			Mif.Tree.Drag.ghost.dispose();
			this.fireEvent('emptydrop', this.element);
			return;
		}.bind(this);
		scroll.addEvent('complete', complete);
		this.tree.select(this.current);
		this.tree.scrollTo(this.current);
	},
	
	beforeDrop: function(){
		if(this.options.beforeDrop){
			this.options.beforeDrop.apply(this, [this.current, this.target, this.where]);
		}else{
			this.drop();
		}
	},
	
	drop: function(){
		var current = this.current, target = this.target, where = this.where;
		Mif.Tree.Drag.ghost.dispose();
		var action = this.action || (this.tree.key[this.options.modifier] ? 'copy' : 'move');
		if(this.where == 'inside' && target.tree && !target.isOpen()){
			if(target.tree) target.toggle();
			if(target.$loading){
				var onLoad = function(){
					this.tree[action](current, target, where);
					this.tree.select(current).scrollTo(current);
					this.fireEvent('drop', [current, target, where]);
					target.removeEvent('load',onLoad);
				};
				target.addEvent('load',onLoad);
				return;
			};
		};
		if(!(current instanceof Mif.Tree.Node )){
			current = current.toNode(this.tree);
		}
		this.tree[action](current, target, where);
		this.tree.select(current).scrollTo(current);
		this.fireEvent('drop', [current, target, where]);
	},
	
	onstop: function(){
		this.clean();
		$clear(this.scrolling);
	}
});

Mif.Tree.Drag.groups={};


/*
---
 
name: Mif.Tree.Drag.Element
description: dom element droppable
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree.Drag
provides: Mif.Tree.Drag.Element
 
...
*/

Mif.Tree.Drag.Element=new Class({

	Implements: [Options, Events],

	initialize: function(element, options){
		
		this.element=$(element);
		
		this.setOptions(options);
		
	},
	
	getElement: function(){
		return this.element;
	},
	
	onleave: function(){
		this.where='notAllowed';
		Mif.Tree.Drag.ghost.firstChild.className='mif-tree-ghost-icon mif-tree-ghost-'+this.where;
	},
	
	onenter: function(){
		this.where='inside';
		Mif.Tree.Drag.ghost.firstChild.className='mif-tree-ghost-icon mif-tree-ghost-'+this.where;
	},
	
	beforeDrop: function(){
		if(this.options.beforeDrop){
			this.options.beforeDrop.apply(this, [this.current, this.trarget, this.where]);
		}else{
			this.drop();
		}
	},
	
	drop: function(){
		Mif.Tree.Drag.ghost.dispose();
		this.fireEvent('drop', Mif.Tree.Drag.current);
	}
	

});


/*
---
 
name: Mif.Tree.Rename
description: Mif.Tree.Rename
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Rename
 
...
*/

Mif.Tree.implement({
	
	attachRenameEvents: function(){
		this.wrapper.addEvents({
			click: function(event){
				if($(event.target).get('tag') == 'input') return;
				this.beforeRenameComplete();
			}.bind(this),
			keydown: function(event){
				if(event.key == 'enter'){
					this.beforeRenameComplete();
				}
				if(event.key == 'esc'){
					this.renameCancel();
				}
			}.bind(this)
		});
	},
	
	disableEvents: function(){
		if(!this.eventStorage) this.eventStorage = new Element('div');
		this.eventStorage.cloneEvents(this.wrapper);
		this.wrapper.removeEvents();
	},
	
	enableEvents: function(){
		this.wrapper.removeEvents();
		this.wrapper.cloneEvents(this.eventStorage);
	},
	
	getInput: function(){
		if(!this.input){
			this.input = new Element('input').addClass('mif-tree-rename');
			this.input.addEvent('focus',function(){this.select();}).addEvent('click', function(event) {
				event.stop();
			});
			Mif.Tree.Rename.autoExpand(this.input);
		}
		return this.input;
	},
	
	startRename: function(node){
		this.focus();
		this.unselect();
		this.disableEvents();
		this.attachRenameEvents();
		var input = this.getInput();
		input.value = node.name;
		this.renameName = node.getDOM('name');
		this.renameNode = node;
		input.setStyle('width', this.renameName.offsetWidth+15);
		input.replaces(this.renameName);
		input.focus();
	},
	
	finishRename: function(){
		this.renameName.replaces(this.getInput());
	},
	
	beforeRenameComplete: function(){
		if(this.options.beforeRename){
			var newName = this.getInput().value;
			var node = this.renameNode;
			this.options.beforeRename.apply(this, [node, node.name, newName]);
		}else{
			this.renameComplete();
		}
	},
		
	renameComplete: function(){
		this.enableEvents();
		this.finishRename();
		var node = this.renameNode;
		var oldName = node.name;
		node.set({
			property:{
				name: this.getInput().value
			}
		});
		this.fireEvent('rename', [node, node.name, oldName]);
		this.select(node);
	},
	
	renameCancel: function(){
		this.enableEvents();
		this.finishRename();
		this.select(this.renameNode);
	}
	
});

Mif.Tree.Node.implement({
	
	rename: function(){
		if (this.property.renameDenied) return;
		this.tree.startRename(this);
	}
	
});

Mif.Tree.Rename={
	
	autoExpand: function(input){
		var span = new Element('span').addClass('mif-tree-rename').setStyles({
			position: 'absolute',
			left: -2000,
			top:0,
			padding: 0
		}).injectInside(document.body);
		input.addEvent('keydown',function(event){
			(function(){
			input.setStyle('width',Math.max(20, span.set('html', input.value.replace(/\s/g,'&nbsp;')).offsetWidth+15));
			}).delay(10);
		});
	}
	
};


/*
---
 
name: Mif.Tree.Checkbox
description: Mif.Tree.Checkbox
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.Checkbox
 
...
*/

Mif.Tree.implement({

	initCheckbox: function(type){
		this.checkboxType = type || 'simple';
		this.dfltState.checked = 'unchecked';
		this.defaults.hasCheckbox = true;
		this.wrapper.addEvent('click', this.checkboxClick.bind(this));
		if(this.checkboxType == 'simple') return;
		this.addEvent('loadChildren', function(node){
			if(!node) return;
			if(node.state.checked == 'checked'){
				node.recursive(function(){
					this.state.checked = 'checked';
				});
			}else{
				node.getFirst().setParentCheckbox(1);
			}
		});

	},
	
	checkboxClick: function(event){
		if(this.mouse.target!='checkbox') {return;}
		this.mouse.node['switch']();
	},
	
	getChecked: function(includePartially){
		var checked=[];
		this.root.recursive(function(){
			var condition = includePartially ? this.state.checked!=='unchecked' : this.state.checked=='checked';
			if(this.hasCheckbox && condition) checked.push(this);
		});
		return checked;
	}

});

Mif.Tree.Node.implement({

	'switch' : function(state){
		if(this.state.checked == state || !this.hasCheckbox) return this;
		var type = this.tree.checkboxType;
		var checked=(this.state.checked == 'checked') ? 'unchecked' : 'checked';
		if(type == 'simple'){
			this.setCheckboxState(checked);
			this.tree.fireEvent(checked == 'checked' ? 'check' : 'unCheck', this);
			this.tree.fireEvent('switch', [this, (checked == 'checked' ? true : false)]);
			return this;
		};
		this.recursive(function(){
			this.setCheckboxState(checked);
		});
		this.setParentCheckbox();
		this.tree.fireEvent(checked == 'checked' ? 'check' : 'unCheck', this);
		this.tree.fireEvent('switch', [this, (checked == 'checked' ? true : false)]);
		return this;
	},
	
	setCheckboxState: function(state){
		if(!this.hasCheckbox) return;
		var oldState = this.state.checked;
		this.state.checked = state;
		if((!this.parentNode&&this.tree.$draw) || (this.parentNode && this.parentNode.$draw)){
			this.getDOM('checkbox').removeClass('mif-tree-node-'+oldState).addClass('mif-tree-node-'+state);
		}
	},
	
	setParentCheckbox: function(s){
		if(!this.hasCheckbox || !this.parentNode || (this.tree.forest && !this.parentNode.parentNode)) return;
		var parent = this.parentNode;
		var state = '';
		var children = parent.children;
		for(var i = children.length; i--; i>0){
			var child = children[i];
			if(!child.hasCheckbox) continue;
			var childState = child.state.checked;
			if(childState == 'partially'){
				state = 'partially';
				break;
			}else if(childState == 'checked'){
				if(state == 'unchecked'){
					state = 'partially';
					break;
				}
				state = 'checked';
			}else{
				if(state == 'checked'){
					state = 'partially';
					break;
				}else{
					state = 'unchecked';
				}
			}
		}
		if(parent.state.checked == state || (s && state == 'partially' && parent.state.checked == 'checked')){return;};
		parent.setCheckboxState(state);
		parent.setParentCheckbox(s);
	}

});


/*
---
 
name: Mif.Tree.CookieStorage
description: Mif.Tree.Node
license: MIT-Style License (http://mifjs.net/license.txt)
copyright: Anton Samoylov (http://mifjs.net)
authors: Anton Samoylov (http://mifjs.net)
requires: Mif.Tree
provides: Mif.Tree.CookieStorage
 
...
*/

Mif.Tree.CookieStorage = new Class({

	Implements: [Options],
	
	options:{
		store: function(node){
			return node.property.id;
		},
		retrieve: function(value){
			return Mif.id(value);
		},
		event: 'toggle',
		action: 'toggle'
	},

	initialize: function(tree, options){
		this.setOptions(options);
		this.tree = tree;
		this.cookie = new Cookie('mif.tree:' + this.options.event + tree.id||'');
		this.nodes = [];
		this.initSave();
	},
	
	write: function(){
		this.cookie.write(JSON.encode(this.nodes));
	},
	
	read: function(){
		return JSON.decode(this.cookie.read()) || [];
	},
	
	restore: function(data){
		if(!data){
			this.restored = this.restored || this.read();
		}
		var restored = data || this.restored;
		for(var i = 0, l = restored.length; i < l; i++){
			var stored = restored[i];
			var node = this.options.retrieve(stored);
			if(node){
				node[this.options.action](true);
				restored.erase(stored);
				l--;
			}
		}
		return restored;
	},
	
	initSave: function(){
		this.tree.addEvent(this.options.event, function(node, state){
			var value = this.options.store(node);
			if(state){
				this.nodes.include(value);
			}else{
				this.nodes.erase(value);
			}
			this.write();
		}.bind(this));
	}

});


