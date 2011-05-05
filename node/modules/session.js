var redis = require('node-redis');
require('mootools.js').apply(GLOBAL);
  
exports.Session = new Class({
    Implements:[Options, Events],
    options:{
        parentStorageRemoval:function(){},
        chatCommandHook:function(){},
        chatRedisHook:function(){}
    },
    initialize:function(sid, options){
        this.setOptions(options);
        this.sessionId = sid;
        this.redis = redis.createClient();
    },
    clients:[],
    sessionId:0,
    user:{
        id:0,
        name:''
    },
    phpid:'',
    redis:{},
    registerClient:function(client){
        if(this.eraseTimeout){
            clearTimeout(this.eraseTimeout);
            this.eraseTimeout = {};
        }
        this.clients.push(client.sessionId);

        client.redis = redis.createClient();
        client.redis.on('subscribe', function(channel){/*console.log('Subscribe ' + channel)*/});
        client.redis.on('unsubscribe', function(channel){/*console.log('Unsubscribe ' + channel)*/});
        client.redis.on('message', this.handleRedis.bind(client));
        client.redis.subscribe('/system');
        client.sendToChannel = this.sendToChannel.bind(this);
        client.session = this;
    },
    removeClient:function(client){
        client.redis.quit();
        delete client.redis;
        this.clients.erase(client.sessionId);
        if(this.clients.length == 0){

            /* UPDATE, SET LARGER TIMEOUT, FOR DEBUG PURPOSES ONLY */
            this.eraseTimeout = setTimeout(this.erase.bind(this), 1000 * 5);
        }
    },
    exit:function(){
        this.redis.quit();
        delete this.redis;
    },
    eraseTimeout:{},
    erase:function(){
        this.exit();
        /* Proč je tu tenhle řádek zdvojený? */
        this.fireEvent('disconnect', this);
        this.options.parentStorageRemoval(this.sessionId);
    },
    handleRedis:function(channel, message){
        message = JSON.parse(message);
        switch(message.cmd){
            case 'chat':
                this.session.options.chatRedisHook(message, this);
                break;
            default:
                console.log(message);
        }
    },
    handleMessage:function(message, client){
        switch(message.cmd){
            case 'chat':
                this.options.chatCommandHook(message,client);
                break;
            default:
                this.sendToClient(client, message);
        }
    },
    sendToChannel:function(channel, message){
        message.time = new Date().getTime();
        message.user = this.user;
        this.redis.publish(channel, JSON.stringify(message));
    },
    sendToClient:function(client, message){
        if(typeof(client) == 'string'){
            if(this.clients.indexOf('client') == -1) throw new Exception('Client not found.');
            client = socket.clients[client];
        }
        client.send(message);
    },
    broadcastToClients:function(message, clients){
        if(!clients || !clients.length) clients = this.clients;
        clients.each(function(client){
            socket.clients[client].send(message);
        });
    }
});
