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
        this.redis.pingTimer = setTimeout(this.pingRedis.bind(this), 30*1000); /* ping every 30 sec */
    },
    clients:[],
    sessionId:0,
    user:{
        id:0,
        name:'',
        preferences:{}
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
        client.redis.on('message', this.handleRedis.bind(client));
        client.redis.on('end', this.reconnectRedis.bind(client));
        client.sendToChannel = this.sendToChannel.bind(this);
        client.redis.pingTimer = setTimeout(this.pingRedis.bind(client), 30*1000); /* ping every 30 sec */
        client.session = this;
    },
    removeClient:function(client){
        if(typeOf(client.redis.quit) == 'function')
            client.redis.quit();
        delete client.redis;
        this.clients.erase(client.sessionId);
        if(this.clients.length == 0){

            /* UPDATE, SET LARGER TIMEOUT, FOR DEBUG PURPOSES ONLY */
            this.eraseTimeout = setTimeout(this.erase.bind(this), 1000 * 5);
        }
    },
    exit:function(){
        if(typeOf(this.redis.quit) == 'function')
            this.redis.quit();
        delete this.redis;
    },
    eraseTimeout:{},
    erase:function(){
        this.exit();
        this.fireEvent('disconnect', this);
        this.options.parentStorageRemoval(this.sessionId); // AKA "Delete me, pls!"
    },
    handleRedis:function(channel, message){
        message = JSON.parse(message);
        return this.session.options.redisHook(message, this, channel);
    },
    reconnectRedis:function(e){
        this.redis = redis.createClient();
        //console.log('Redis reconnect');
    },
    pingRedis:function(){
        /* Just to keep connection alive */
        this.redis.publish('/dev/null', Math.round(Math.random()*1000));
    },
    sendToChannel:function(channel, message){
        message.time = new Date().getTime();
        message.user = this.user;
        if(!this.redis.publish)
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
            socket.clients[client].send(message);
        });
    }
});
