var FloatingTips=new Class({Implements:[Options,Events],options:{position:"top",center:true,content:"title",html:false,balloon:true,arrowSize:6,arrowOffset:6,distance:3,motion:6,motionOnShow:true,motionOnHide:true,showOn:"mouseenter",hideOn:"mouseleave",showDelay:0,hideDelay:0,className:"floating-tip",offset:{x:0,y:0},fx:{duration:"short"}},initialize:function(b,a){this.setOptions(a);if(!["top","right","bottom","left","inside"].contains(this.options.position)){this.options.position="top"}if(b){this.attach(b)}return this},attach:function(b){var a=this;$$(b).each(function(c){evs={};evs[a.options.showOn]=function(){a.show(this)};evs[a.options.hideOn]=function(){a.hide(this)};c.addEvents(evs)});return this},show:function(b){var a=b.retrieve("floatingtip");if(a){if(a.getStyle("opacity")==1){clearTimeout(a.retrieve("timeout"));return this}}var c=this._create(b);if(c==null){return this}b.store("floatingtip",c);this._animate(c,"in");this.fireEvent("show",[c,b]);return this},hide:function(a){var b=a.retrieve("floatingtip");if(!b){return this}this._animate(b,"out");this.fireEvent("hide",[b,a]);return this},_create:function(f){var c=this.options;var b=c.content;var l=c.position;if(b=="title"){b="floatingtitle";if(!f.get("floatingtitle")){f.setProperty("floatingtitle",f.get("title"))}f.set("title","")}var e=(typeof(b)=="string"?f.get(b):b(f));var d=new Element("div").addClass(c.className).setStyle("margin",0);var m=new Element("div").addClass(c.className+"-wrapper").setStyles({margin:0,padding:0,"z-index":d.getStyle("z-index")}).adopt(d);if(e){if(c.html){d.set("html",typeof(e)=="string"?e:e.get("html"))}else{d.set("text",e)}}else{return null}var g=document.id(document.body);m.setStyles({position:"absolute",opacity:0}).inject(g);if(c.balloon&&!Browser.ie6){var a=new Element("div").addClass(c.className+"-triangle").setStyles({margin:0,padding:0});var h={"border-color":d.getStyle("background-color"),"border-width":c.arrowSize,"border-style":"solid",width:0,height:0};switch(l){case"inside":case"top":h["border-bottom-width"]=0;break;case"right":h["border-left-width"]=0;h["float"]="left";d.setStyle("margin-left",c.arrowSize);break;case"bottom":h["border-top-width"]=0;break;case"left":h["border-right-width"]=0;if(Browser.ie7){h.position="absolute";h.right=0}else{h["float"]="right"}d.setStyle("margin-right",c.arrowSize);break}switch(l){case"inside":case"top":case"bottom":h["border-left-color"]=h["border-right-color"]="transparent";h["margin-left"]=c.center?m.getSize().x/2-c.arrowSize:c.arrowOffset;break;case"left":case"right":h["border-top-color"]=h["border-bottom-color"]="transparent";h["margin-top"]=c.center?m.getSize().y/2-c.arrowSize:c.arrowOffset;break}a.setStyles(h).inject(m,(l=="top"||l=="inside")?"bottom":"top")}var i=m.getSize(),k=f.getCoordinates(g);var j={x:k.left+c.offset.x,y:k.top+c.offset.y};if(l=="inside"){m.setStyles({width:m.getStyle("width"),height:m.getStyle("height")});f.setStyle("position","relative").adopt(m);j={x:c.offset.x,y:c.offset.y}}else{switch(l){case"top":j.y-=i.y+c.distance;break;case"right":j.x+=k.width+c.distance;break;case"bottom":j.y+=k.height+c.distance;break;case"left":j.x-=i.x+c.distance;break}}if(c.center){switch(l){case"top":case"bottom":j.x+=(k.width/2-i.x/2);break;case"left":case"right":j.y+=(k.height/2-i.y/2);break;case"inside":j.x+=(k.width/2-i.x/2);j.y+=(k.height/2-i.y/2);break}}m.set("morph",c.fx).store("position",j);m.setStyles({top:j.y,left:j.x});return m},_animate:function(a,b){clearTimeout(a.retrieve("timeout"));a.store("timeout",(function(d){var f=this.options,e=(b=="in");var c={opacity:e?1:0};if((f.motionOnShow&&e)||(f.motionOnHide&&!e)){var g=d.retrieve("position");if(!g){return}switch(f.position){case"inside":case"top":c.top=e?[g.y-f.motion,g.y]:g.y-f.motion;break;case"right":c.left=e?[g.x+f.motion,g.x]:g.x+f.motion;break;case"bottom":c.top=e?[g.y+f.motion,g.y]:g.y+f.motion;break;case"left":c.left=e?[g.x-f.motion,g.x]:g.x-f.motion;break}}d.morph(c);if(!e){d.get("morph").chain(function(){this.dispose()}.bind(d))}}).delay((b=="in")?this.options.showDelay:this.options.hideDelay,this,a));return this}});