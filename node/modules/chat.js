require('mootools.js').apply(GLOBAL);
var Redis = require('node-redis');

exports.Chat = new Class({
    Implements:[Options, Events],
    options:{
        length:300,
        timeout:15*60
    },
    initialize:function(options){
        this.setOptions(options);
        this.redis = Redis.createClient();
    },
    sysMsg:function(channel, message){
        message.user = {id:0, name:'System'};
        message.time = new Date().getTime();
        this.storeMessage(channel, message);
        this.redis.publish(channel, JSON.stringify(message));
    },
    redis:null,
    storage:{},
    storeMessage:function(cname, message){
        var tmp = this.getQueue(cname);
        tmp.unshift({t:setTimeout(function(){}.bind(this), this.options.timeout*1000), d:message});
        if(tmp.length > this.options.length){
            clearTimeout(tmp.last().t);
            tmp.pop();
        }
        this.storage[cname] = tmp;
    },
    getQueue:function(cname){
        return this.storage[cname] || [];
    },
    sessionHook:function(message, client){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.id || 'null');
        switch(message.data.action){
            case "enter":
                client.redis.subscribe(cname); // Public channel
                client.redis.subscribe(cname + '/' + client.session.user.name); // Whisper
                break;
            case 'leave':
                client.redis.unsubscribe(cname);
                client.redis.unsubscribe(cname + '/' + client.session.user.name);
                break;
            case "post":
                this.storeMessage(cname, message);
                client.sendToChannel(cname, message);
                break;
            case "queue":
                var t = this.getQueue(cname).combine(this.getQueue(cname + '/' + client.session.user.name)), s = [];
                for(var i = 0; i < t.length; i++){
                    var msg = t[i].d;
                    msg.data.time = msg.time;
                    msg.data.from = msg.user.name;
                    s.push(msg);
                }
                s.sort(function(a,b){ var c = a.time - b.time; return (c < 0 ? -1 : (c ? 1 : 0)); });
                client.send({cmd:'chat', data:{action:'queue', queue:s}});
                break;
        }
    },
    sessionHookRedis:function(message, client){
        message.data.from = message.user.name;
        client.send(message);
    },
    unixHook:function(message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.room || 'null');
        switch(message.data.action){
            case "enter":
                console.log('User entered chat');
                this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' přichází do místnosti.'}});
                break;
            case "leave":
                console.log('User left chat');
                this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchází z místnosti.'}});
                break;
            default:
                return ('BAD_PARAM');
        }
        return "OK";
    }
});
