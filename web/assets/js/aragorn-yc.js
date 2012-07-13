/*
 * Created by Jakub BuriÄ‚Ë‡nek
 * &copy; Aragorn.cz 2011
 *
 */

//WEB_SOCKET_DEBUG = true;
WEB_SOCKET_SWF_LOCATION = '/WebSocketMain.swf';


/*
 * Aragorn client
 * Options:
 * {
 *  @bool   batchOffline    if true, message that are sent while disconnected from server are stored and sent when connected
 *  @object client          Socket.io related configuration
 *           {
 *            @string   url         Location of node.js
 *            @object   options     See Socket.io documentation
 *           }
 * }
 * 
 * Public api:
 * @object      ajax(@string url, @object data, @function callback)     Sends ajax request on url. For options see Request.HTML documentation on mootools
 * @object      send(@string cmd, @object data)                         Sends cmd(data) to node.js server
 * @object      sendRaw(@object message)                                
 * @bool        connected                                               Indicates whenever is session established or not
 * @object      registerCmd(@string cmd, @function callback)            Callback to be executed when cmd arives
 * @object      removeCmd(@string cmd)                                  Oposite to registerCmd
 */
var AragornClient = new Class({
    Implements:[Options, Events],
    options:{
        batchOffline:true,
        client:{
            url:window.location.host,
            options:{
                port:8000,
                rememberTransport:false
            }
        },
        notifications:{
            sound:true,
            source:'audioNotification'
        }
    },
    initialize:function(options){
        this.setOptions(options);
        this.ajax = this.Ajax.send.bind(this);
        //this.notimoo = new Notimoo();
        if(this.options.notifications.sound)
            this.notificationAudio = $(this.options.notifications.source);
        if(typeof window != 'undefined' && !window.AUTHENTICATED){
            Cookie.write('sid', Math.random());
        }
        this.transport = io.connect(this.options.client.url + ':8000');
        if(!this.transport){
            console.log('Unable to create transport.')            
        }
               
        var t = this.transport;
        t.on('connect', this.fn.connectionEstablished.bind(this));
        t.on('connecting', function(){$('constat').set('class', 'connecting');$('constat').set('title', 'Connection status: connecting');});
        t.on('PING', function(){$('constat').set('text', new Date().getTime() - this._ping.last.shift());}.bind(this));
        t.on('SESSION_REQUEST_IDENTITY', function(){
            if(Cookie.read('sid')){
                this.emit('SESSION_SID',Cookie.read('sid'));
            }else{
                this.emit('SESSION_REQUEST_SID');
            }
        });
        t.on('SESSION_REGISTER_SID', function(sid){
            Cookie.write('sid', sid);
            this.fireEvent('SESSION_HANDSHAKE');
        }.bind(this));
        t.on('SESSION_CONFIRMED_SID', function(sid){
            this.fireEvent('SESSION_HANDSHAKE');
        }.bind(this));
        t.on('SESSION_RESET_SID', function(){
            Cookie.dispose('sid');
            this.emit('SESSION_REQUEST_SID');
        });
        t.on('SYSTEM_UPDATE_USERS_ONLINE', function(num){
            $$('#numUsrOnline').each(function(e){
                e.set('text', num[0]);
                e.getParent().set('title', 'Celkem pĹ™ipojenĂ­: ' + parseInt(num[1]));
            });
        });
        t.on('connect_failed', this.fn.global);
        t.on('disconnect', this.fn.handleDisconnect.bind(this));
        t.on('message', function(msg){ console.log(msg);});
        this.addEvent('SESSION_HANDSHAKE', this.fn.sessionHandshake.bind(this));
        if(window.AUTHENTICATED)
            this.resetInactivity();
        document.head || (document.head = document.getElementsByTagName('head')[0]);
    },
    notificationAudio:{play:function(){}},
    notimoo:null,
    transport:null,
    ajax:null,
    _ping:{ 
        timeout:null,
        last:[]
    },
    Ajax:{
        transports:[],
        reloadLocation:function(){
            if(History.initialized == true)
                History.push(location.href);
            else
                location.reload();
        },
        send:function(action, data, callback, method){
            var t = null;
            for(var i = 0; i < this.Ajax.transports.length; i++){
                if(!this.Ajax.transports[i].isRunning()){
                    t = this.Ajax.transports[i];
                    break;
                }
            }
            if(!t){
                t = new Request.HTML({evalScripts:false, link:'chain', 'method':'get'});
                this.Ajax.transports.push(t);
            }
            t.removeEvents('success');
            if(callback) t.addEvent('success', callback.bind(t));
            var uri = '/ajax/'+ action + '/';
            if(data && data.id){
                uri += encodeURI(data.id) + '/';
                delete data.id;
                if(data.param || data.param === ""){
                    uri += encodeURI(data.param) + '/';
                    delete data.param ;
                }
            }
            return t.send({url:uri, data:data, method:(method || 'post')});
        }
    },
    fn:{
        global:function(message){
            console.error('FAIL!');
        },
        ping:function(){
            this._ping.last.push(new Date().getTime());
            this.transport.emit('PING');
        },
        connectionEstablished:function(){
            $('constat').set('class', 'online');
            $('constat').set('title', 'Connection status: online');
            //this.fireEvent('SESSION_HANDSHAKE');
        },
        sessionHandshake:function(){
            //console.log('HANDSHAKE!');
            this.connected = true;
            this._ping.timeout = setInterval(this.fn.ping.bind(this), 1000);
            this.ajax('testIdentity', {sid:Cookie.read('sid')}, function(tree){
                //console.log('Handshake complete', tree[2].get('text'), this);
                this.fireEvent('identitypush');
            }.bind(this));
            if(this.options.batchOffline){
                this.batch.each(function(msg){
                    this.sendRaw(msg);
                }.bind(this));
                this.batch = [];
            }
        },
        handleDisconnect:function(){
            this.connected = false;
            $('constat').set('class', 'offline');
            $('constat').set('title', 'Connection status: offline');
            clearInterval(this._ping.timeout);
            this._ping.last.empty();
        },
        getIdentity:function(){
            if(window.AUTHENTICATED === true){
                this.transport.send({cmd:'SESSION_HAS_PHPSESSID_REGISTERED'});
            }
        },
        handleMessage:function(msg){
            if(!msg || !msg.cmd){
                console.error('Broken message: ', msg);
                return;
            }
            switch(msg.cmd){
                case 'PING':
                    $('constat').set('text', new Date().getTime() - this._ping.last.shift());
                    break;
                case 'NOTIFY':
                    this.message(msg.data.title, msg.data.body, msg.data.options);
                    break;
                default:
                    this.fireEvent('cmd_' + msg.cmd, [msg.data, msg, this]);
                    break;
            }
        }
    },
    connected:false,
    batch:[],
    send:function(cmd, params){
        var message = {
            cmd:cmd,
            data:params
        }
        if(this.connected){
            if(!message.identity) message.identity = Cookie.read('sid');
            if(!message.time) message.time = new Date().getTime();
            return this.transport.json.emit(cmd, message);
        
        }else{
            if(this.options.batchOffline){
                message.itime = new Date().getTime();
                return this.batch.push(message);
            }
        }
        return null;
    },
    sendRaw:function(message){
        if(this.connected){
            if(!message.identity) message.identity = Cookie.read('sid');
            if(!message.time) message.time = new Date().getTime();
            return this.transport.send(message);
        }else{
            if(this.options.batchOffline){
                message.itime = new Date().getTime();
                return this.batch.push(message);
            }
        }
        return null;
    },
    registerCmd:function(cmd, callback){
        return this.transport.on(cmd, callback);
        //return this.addEvent('cmd_' + cmd, callback);
    },
    removeCmd:function(cmd){
        return this.transport.removeEvents(cmd);
        //return this.removeEvent('cmd_' + cmd);
    },
    message:function(title, message, options){
        this.notificationAudio.play();
        return this.notimoo.show(Object.merge({title:title || '', message:message || ''}, options));
    },
    prompt:function(question, cb){
        new MooDialog.Prompt(question, cb);
    },
    info:function(){},
    resetInactivity:function(timeout){
        if(this.inactive)
            clearTimeout(this.inactive);
        this.inactive = setTimeout(function(){this.inactivityOverlay();}.bind(this), timeout || 60*60*1000);
    },
    updateFavicon:function(count){
        $$('#favicon').dispose();
        return document.head.appendChild(new Element('link', {rel:'shortcut icon', href:'/icon.php?t=' + new Date().getTime() + '&num=' + (count ? parseInt(count) : ''), id:'favicon'}));
    },
    inactivityOverlay:function(){
        var dialog = new MooDialog.Request('/ajax/loginui/', {
            title:'PÄąâ„˘ihlÄ‚Ë‡ÄąË‡enÄ‚Â­ vyprÄąË‡elo',
            scroll:true,
            useEscKey:false
        });
        dialog.addEvent('hide', function(){
            if(!this.session)
                location.reload();
            this.session = false;
        });
        dialog.open();
    },
    pay:function(name, params){
        params = params || {};
        AC.ajax('bank', {
            id:'askprice',
            params:name
        }, function(res, tree){
            console.log(res);
            var win = new mBox.Modal.Confirm({
                title:'Potvrdit platbu',
                overlay: true,
                overlayStyles: {
                    color: 'white',
                    opacity: 0.8
                },
                overlayFadeDuration: 50,
                content: 'Akce: ' + tree[1].get('text') + '<br/>Cena: ' + tree[2].get('text') + '&yen;<br/>',
                buttons:[
                    { title: 'ZruÄąË‡it' },
                    { 
                        title: 'Potvrdit platbu',
                        event: function() {
                            AC.ajax(params.task, params.prm, params.cb, params.method)
                            win.close();
                        }
                    }
                ]
            });
            win.open();
        });
    },
    inactive:null,
    session:false,
    profileLinkTips:null
}), AC = new AragornClient(), spinner = null;

