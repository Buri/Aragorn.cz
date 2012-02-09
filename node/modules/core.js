require('mootools.js').apply(GLOBAL);

exports.Core = new Class({
    Implements:[Options, Events],
    options:{
    },
    initialize:function(socket, options){
        this.setOptions(options);
        this.socket = socket;
    },
    sessionHook:function(client, data){
        //console.log(data);
        switch(data.call){
            case 'request-moderator':
                client.session.pub('/system/moderators', data);
                break;
            default:
                console.log(data);
        }
    },
    redisHook:function(message, client, channel){
        console.log(message);
        //message.data.from = message.user.name;
        message.data.time = message.itime || message.time || new Date().getTime();
        client.json.emit('core', message);
    },
    unixHook:function(message){
        return "OK";
    }
});
exports.create = function(namespace, conf){
    return new exports.Core(namespace, {userServer:conf.userServer});
}
