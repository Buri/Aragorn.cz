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
        connectionEstablished:function(){
            $('constat').setStyle('background', 'lime');
            $('constat').set('title', 'Connection status: online');
        },
        sessionHandshake:function(){
            console.log('HANDSHAKE!');
            this.connected = true;
            if(this.options.batchOffline){
                this.batch.each(function(msg){
                    this.sendRaw(msg);
                }.bind(this));
                this.batch = [];
            }
        },
        handleDisconnect:function(){
            this.connected = false;
            $('constat').setStyle('background', 'red');
            $('constat').set('title', 'Connection status: offline');
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
    }
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
        alert('History changed: ' + url);
    });
    AC = new AragornClient();
    new LazyLoad();
    /*$$('a[href]').addEvent('click', function(e){
        e.stop();
        $('content').load(this.href);
    });*/
});
