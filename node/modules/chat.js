require('mootools.js').apply(GLOBAL);

exports.Chat = new Class({
    Implements:[Options, Events],
    options:{},
    initialize:function(options){
        this.setOptions(options);
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
                break;
            case "post":
                client.sendToChannel(cname, message);
                break;
        }
    },
    sessionHookRedis:function(message, client){
        message.data.from = message.user.name;
        client.send(message);
    }
});
