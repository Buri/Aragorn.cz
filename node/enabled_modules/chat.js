require('mootools').apply(GLOBAL);
exports.info = {
    'authors':['Buri'],
    version:{
        major:0,
        minor:1,
        build:0,
        toString:function(){
            return this.major + '.' + this.minor + '.' + this.build;
        }
    },
    handle:'chat',
    autoRestart:true
};
var redis = require('redis');

exports.ChatServer = new Class({
    Implements:[Options, Events],
    options:{
        length:50,          // How many messages to store in one channel? Defaults to 500
        timeout:15*60,       // How long before message is deleted, defaults to 15 minutes
        userServer:'static.aragorn.cz' // From where to serve icons?
    },
    initialize:function(socket, options){
        this.setOptions(options);
        this.redis = redis.createClient();
        this.socket = socket;
    },
    sysMsg:function(channel, message, store){
        message.user = {id:0, name:'System'};
        message.time = new Date().getTime();
        message.data = message.data || {};
        message.data.color = '#7B6200';
        message.data.size = 'small';
        message.data.id = this.newMsgId(channel);
        if(store)
            this.storeMessage(channel, message);
        app.sockets.in(channel).json.emit('chat', message);
    },
    socket:null,
    storage:{},
    users:{},
    /* 
     * States: idle, writing, deleting, textready, disconnected
     * Roles:  guest, user, moderator, admin or fetched from db?
     *
     */
    sortUsers:function(a,b){
        /* Case insensitive sorting */
        a = String(a.name).toUpperCase();
        b = String(b.name).toUpperCase();
        if(a > b) return 1;
        else if(a == b) return 0;
        return -1;
    },
    addUser:function(room, user, info){
        info.name = user;
        info.state = 'idle';
        info.time = new Date().getTime();
        var newusers = this.getUsers(room).include(info).sort(this.sortUsers);
        this.setUsers(room, newusers);
        this.broadcastUserUpdate(room);
    },
    removeUser:function(room, user){
        var pos = this.getUserPosition(room, user);
        this.users[room].splice(pos, 1);
        this.broadcastUserUpdate(room);
    },
    getUsers:function(room){
        return this.users[room] || [];
    },
    updateUser:function(room, user, data){
        var old = this.getUserInfo(user, room);
        var pos = this.getUserPosition(room, user);
        this.users[room][pos] = Object.merge(old, data);
        this.broadcastUserUpdate(room);
    },
    getUserNames:function(cname){
        var usr = this.getUsers(cname), r = [];
        for(var i = 0; i < usr.length; i++)
            r.push(usr[i].name);
        return r;
    },
    getUserInfo:function(user, room){
        var users = this.getUsers(room);
        var pos = this.getUserPosition(room, user);
        return users[pos];
    },
    getUsersO:function(room){
        var a = this.getUsers(room), r = [];
        a = this.users[room] || [];
        for(var i = 0; i < a.length; i++){
            var clone = Object.clone(a[i]);
            delete clone.permissions;
            delete clone.id;
            r.push(clone);
        }
        return r;
    },
    getUserPosition:function(room, user){
        var users = this.getUsers(room);
        return users.binarySearch({name:user}, this.sortUsers);
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
    removeRoom:function(cname){
        delete this.users[cname];
    },
    storeMessage:function(cname, message){
        var tmp = this.getQueue(cname);
        tmp.unshift({
            t:setTimeout(function(cname){
                if(this.storage[cname]){
                    this.storage[cname].pop(); 
                    /* Delete storage after channel is empty? If not, possible memory leaks... */
                    if(!this.storage[cname].length) 
                        delete this.storage[cname];
                }
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
    sessionHook:function(client, message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + (message.data.whisper ? '/' + message.data.whisper : '');
        switch(message.data.action){
            case "enter":
                client.join(cname); //.redis.subscribe(cname); // Public channel
                client.join(cname + '/' + client.session.user.name); // Whisper
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
                client.json.emit('chat', {cmd:'chat', data:{action:'queue', queue:s}});
                break;
            case 'userlist':
                client.json.emit('chat', {cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}});
                break;
            case 'leave':
                client.leave(cname);
                client.leave(cname + '/' + client.session.user.name);
                break;
            case 'post':
                if(this.getUserNames(cname).indexOf(client.session.user.name) == -1) return;
                if(!message.data.color && client.session.user.preferences && client.session.user.preferences.chat)
                    message.data.color = client.session.user.preferences.chat.color || '#fff' ;
                message.data.id = this.newMsgId(cname);
                if(message.data.whisper == client.session.user.name){
                    cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null');
                    delete message.data.whisper;
                }else if(message.data.whisper){
                    message.data.message = message.data.message.substr(message.data.message.indexOf('#')+1);
                    var cname2 = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + '/' + client.session.user.name;
                    this.storeMessage(cname2, message);
                }
                this.storeMessage(cname, message);
                client.sendToChannel(cname, message);
                var u = this.getUserInfo(client.session.user.name, cname);
                if(u){
                    u.time = new Date().getTime();
                    this.updateUser(cname, client.session.user.name, u);
                }
                break;
            case 'state':
                var us = this.getUserInfo(client.session.user.name, cname);
                if(us){
                    us.state = message.data.state;
                    us.time = new Date().getTime();
                    this.updateUser(cname, client.session.user.name, us);
                }
                break;
            case 'cmd':
                switch(message.data.command){
                    case 'kick':
                        if(client.session.isAllowed('chat', 'moderator')){
                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:client.session.user.name + ' vyhodil uĹľivatele ' + message.data.params.param + ' z mĂ­stnosti.'}}, true);
                            this.sysMsg(cname + '/' + message.data.params.param, {cmd:'chat', data:{action:'force-leave', silent:true}});
                        }else
                            client.send('notify', {code:403,msg:'Not allowed'});
                        break;
                    case 'kickall':
                        if(client.session.isAllowed('chat', 'moderator')){
                            this.sysMsg(cname, {cmd:'chat', data:{action:'force-leave', silent:true}});
                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:client.session.user.name + ' vyhodil vĹˇechny z mĂ­stnosti.'}}, true);
                        }else
                            client.send('notify', {code:403,msg:'Not allowed'});
                        break;
                    case 'sys':
                        this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.params.param}}, true);
                        break;
                    default:
                        console.log(message);
                }
                break;
            case 'delete':
                if(client.session.isAllowed('chat', 'moderator')){
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
    unixHook:function(message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.room || 'null');
        switch(message.data.action){
            case "enter":
                if(this.getUsers(cname).binarySearch(message.data.name) == -1){
                    this.addUser(cname, message.data.name, message.data.info);
                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' pĹ™ichĂˇzĂ­ do mĂ­stnosti.'}}, true);
                }
                break;
            case "leave":
                if(this.getUserNames(cname).binarySearch(message.data.name) != -1){
                    this.removeUser(cname, message.data.name);
                    if(!message.data.silent){
                        this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchĂˇzĂ­ z mĂ­stnosti.'}}, true);
                    }
                }
                cname += '/' + message.data.name;
                this.sysMsg(cname, {cmd:'chat', data:{'action':'force-leave', silent:true}});
                break;
            default:
                return ('BAD_PARAM');
        }
        return "OK";
    }
});
exports.create = function(namespace, conf){
    return new exports.ChatServer(namespace, {userServer:conf.userServer});
}