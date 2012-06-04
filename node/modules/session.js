var redis = require('redis');
require('mootools'); 

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
        getRedis:null
    },
    initialize:function(sid, Redis, options){
        this.setOptions(options);
        this.sessid = sid;
        if(!this.options.redis)
            this.options.redis = redis.createClient();
        this.redis = Redis || redis.createClient();
    },
    
    /* Fields */
    clients:[],
    sessid:0,
    user:{
        id:0,
        roles:[],
        name:'',
        preferences:{},
        permissions:{},
        ips:[]
    },
    phpid:'',
    eraseTimeout:null,
    redis:null,
    
    /* Methods */
    registerClient:function(client){
        var c = 'session-' + this.sessid; // + '-clients',
            r = this.redis;
        if(!r.connected) r = redis.createClient();
            //console.log(c);
        r.multi().persist(c).sadd(c, client.id, function(err, res){
            if(err) return;
        }).exec();
    },
    removeClient:function(client){
        var r = this.redis,
            c = 'session-' + this.sessid; // + '-clients';;
        if(!r.connected) r = redis.createClient();
        r.srem(c, client.id, function(err, res){
            if(err) return;
            r.smembers(c, function(err, mem){
                if(err) return;
                if(!mem || !mem.length){
                    r.multi().expire(c, 15).expire(c + '-user', 15).exec();
                }
            });
        });
    },
    erase:function(){
        this.clients.each(function(client){
            console.log('disconnect client ' + client);
        });
        this.fireEvent('disconnect', this);
      //  this.options.parentStorageRemoval(this.sessionId); // AKA "Delete me, pls!"
    },
    
    /* Permission handling */
    updateUser:function(usr){
        this.user = usr;
    },
    getUser:function(cb){
        //this.user = '';
        return cb(this.user);
    },
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
    
    sendToChannel:function(channel, message){
        message.time = new Date().getTime();
        message.user = this.user;
        message.channel = channel;
        return app.sockets.in(channel).json.emit(message.cmd, message);
    },
    /* Client param can be id of client or client object */
    sendToClient:function(client, message){
        if(typeof(client) == 'string'){
            if(this.clients.indexOf('client') == -1) throw new Exception('Client not found.');
            client = socket.clients[client];
        }
        message.time = new Date().getTime();
        message.identity = client.identity;
        client.json.send(message);
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