window.addEvents({'domready': function(){
    new LazyLoad({elements:'img.ll'});
   /* AC.profileLinkTips = new FloatingTips($$('a.user-link'), {
        html:true,
        position:'right',
        content:function(e){
            var tt = e.get('data-profileinfo');
            if(tt) return new Element('div', {html:tt});
            AC.ajax('profileinfo', {
                id:e.get('data-profile')
            }, function(res, tree){
                $$('a[href=' + e.get('href') + ']').set('data-profileinfo', tree[0].get('html'));
                setTimeout(function(){
                    $$('a[href=' + e.get('href') + ']').set('data-profileinfo', null);
                }, 30000);
                AC.profileLinkTips.hide(e).show(e);
            }.bind(this), 'get');
            return "NaÄŤĂ­tĂˇm...";
        }
    });*/
    if(window.AUTHENTICATED && false){
        var iddlebar = new MoogressBar('iddlebar', {
            bgImage:'http://static.aragorn.cz/images/dark/progressbar/blue.gif', 
            hide:false, 
            label:false, 
            fx:false
        });
        iddlebar.setPercentage(100)
        iddlebar.parent.set('title', 'Odpočet neaktivity');
        iddlebar.addEvent('change', function(p){
            this.bar.set('text', Math.round(60 * p / 100) + ' minut');
        });
        iddlebar.interval = setInterval(function(){
            this.increasePercentage(-1);
        }.bind(iddlebar), 1000);
    }
    History.initialized = false;
    if(!Browser.ie){
        History.addEvent('change', function(url){
            if(!this.req)
                this.req = new Request.HTML({
                    url: url,
                    link:'cancel',
                    evalScripts:true,
                    evalResponse:true,
                    update:$('content'),
                    onComplete:function(){
                        AC.resetInactivity();
                        $('content').removeClass('contentLoading');
                        //AC.profileLinkTips.attach($$('a.user-link'));
                        //spinner.stopSpin();
                    },
                    onFailure:function(){
                        AC.message('Chyba', 'StrÄ‚Ë‡nku se nepodaÄąâ„˘ilo naĂ„Ĺ¤Ä‚Â­st.');
                        location.href = url;
                    }
                });
            this.req.send({'url':url});
        });
        if(!Browser.ie || location.href.indexOf('mistnost') == -1 )
            History.handleInitialState();
        History.initialized = true;
        if($$('#content').length){
            $(window).addEvent('click:relay(a.ajax)', function(event) {
                event.preventDefault();
                $('content').addClass('contentLoading');
                //spinner.startSpin();
                History.push(this.get('href'));
            });
        }
/*        var fn = function(e){
            if(e.type == 'change' || (e.type == 'keyup' && e.key == 'enter') || e.target.id == 'btnStatus'){
                AC.ajax('statusupdate', {id:$('msgStatus').get('value')});
                $('msgStatus').blur();
            }
        };
        $$('#msgStatus,#btnStatus').addEvents({'keyup':fn, 'click':fn});*/
    }
}/*,
'resize':function(e){
    console.log(window.innerWidth);
    if(window.innerWidth <= 1330){
        $('sidebar-left').addClass('slide-left');
        if(window.innerWidth <= 1134){
            $('sidebar-right').addClass('slide-right');
        }else{
            $('sidebar-right').removeClass('slide-right');
        }
    }else{
        $('sidebar-left').removeClass('slide-left');
    }
}*/});
