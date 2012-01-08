var Notimoo=new Class({
    elements:[],
    Implements:[Options,Events],
    scrollTimeOut:null,
    options:{
        parent:"",
        height:50,
        width:300,
        visibleTime:5000,
        locationVType:"top",
        locationHType:"right",
        locationVBase:42,
        locationHBase:10,
        notificationsMargin:19,
        opacityTransitionTime:750,
        closeRelocationTransitionTime:750,
        scrollRelocationTransitionTime:500,
        notificationOpacity:0.75
    },
    initialize:function(a){
        this.options.parent=$(document.body);
        if(a){
            if(a.parent){
                a.parent=$(a.parent)
                }
                this.setOptions(a)
            }
            var b=this;
        this.options.parent.addEvent("scroll",function(){
            $clear(this.scrollTimeOut);
            this.scrollTimeOut=(function(){
                b._relocateActiveNotifications(b.TYPE_RELOCATE_SCROLL)
                }).delay(200)
            },this);
        window.addEvent("scroll",function(){
            $clear(b.scrollTimeOut);
            b.scrollTimeOut=(function(){
                b._relocateActiveNotifications(b.TYPE_RELOCATE_SCROLL)
                }).delay(200)
            });
        this.elements.push(this.createNotificationElement(this.options))
        },
    createNotificationElement:function(){
        var c=new Element("div",{
            "class":"notimoo"
        });
        var n = new Element("div",{
            "class":"notimoo-wraper"
        });
        c.adopt(n);
        c.setStyle(this.options.locationVType,this.options.locationVBase);
        c.setStyle(this.options.locationHType,this.options.locationHBase);
        n.adopt(new Element("span",{
            "class":"title"
        }));
        n.adopt(new Element("div",{
            "class":"message"
        }));
        c.setStyle("width",this.options.width);
        c.setStyle("height",this.options.height);
        c.store("working",false);
        c.set("tween",{
            link:"chain",
            duration:this.options.opacityTransitionTime
            });
        c.set("opacity",0);
        var b=new Fx.Tween(c,{
            property:this.options.locationVType,
            link:"chain",
            duration:this.options.closeRelocationTransitionTime
            });
        c.store("baseTween",b);
        var a=new Fx.Tween(c,{
            property:this.options.locationVType,
            link:"chain",
            duration:this.options.scrollRelocationTransitionTime
            });
        c.store("scrollTween",a);
        c.addEvent("click",function(d){
            d.stop();
            this.close(c)
            }.bind(this));
        return c;
        },
    show:function(b){
        var c=this;
        var a=this._applyScrollPosition(this.options.locationVBase);
        var d=this.elements.filter(function(f){
            var e=f.retrieve("working");
            if(e){
                a=f.getStyle(this.options.locationVType).toInt()+f.getSize().y+this.options.notificationsMargin
                }
                return !e
            },this).getLast();
        if(!d){
            d=this.createNotificationElement();
            this.elements.push(d)
            }
            d.setStyle(this.options.locationVType,a);
        d.store("working",true);
        if(b.width){
            d.setStyle("width",b.width)
            }
            if(b.title){
            d.getElement("span.title").set("html",b.title)
            }
            d.getElement("div.message").set("html",b.message);
        if(b.customClass){
            d.addClass(b.customClass)
            }
            d.getElements("a").addEvent("click",function(e){
            e.stopPropagation()
            });
        this.options.parent.adopt(d);
        this._checkSize(d);
        d.setStyle('visibility', 'visible');
        d.get("tween").start("opacity",this.options.notificationOpacity).chain(function(){
            if((b.sticky)?!b.sticky:true){
                (function(){
                    c.close(d)
                    }).delay((b.visibleTime)?b.visibleTime:c.options.visibleTime,c)
                }
                c.fireEvent("show",d)
            })
        },
    close:function(c){
        var b=this;
        var a=b.elements;
        c.get("tween").start("opacity",0).chain(function(){
            if(a.length>1){
                a.elements=a.erase(c);
                }
                b._resetNotificationElement(c);
            b._relocateActiveNotifications(b.TYPE_RELOCATE_CLOSE);
            b.fireEvent("close",c)
            c.destroy();
            })
        },
    _relocateActiveNotifications:function(b){
        var d=this._applyScrollPosition(this.options.locationVBase);
        for(var a=0;a<this.elements.length;a++){
            var c=this.elements[a];
            if(c.retrieve("working")){
                if(this.TYPE_RELOCATE_CLOSE==b){
                    c.retrieve("baseTween").start(d)
                    }else{
                    c.retrieve("scrollTween").start(d)
                    }
                    d+=c.getSize().y+this.options.notificationsMargin
                }
            }
        },
    _checkSize:function(b){
        var d=b.getStyle("height").toInt();
        var c=b.getElement("span.title").getSize().y;
        var a=b.getElement("div.message").getSize().y;
        if(a>(d-c)){
            b.setStyle("height",d+(a-(d-c)))
            }
        },
    _resetNotificationElement:function(a){
        a.store("working",false);
        a.setStyle(this.options.locationVType,this.options.locationVBase);
        a.setStyle("height",this.options.height);
        a.setStyle("width",this.options.width)
        },
    _applyScrollPosition:function(a){
        if(this.options.locationVType=="top"){
            a+=this.options.parent.getScroll().y
            }else{
            a-=this.options.parent.getScroll().y
            }
            return a
        },
    TYPE_RELOCATE_CLOSE:1,
    TYPE_RELOCATE_SCROLL:2
});