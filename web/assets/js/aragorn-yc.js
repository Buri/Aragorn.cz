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
                port:8000/*,
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
//        console.log('Start connecting');
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
//            console.log('Connection established');
        },
        sessionHandshake:function(){
            console.log('HANDSHAKE!');
            this.connected = true;
            if(this.options.batchOffline){
                this.batch.each(function(msg){
                    console.log('Sending: ', msg);
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
//                console.error('Broken message: ', msg);
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
//                    console.log('Identity cleared');
                case 'SESSION_REQUEST_IDENTITY':
//                    console.log('Obtained identity request');
                    if(Cookie.read('sid')){
                        this.transport.send({cmd:'SESSION_SID', identity:Cookie.read('sid')});
//                        console.log('Responded with identity: ' + Cookie.read('sid'));
                    }else{
                        this.transport.send({cmd:'SESSION_REQUEST_SID'});
//                        console.log('Requested new identity', {cmd:'REQUEST_IDENTITY'});
                    }
                    break;
                case 'SESSION_REGISTER_SID':
                    Cookie.write('sid', msg.identity);
//                    console.log('Identity registered: ' + msg.identity);
                    this.fireEvent('SESSION_HANDSHAKE');
                    this.fn.getIdentity.bind(this).call();
                    break;
                case 'SESSION_CONFIRMED_SID':
//                    console.log('Identity confirmed.');
                    this.fireEvent('SESSION_HANDSHAKE');
                    this.fn.getIdentity.bind(this).call();
                    break;
                case 'INVALID_SID':
//                    console.log('Problem with session.');
                    break;
                default:
//                    console.log('Fire event: cmd_' + msg.cmd);
                    this.fireEvent('cmd_' + msg.cmd, [msg.data, msg, this]);
                    break;
            }
        }
    },
    connected:false,
    batch:[],
    send:function(message){
        if(this.connected){
//            console.log('Sending message', message);
            this.transport.send(message);
        }else{
            if(this.options.batchOffline){
//                console.log('Storing message for sending later', message);
                this.batch.push(message);
            }
        }
    },
    registerCmd:function(cmd, callback){
//        console.log('Registering cmd ' + cmd);
        this.addEvent('cmd_' + cmd, callback);
    },
    removeCmd:function(cmd){
        this.removeEvent('cmd_' + cmd);
    }
}), AC = null;

window.addEvent('domready', function(){
    AC = new AragornClient();
});
