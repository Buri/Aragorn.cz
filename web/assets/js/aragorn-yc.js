/*
 * Created by Jakub Buriánek
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
//            timeout:120,
            url:window.location.host,
            options:{
                port:8000,
                rememberTransport:false
            }
        }
    },
    initialize:function(options){
        this.setOptions(options);
        this.transport = new io.Socket(this.options.client.url, this.options.client.options);
        if(!this.transport){
            console.log('Unable to create transport.');
            return null;
        }
        this.ajax = this.Ajax.send.bind(this);
        this.transport.on('connect', this.fn.connectionEstablished);
        this.transport.on('connectiong', function(){ $('constat').setStyle('background', 'yellow'); $('constat').set('title', 'Connection status: connecting'); conslole.log('connecting', this);});
        this.transport.on('connect_failed', this.fn.global);
        this.transport.on('disconnect', this.fn.handleDisconnect.bind(this));
        this.transport.on('message', this.fn.handleMessage.bind(this));
        this.addEvent('SESSION_HANDSHAKE', this.fn.sessionHandshake.bind(this));
        this.transport.connect();
    },
    transport:null,
    ajax:null,
    _ping:{
        timeout:null,
        last:[]
    },
    Ajax:{
        transports:[],
        send:function(action, data, callback){
            var t = null;
            for(var i = 0; i < this.Ajax.transports.length; i++){
                var transport = this.Ajax.transports[i];
                if(transport.isRunning())
                    continue;
                t = transport;
                break;
            }
            if(!t){
                t = new Request.HTML({evalScripts:false, link:'chain'});
                this.Ajax.transports.push(t);
            }
            if(callback) t.addEvent('success', callback.bind(t));
            t.send({url:'/ajax/' + action + '/', data:data});
        }
    },
    fn:{
        global:function(message){
            console.error('FAIL!');
        },
        ping:function(){
            this._ping.last.push(new Date().getTime());
            this.send('PING');
        },
        connectionEstablished:function(){
            $('constat').set('class', 'online');
            $('constat').set('title', 'Connection status: online');
        },
        sessionHandshake:function(){
            console.log('HANDSHAKE!');
            this.connected = true;
            this._ping.timeout = setInterval(this.fn.ping.bind(this), 1000);
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
                case 'SESSION_HAS_PHPSESSID_REGISTERED':
                    if(msg.identity === true){
                        this.fireEvent('SESSION_CONFIRM');
                    }else{
                        this.ajax('testidentity', null, null);
                    }
                    break;
                case 'SESSION_RESET_SID':
                    Cookie.dispose('sid');
                case 'SESSION_REQUEST_IDENTITY':
                    if(Cookie.read('sid')){
                        this.transport.send({cmd:'SESSION_SID', identity:Cookie.read('sid')});
                    }else{
                        this.transport.send({cmd:'SESSION_REQUEST_SID'});
                    }
                    break;
                case 'SESSION_REGISTER_SID':
                    Cookie.write('sid', msg.identity);
                    this.fireEvent('SESSION_HANDSHAKE');
                    this.fn.getIdentity.bind(this).call();
                    break;
                case 'SESSION_CONFIRMED_SID':
                    this.fireEvent('SESSION_HANDSHAKE');
                    this.fn.getIdentity.bind(this).call();
                    break;
                case 'INVALID_SID':
                    break;
                case 'PING':
                    $('constat').set('text', new Date().getTime() - this._ping.last.shift());
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
        return this.sendRaw({
            cmd:cmd, 
            time:new Date().getTime(), 
            data:params, 
            identity:Cookie.read('sid')
        });
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
        return this.addEvent('cmd_' + cmd, callback);
    },
    removeCmd:function(cmd){
        return this.removeEvent('cmd_' + cmd);
    },
    message:function(){
        console.log('Resource not aviable.');
    },
    prompt:function(){},
    info:function(){}
}), AC = null;
/*Linker = new Request.HTML({
    update:$('content'),
    onSuccess:function(){
        console.log('Done loading');
    }
});
Linker.callback = function(e){
    e.stop();
    Linker.get({url:this.href});
};
Linker.hook = '';*/

window.addEvent('domready', function(){
    History.addEvent('change', function(url){
        new Request.HTML({
             url: url,
             link:'cancel',
             evalScripts:true,
             evalResponse:true,
             update:$('content'),
             onComplete:function(){
                 $('content').fade(1);
             },
             onSuccess:function(){
             },
             onFailure:function(){
                 AC.info('Chyba', 'Stránku se nepodařilo načíst.', 'error.png');
                 location.href = url;
             }
        }).send();
        console.log('History changed: ' + url);
    });
    AC = new AragornClient();
    new LazyLoad({elements:'img.ll'});
    if($$('#content').length){
        $(document.body).addEvent('click:relay(.ajax)', function(event) {
            new Event(event).stop();
            $('content').fade(0.5);
            History.push(this.get('href'));
        });
    }
});
