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
    redis:null,
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
        //console.log('Try send', message);
        this.redis.incr('chat:msgid:' + channel, function(err, count){
            if(err){
                console.log(err);
                throw new Error(err);
                }
            message.id = 'chat:message:' + channel + ':' + count;
            message.data.id = ('msg' + channel.replace(/\//gi, '-') + '-' + count);
            //console.log('Sending:',channel, message, store);
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
    
    /* 
     * States: idle, writing, deleting, textready, disconnected
     * Roles:  guest, user, moderator, admin or fetched from db?
     *
     */
    uRoomName:function(cname){
        return 'chat:room-occupants:' + cname;
    },
    /*sortUsers:function(a,b){
        a = String(a.name).toUpperCase();
        b = String(b.name).toUpperCase();
        if(a > b) return 1;
        else if(a == b) return 0;
        return -1;
    },*/
    addUser:function(room, user, info){
        delete info.permissions;
        info.name = user;
        info.state = 'idle';
        info.time = new Date().getTime();
        var rname = this.uRoomName(room);
        this.redis.hmset(rname + ':' + user, info);
        this.broadcastUserUpdate(room);
    },
    removeUser:function(room, user){
        this.redis.del(this.uRoomName(room) + ':' + user);
        this.broadcastUserUpdate(room);
    },
    getUsers:function(room, cb){
        this.redis.keys(this.uRoomName(room) + ':*', function(err, names){
            var m = this.redis.multi();
            for(var i = 0; i < names.length; i++)
                m.hgetall(names[i]);
            m.exec(cb);
        }.bind(this));
    },
    updateUser:function(room, user, data){
        this.redis.hmset(this.uRoomName(room) + ':' + user, data);
    },
    getUserNames:function(cname, cb){
        this.getUsers(cname, function(err, usr){
            var r = [];
            for(var i = 0; i < usr.length; i++)
                r.push(usr[i].name);
            cb(r);
        });
    },
    getUserProperty:function(client, property, cb){
        client.get('session-id', function(err, id){
            this.redis.hget('session-' + id + '-user', property, cb.bind(this));
        }.bind(this));
    },
    broadcastUserUpdate:function(cname){
        this.getUsers(cname, function(err, res){
            this.sysMsg(cname, {cmd:'chat', data:{action:'userlist', list:res}}, false);
        }.bind(this));
    },
    sessionHook:function(client, message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + (message.data.whisper ? '/' + message.data.whisper : '');
        switch(message.data.action){
            case "enter":
                client.join(cname); 
                this.getUserProperty(client, 'name', function(err, name){
                        client.join(cname + '/' + name); // Whisper
                });
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
                           if(obj[i].data) obj[i].data = JSON.parse(obj[i].data);
                           if(obj[i].user) obj[i].user = JSON.parse(obj[i].user);
                        }
                        client.json.emit('chat', {cmd:'chat', data:{action:'queue', queue:obj}});
                    }.bind(this));
                }.bind(this));
                break;
            case 'userlist':
                this.getUsers(cname, function(err, obj){
                    client.json.emit('chat', {cmd:'chat', data:{action:'userlist', list:obj}});
                });
                break;
            case 'leave':
                client.leave(cname);
                client.leave(cname + '/' + client.session.user.name);
                break;
            case 'post':
                this.getUserProperty(client, 'name', function(err, name){
                    this.getUserNames(cname, function(usrs){
                        if(usrs.indexOf(name) != -1){
                            this.getUserProperty(client, 'preferences', function(err, prefs){
                                prefs = JSON.parse(prefs);
                                message.user = {name: name};
                                if(!message.data.color && prefs && prefs.chat)
                                    message.data.color = prefs.chat.color || '#fff' ;
                                if(message.data.whisper == name){
                                    cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null');
                                    delete message.data.whisper;
                                }else if(message.data.whisper){
                                    message.data.message = message.data.message.substr(message.data.message.indexOf('#')+1);
                                    var cname2 = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + '/' + name;
                                    this.storeMessage(cname2, message);
                                }
                                this.sendMessage(cname, message, true);
                                this.redis.hmset(this.uRoomName(cname) + ':' + name, {'time':new Date().getTime()});
                            }.bind(this));
                        }
                    }.bind(this));
                }.bind(this));
                break;
            case 'state':
                this.getUserProperty(client, 'name', function(err, name){
                    this.redis.hmset(this.uRoomName(cname) + ':' + name, {'time':new Date().getTime(), state : message.data.state});
                }.bind(this));
                break;
            case 'cmd':
                switch(message.data.command){
                    case 'kick':
                        client.isAllowed('chat', 'moderator', function(a){
                            if(a){
                                this.getUserProperty(client, 'name', function(err, name){
                                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:name + ' vyhodil uživatele ' + message.data.params.param + ' z místnosti.'}}, true);
                                    this.sysMsg(cname + '/' + message.data.params.param, {cmd:'chat', data:{action:'force-leave', silent:true}});
                                }.bind(this));
                            }else{
                                client.send('notify', {code:403,msg:'Not allowed'});
                            }
                        }.bind(this));
                        break;
                    case 'kickall':
                        client.isAllowed('chat', 'moderator', function(a){
                            if(a){
                                this.getUserProperty(client, 'name', function(err, name){
                                    this.sysMsg(cname, {cmd:'chat', data:{action:'force-leave', silent:true}});
                                    this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:name + ' vyhodil všechny z místnosti.'}}, true);
                                }.bind(this));
                            }else{
                                client.send('notify', {code:403,msg:'Not allowed'});
                            }
                        }.bind(this));
                        break;
                    case 'sys':
                        this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.params.param}}, true);
                        break;
                    default:
                        console.log(message);
                }
                break;
            case 'delete':
                client.isAllowed('chat', 'moderator', function(a){
                    if(a){
                        var msgid = 'chat:message:' + cname + ':' + message.data.messid.substr(message.data.messid.lastIndexOf('-') + 1);
                        this.redis.del(msgid);
                        this.sysMsg(cname, {cmd:'chat', data:{action:'delete', message:message.data.messid}});
                    }
                }.bind(this));
                break;
        }
    },
    unixHook:function(message){
        var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.room || 'null');
        switch(message.data.action){
            case "enter":
                this.getUsers(cname, function(names){
                    if(!names || names.indexOf(message.data.name) == -1){
                        this.addUser(cname, message.data.name, message.data.info);
                        this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' přichází do místnosti.'}}, true);
                    }  
                }.bind(this))
                break;
            case "leave":
                this.getUserNames(cname, function(names){
                    if(names && names.indexOf(message.data.name) != -1){
                        this.removeUser(cname, message.data.name);
                        if(!message.data.silent){
                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:message.data.name + ' odchází z místnosti.'}}, true);
                        }
                    }
                    cname += '/' + message.data.name;
                    this.sysMsg(cname, {cmd:'chat', data:{'action':'force-leave', silent:true}});
                }.bind(this));
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