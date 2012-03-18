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
    socket:null,
    storage:{},
    users:{},
    
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
        this.sendMessage(channel, message, store);
    },
    sendMessage:function(channel, message, store){
        console.log('Try send', message);
        this.redis.incr('chat:msgid:' + channel, function(err, count){
            if(err){
                console.log(err);
                throw new Error(err);
                }
            message.id = 'chat:message:' + channel + ':' + count;
            message.data.id = ('msg' + channel.replace(/\//gi, '-') + count);
            console.log('Sending:',channel, message, store);
            app.sockets.in(channel).json.emit('chat', message);
            if(store)
                this.storeMessage(channel, message);
            
        }.bind(this));
    },
    storeMessage:function(cname, message){
        if(message.data) message.data = JSON.stringify(message.data);
        if(message.user) message.user = JSON.stringify(message.user);
        message.channel = cname;
        //console.log('Storing: ', cname, message);
        this.redis.multi()                              // Start transaction
        .hmset(message.id, message)                     // Store message
        .expire(message.id, this.options.timeout)       // Expire after (timeout) seconds
        .exec();                                        // Execute transaction
    },
    getQueue:function(cname, cb){
        this.redis.keys('chat:message:' + cname + ':*', cb);
    },
    clearChannel:function(channel){
        delete this.storage[channel];
    },
    
    /* 
     * States: idle, writing, deleting, textready, disconnected
     * Roles:  guest, user, moderator, admin or fetched from db?
     *
     */
    removeRoom:function(cname){
        delete this.users[cname];
    },
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
    
    sessionHook:function(client, message){
        //console.log(message);
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + (message.data.whisper ? '/' + message.data.whisper : '');
        switch(message.data.action){
            case "enter":
                client.join(cname); 
                client.join(cname + '/' + client.session.user.name); // Whisper
                if(message.data.noqueue)
                    break;
            case "queue":
                this.getQueue(cname, function(err, queue){
                    if(err) throw err;
                    var m = this.redis.multi();
                    for(var i = queue.length - 1; i; i--){
                        m.hgetall(queue[i]);
                    }
                    m.exec(function(err, obj){
                        if(err) throw err;
                        obj.sort(function(a,b){var c = a.time - b.time;return (c < 0 ? -1 : (c ? 1 : 0));});
                        //console.log(obj);
                        for(var i = 0; i < obj.length; i++){
                            //console.log(obj[i]);
                           if(obj[i].data) obj[i].data = JSON.parse(obj[i].data);
                           if(obj[i].user) obj[i].user = JSON.parse(obj[i].user);
                        }
                        client.json.emit('chat', {cmd:'chat', data:{action:'queue', queue:obj}});
                    }.bind(this));
                }.bind(this));
                break;
            case 'userlist':
                client.json.emit('chat', {cmd:'chat', data:{action:'userlist', list:this.getUsersO(cname)}});
                break;
            case 'leave':
                client.leave(cname);
                client.leave(cname + '/' + client.session.user.name);
                break;
            case 'post':
                console.log('NEW POST', client.get('user'));
                if(this.getUserNames(cname).indexOf(client.session.user.name) == -1){
                    console.log('returning: ', this.getUserNames(cname), client.session.user.name);
                    return;
                    }
                if(!message.data.color && client.session.user.preferences && client.session.user.preferences.chat)
                    message.data.color = client.session.user.preferences.chat.color || '#fff' ;
                if(message.data.whisper == client.session.user.name){
                    cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null');
                    delete message.data.whisper;
                }else if(message.data.whisper){
                    message.data.message = message.data.message.substr(message.data.message.indexOf('#')+1);
                    var cname2 = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + '/' + client.session.user.name;
                    this.storeMessage(cname2, message);
                }
                console.log('NEW POST 2');
                this.sendMessage(cname, message, true);
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
                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:client.session.user.name + ' vyhodil uživatele ' + message.data.params.param + ' z místnosti.'}}, true);
                            this.sysMsg(cname + '/' + message.data.params.param, {cmd:'chat', data:{action:'force-leave', silent:true}});
                        }else
                            client.send('notify', {code:403,msg:'Not allowed'});
                        break;
                    case 'kickall':
                        if(client.session.isAllowed('chat', 'moderator')){
                            this.sysMsg(cname, {cmd:'chat', data:{action:'force-leave', silent:true}});
                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:client.session.user.name + ' vyhodil všechny z místnosti.'}}, true);
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
                    var msgid = 'chat:message:' + cname + message.data.messid;
                    this.redis.del(msgid);
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
                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' přichází do místnosti.'}}, true);
                }
                break;
            case "leave":
                if(this.getUserNames(cname).binarySearch(message.data.name) != -1){
                    this.removeUser(cname, message.data.name);
                    if(!message.data.silent){
                        this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchází z místnosti.'}}, true);
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