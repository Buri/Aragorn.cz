/*
 * Created by Jakub Buriánek
 * &copy; Aragorn.cz 2011
 *
 */

//WEB_SOCKET_DEBUG = true;
WEB_SOCKET_SWF_LOCATION = '/WebSocketMain.swf';

var AragornClient = new Class({
    Implements:[Options, Events],
    options:{
        node:{
            url:null,
            port:0
        },
        batchOffline:true,
        client:{
            timeout:120,
            url:window.location.host,
            options:{
                port:8000,
                rememberTransport:false/*,
                transports:['websocket', 'flashsocket']*/
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
        this.transport.on('connectiong', function(){conslole.log('connecting', this);});
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
        },
        sessionHandshake:function(){
            console.log('HANDSHAKE!');
            this.connected = true;
            if(this.options.batchOffline){
                this.batch.each(function(msg){
                    this.transport.send(msg);
                }.bind(this));
                this.batch = [];
            }
        },
        handleDisconnect:function(){
            this.connected = false;
        },
        getIdentity:function(){
//            console.log('Testing identity.');
            if(window.AUTHENTICATED === true){
                this.transport.send({cmd:'TEST_IDENTITY'});
            }
        },
        handleMessage:function(msg){
            if(!msg || !msg.cmd){
                console.error('Broken message: ', msg);
                return;
            }
            switch(msg.cmd){
                case 'TEST_IDENTITY':
//                   console.log(msg);
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
                    console.log(msg);
                    console.log('Fire event: cmd_' + msg.cmd);
                    this.fireEvent('cmd_' + msg.cmd, [msg.data, msg, this]);
                    break;
            }
        }
    },
    connected:false,
    batch:[],
    send:function(message){
        if(this.connected){
            this.transport.send(message);
        }else{
            if(this.options.batchOffline){
                this.batch.push(message);
            }
        }
    },
    registerCmd:function(cmd, callback){
        this.addEvent('cmd_' + cmd, callback);
    },
    removeCmd:function(cmd){
        this.removeEvent('cmd_' + cmd);
    }
}), AC = null;

window.addEvent('domready', function(){
    AC = new AragornClient();
});
