require('mootools.js').apply(GLOBAL);
var Redis = require('node-redis');

exports.ChatServer = new Class({
    Implements:[Options, Events],
    options:{
        length:50,          // How many messages to store in one channel? Defaults to 500
        timeout:15*60,       // How long before message is deleted, defaults to 15 minutes
        userServer:'static.aragorn.cz' // From where to serve icons?
    },
    initialize:function(options){
        this.setOptions(options);
        this.redis = Redis.createClient();
    },
    sysMsg:function(channel, message, store){
        message.user = {id:0, name:'System'};
        message.time = new Date().getTime();
        message.data.color = '#7B6200';
        message.data.size = 'small';
        message.data.id = this.newMsgId(channel);
        if(store)
            this.storeMessage(channel, message);
        this.redis.publish(channel, JSON.stringify(message));
    },
    redis:null,
    storage:{},
    users:{},
    usersInfo:{},
    /* 
     * States: idle, writing, deleting, textready, disconnected
     * Roles:  guest, user, moderator, admin or fetched from db?
     *
     */
    sortUsers:function(a,b){
        /* Case insensitive sorting */
        a = String(a).toUpperCase();
        b = String(b).toUpperCase();
        if(a > b) return 1;
        else if(a == b) return 0;
        return -1;
    },
    addUser:function(room, user, info){
        this.setUsers(room, this.getUsers(room).include(user).sort(this.sortUsers));
        if(user)
            this.usersInfo[user] = Object.merge(info || {icon:'http://' + this.options.userServer + '/i/nobody.jpg', status:'Lorem ipsum', permissions:{'delete':false}, id:-1}, {time:new Date().getTime(), state:'idle'});
        this.broadcastUserUpdate(room);
    },
    removeUser:function(room, user){
        this.setUsers(room, this.getUsers(room).erase(user).sort(this.sortUsers));
        if(user)
            delete this.usersInfo[user];
        this.broadcastUserUpdate(room);
    },
    getUsers:function(room){
        return this.users[room] || [];
    },
    updateUser:function(room, user, data){
        this.usersInfo[user] = data;
        this.broadcastUserUpdate(room);
    },
    getUserInfo:function(user){
        return this.usersInfo[user]
    },
    getUsersO:function(room){
        var a = this.getUsers(room), r = [];
        for(var i = 0; i < a.length; i++){
            var clone = Object.clone(this.usersInfo[a[i]]);
            clone.name = a[i];
            delete clone.permissions;
            delete clone.id;
            r.push(clone);
        }
        return r;
    },
    broadcastUserUpdate:function(cname){
        this.sysMsg(cname, {cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}}, false);
    },
    setUsers:function(room, users, broadcastUpdate){
        this.users[room] = users || [];
        if(broadcastUpdate)
            this.broadcastUserUpdate(room);
        return this.users[room];
    },
    storeMessage:function(cname, message){
        var tmp = this.getQueue(cname);
        tmp.unshift({
            t:setTimeout(function(cname){
                this.storage[cname].pop(); 
                /* Delete storage after channel is empty? If not, possible memory leaks... */
                if(!this.storage[cname].length) 
                    delete this.storage[cname];
            }.bind(this, cname), this.options.timeout*1000), 
            d:message,
            id:message.data.id
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
        if(!cname) return 0;
        var tmp = this.getQueue(cname);
        tmp.counter = tmp.counter || 0;
        this.storage[cname] = tmp;
        return ('msg' + cname.replace(/\//gi, '-') + (tmp.counter++));
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
                client.send({cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}});
                break;
            case 'leave':
                client.redis.unsubscribe(cname);
                client.redis.unsubscribe(cname + '/' + client.session.user.name);
                break;
            case 'post':
                if(!message.data.color && client.session.user.preferences)
                    message.data.color = client.session.user.preferences.chat.color || '#fff' ;
                message.data.id = this.newMsgId(cname);
                this.storeMessage(cname, message);
                client.sendToChannel(cname, message);
                break;
            case 'state':
                var u = this.getUserInfo(client.session.user.name);
                if(u){
                    u.state = message.data.state;
                    this.updateUser(cname, client.session.user.name, u);
                }
                break;
            case 'delete':
                var usr = this.getUserInfo(client.session.user.name);
                if(usr && usr.permissions['delete']){
                    var q = this.getQueue(cname), pos = q.binarySearch({id:message.data.messid}, function(a,b){a = parseInt(a.id.substr(a.id.lastIndexOf('-') + 1));b = parseInt(b.id.substr(b.id.lastIndexOf('-') + 1));return ( a < b ? 1 : (a == b ? 0 : -1));});
                    for(pos; pos < q.length - 1; pos++){
                        q[pos] = q[pos + 1];
                    }
                    q = q.pop();
                    if(q) clearTimeout(q.t);
                    this.sysMsg(cname, {cmd:'chat', data:{action:'delete', message:message.data.messid}});
                }
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
        switch(message.data.action){
            case "enter":
                if(this.getUsers(cname).binarySearch(message.data.name) == -1){
                    this.addUser(cname, message.data.name, message.data.info);
                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' přichází do místnosti.'}}, true);
                }
                break;
            case "leave":
                if(this.getUsers(cname).binarySearch(message.data.name) != -1){
                    this.removeUser(cname, message.data.name);
                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchází z místnosti.'}}, true);
                }
                cname += '/' + message.data.name;
                this.sysMsg(cname, {cmd:'chat', data:{'action':'force-leave', url:'/chat'}});
                break;
            default:
                return ('BAD_PARAM');
        }
        return "OK";
    }
});
exports.create = function(conf){return new exports.ChatServer({userServer:conf.common['variable.userServer']});}