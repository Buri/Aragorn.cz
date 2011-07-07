require('mootools.js').apply(GLOBAL);
var Redis = require('node-redis');

exports.ChatServer = new Class({
    Implements:[Options, Events],
    options:{
        length:30,          // How many messages to store in one channel? Defaults to 500
        timeout:15*60       // How long before message is deleted
    },
    initialize:function(options){
        this.setOptions(options);
        this.redis = Redis.createClient();
    },
    sysMsg:function(channel, message){
        message.user = {id:0, name:'System'};
        message.time = new Date().getTime();
        message.data.color = '#7B6200';
        message.data.id = this.newMsgId(channel);
        this.storeMessage(channel, message);
        this.redis.publish(channel, JSON.stringify(message));
    },
    redis:null,
    storage:{},
    users:{},
    usersInfo:{},
    sortUsers:function(a,b){
        if(a < b) return 1;
        else if(a == b) return 0;
        return -1;
    },
    addUser:function(room, user){
        this.setUsers(room, this.getUsers(room).include(user).sort(this.sortUsers));
        if(user)
            this.usersInfo[user] = {};
    },
    removeUser:function(room, user){
        this.setUsers(room, this.getUsers(room).erase(user).sort(this.sortUsers));
        if(user)
            delete this.usersInfo[user];
    },
    getUsers:function(room){
        return this.users[room] || [];
    },
    getUsersO:function(room){
        var a = this.getUsers(room), r = [];
        for(var i = 0; i < a.length; a++){
            r[i] = Object.clone(this.usersInfo[a[i]]);
            r[i].name = a[i];
        }
        return r;
    },
    
    setUsers:function(room, users){
        this.users[room] = users || [];
        console.log(this.getUsers(room));
        return this.users[room];
    },
    storeMessage:function(cname, message){
        var tmp = this.getQueue(cname);
        tmp.unshift({
            t:setTimeout(function(cname){
                this.storage[cname].pop(); 
                if(!this.storage[cname].length) 
                    delete this.storage[cname];
                console.log(this.storage);
            }.bind(this, cname), this.options.timeout*1000), 
            d:message
        });
        if(tmp.length > this.options.length){
            clearTimeout(tmp[tmp.length - 1].t);
            tmp.pop();
        }
        this.storage[cname] = tmp;
    },
    getQueue:function(cname){
        return this.storage[cname] || [];
    },
    newMsgId:function(cname){
        var tmp = this.getQueue(cname);
        tmp.counter = tmp.counter || 0;
        this.storage[cname] = tmp;
        return tmp.counter++;
    },
    clearChannel:function(channel){
        delete this.storage[channel];
    },
    sessionHook:function(message, client){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.id || 'null') + (message.data.whisper ? '/' + message.data.whisper : '');
        switch(message.data.action){
            case "enter":
                client.redis.subscribe(cname); // Public channel
                client.redis.subscribe(cname + '/' + client.session.user.name); // Whisper
                if(message.data.noqueue)
                    break;
            case "queue":
                var t = this.getQueue(cname).combine(this.getQueue(cname + '/' + client.session.user.name)), s = [];
                for(var i = 0; i < t.length; i++){
                    var msg = t[i].d;
                    msg.data.time = msg.time;
                    msg.data.from = msg.user.name;
                    s.push(msg);
                }
                s.sort(function(a,b){var c = a.time - b.time;return (c < 0 ? -1 : (c ? 1 : 0));});
                client.send({cmd:'chat', data:{action:'queue', queue:s}});
                break;
            case 'userlist':
                client.send({cmd:'chat', data:{action:'userlist', list:this.getUsers(cname)}});
                break;
            case 'leave':
                client.redis.unsubscribe(cname);
                client.redis.unsubscribe(cname + '/' + client.session.user.name);
                break;
            case "post":
                if(!message.data.color)
                    message.data.color = client.session.user.preferences.chat.color;
                message.data.id = this.newMsgId(cname);
                this.storeMessage(cname, message);
                client.sendToChannel(cname, message);
                break;
        }
    },
    redisHook:function(message, client, channel){
        message.data.from = message.user.name;
        message.data.time = message.itime || message.time || new Date().getTime();
        client.send(message);
    },
    unixHook:function(message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.room || 'null');
        console.log("Unix: " + cname + ':' + JSON.stringify(message));
        switch(message.data.action){
            case "enter":
                this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' přichází do místnosti.'}});
                //this.sysMsg(cname, {cmd:'chat', data:{action:'add-user', user:{id:message.data.uid, name:message.data.name}}});
                this.sysMsg(cname, {cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}});
                this.addUser(cname,message.data.name);
                break;
            case "leave":
                this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchází z místnosti.'}});
                this.sysMsg(cname, {cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}});
                //this.sysMsg(cname, {cmd:'chat', data:{action:'remove-user', user:{id:message.data.uid}}});
                this.removeUser(cname,message.data.name);
                break;
            default:
                return ('BAD_PARAM');
        }
        return "OK";
    }
});
exports.create = function(){return new exports.ChatServer();}