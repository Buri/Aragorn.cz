var redis = require('node-redis');
require('mootools.js').apply(GLOBAL);

/*
 * Sessoion.js
 * Class for representing user session (guest/registered)
 * Allows synchronistaion with php sessions
 * Options:
 * {
 *  @function   parentStorageRemoval()      Function used to remove expired session from storage
 *  @fucntion   redisHook(@object message,
 *                  @object client,
 *                  @string channel)        Callback function used to handle incomming messages on pubsub
 * }
 * 
 * Public api:
 * @void        exit()                      Tells session to end itself. To be reworked
 * @mixed       sendToChannel(@string channel,
 *                  @object message)        Sends message to pubsub
 * @mixed       sendToClient(@mixed client,
 *                  @object message)        Sends message to selected client belonging to session
 * @void        broadcastToClients(
 *                  @object message)        Sends message to all clients owned by session
 */

exports.Session = new Class({
    Implements:[Options, Events],
    options:{
        parentStorageRemoval:null,
        redisHook:null
    },
    initialize:function(sid, options){
        this.setOptions(options);
        this.sessionId = sid;
        this.redis = redis.createClient();
        
        /* Setup aliases */
        this.pub = this.sendToChannel;
        this.puball = this.broadcastToClients;
        this.csend = this.sendToClient;
    },
    
    /* Fields */
    clients:[],
    sessionId:0,
    user:{
        id:0,
        roles:[],
        name:'',
        preferences:{},
        permissions:{},
        ips:[]
    },
    phpid:'',
    redis:null,
    eraseTimeout:null,
    
    /* Methods */
    registerClient:function(client){
        if(this.eraseTimeout){
            clearTimeout(this.eraseTimeout);
            this.eraseTimeout = {};
        }
        this.clients.push(client.sessionId);
        this.user.ips.include(client.connection.remoteAddress); /* Probably users real ip address, always good to know */
        
        client.redis = redis.createClient();
        client.redis.on('message', this.handleRedis.bind(client));
        client.redis.on('end', this.reconnectRedis.bind(client));
        client.sendToChannel = this.sendToChannel.bind(this);
        client.session = this;
    },
    removeClient:function(client){
        if(typeOf(client.redis.quit) == 'function')
            client.redis.quit();
        delete client.redis;
        this.clients.erase(client.sessionId);
        if(this.clients.length == 0){
            /* UPDATE, SET LARGER TIMEOUT, FOR DEBUG PURPOSES ONLY */
            this.eraseTimeout = setTimeout(this.erase.bind(this), 1000 * 15);
        }
    },
    exit:function(){
        if(typeOf(this.redis.quit) == 'function')
            this.redis.quit();
        delete this.redis;
    },
    erase:function(){
        this.exit();
        this.fireEvent('disconnect', this);
        this.options.parentStorageRemoval(this.sessionId); // AKA "Delete me, pls!"
    },
    
    /* Permission handling */
    isAllowed:function(res, op){
        var ps = this.user.permissions || {'_ALL':false}; /* This method doesn't care about real permissions, it simply uses what's pushed from PHP */
        if(!this.user.id || !this.user.roles) return false; /* User is not logged in */
        if(this.user.roles.indexOf('0') != -1) return true; /* If user is root */
        
        var p = ps[res];
        if(typeOf(p) != 'null'){
            if(typeOf(p[op]) != 'null') return p[op];
            if(typeOf(p['_ALL']) != 'null') return p['_ALL'];
            return false;
        }else if(typeOf(ps['_ALL']) != 'null'){
            return ps['_ALL'];
        }
        return false; /* Fallback */
    },

    /* Redis handling */
    handleRedis:function(channel, message){
        message = JSON.parse(message);
        return this.session.options.redisHook(message, this, channel);
    },
    reconnectRedis:function(e){
        this.redis = redis.createClient();
    },
    
    sendToChannel:function(channel, message){
        message.time = new Date().getTime();
        message.user = this.user;
        if(!this.redis || this.redis.publish)
            this.redis = redis.createClient();
        return this.redis.publish(channel, JSON.stringify(message));
    },
    /* Client param can be id of client or client object */
    sendToClient:function(client, message){
        if(typeof(client) == 'string'){
            if(this.clients.indexOf('client') == -1) throw new Exception('Client not found.');
            client = socket.clients[client];
        }
        message.time = new Date().getTime();
        message.identity = client.identity;
        client.send(message);
    },
    /* Clients can be array of client objects or empty */
    broadcastToClients:function(message, clients){
        if(!clients || !clients.length) clients = this.clients;
        clients.each(function(client){
            if(typeOf(client) == 'object')
                client.send(message);
            else
                socket.clients[client].send(message);
        });
    }
});
