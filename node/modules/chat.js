require('mootools'); 
exports.info = {
    'authors':['Buri'],
    version:{
        major:0,
        minor:2,
        build:0,
        toString:function(){
            return this.major + '.' + this.minor + '.' + this.build;
        }
    },
    handle:'chat',
    autoRestart:true
}
exports.create = function(namespace, conf){
    return new exports.ChatServer(namespace, {
        userServer: conf.servers.userContent
    });
}

var   redis = require('redis'),
        User = new Class({
            channel:'',
            id:0,
            name: '',
            time:0,
            state:'idle',
            info:{
                color:'#fff',
                icon:'http://stat.aragorn.cz/i/default.png',
                permissions:{
                    '_ALL':false
                }
            },
            server:null,
            initialize:function(server, id, channel){
                this.redis = server.redis;
                this.server = server;
                this.time = new Date().getTime();
                if(typeOf(id) != 'undefined' && typeOf(channel) != 'undefined'){
                    this.id = id;
                    this.channel = channel;
                    this.reload();
                }
            },
            setServer:function(server){
                this.server = server;
            },
            update:function(cb){
                log.trace('Updating user ' + this.id + ' in channel ' + this.channel)
                var up = {
                    id:this.id,
                    channel:this.channel,
                    name:this.name,
                    time: this.time,
                    state:this.state,
                    info:JSON.stringify(this.info)
                };
                this.redis.hmset(this.channel + ':user-info/' + this.id, up, function(err, res){
                    this.server.Users.getList(this.channel,  function(err, obj){
                            app.sockets.in(this.channel).json.emit('chat', {cmd:'chat', data:{action:'userlist', list:obj}});
                            if(typeOf(cb) == 'function')
                                cb(err, res);
                    }.bind(this));
                }.bind(this));

            },
            reload:function(cb){
                if(this.id){
                    this.redis.hgetall(this.channel + ':user-info/' + this.id, function(err, res){
                        if(err || !res){
                            log.warn('Cannot load information about user')
                            return;
                        }
                        this.id = res.id;
                        this.channel = res.channel;
                        this.name = res.name;
                        this.info = JSON.decode(res.info);
                        this.time = res.time;
                        this.state = res.state;
                        log.trace('User ' + this.id + ' in channel' + this.channel + ' relaoded');
                        if(cb)
                            cb(err, res);
                    }.bind(this));
                }
            }
        });

exports.ChatServer = new Class({
    Implements:[Options, Events],
    options:{
        length:50,          // How many messages to store in one channel? Defaults to 500
        timeout:15*60,       // How long before message is deleted, defaults to 15 minutes
        userServer:'stat.aragorn.cz' // From where to serve icons?
    },
    redis:null,
    socket:null,
    storage:{},
    users:{},
    
    initialize:function(socket, options){
        this.setOptions(options);
        this.redis = redis.createClient();
        this.socket = socket;

        /* Bindings */
        this.Users.redis = this.redis;
        this.Users.parent = this;
        this.Messages.redis = this.redis;
        this.Messages.parent = this;
        this.Hooks.parent = this;
        this.Hooks.redis = this.redis;

        /* Hooks */
        this.unixHook = this.Hooks.unix.bind(this);
        this.sessionHook = this.Hooks.session.bind(this);
    },
    Users:{
        create:function(cname, id){
            return new User(this.parent, cname, id);
        },
        add:function(channel, user){
            var usr = this.create();
            usr.id = user.uid;
            usr.name = user.name;
            usr.info = user.info
            usr.channel = channel
            log.trace('New user in chat ' + channel + ': ' + usr.id);
            usr.update();
            this.redis.sadd(channel + ':users', usr.id, function(err, res){
                log.trace('Updated users in channel ' + channel + ':users')
            });
        },
        getIds:function(channel, cb){
            this.redis.smembers(channel + ':users', cb);
        },
        remove:function(user, cb){
            this.redis.multi()
            .srem(user. channel + ':users', user.id)
            .del(user.channel + ':user-info/' + user.id)
            .exec(typeOf(cb) == 'function' ? cb : null);
        },
        getList:function(cname, callback){
            this.redis.smembers(cname + ':users', function(err, ids){
                var m = this.redis.multi();
                for(var i = 0; i < ids.length; i++){
                    m.hgetall(cname + ':user-info/' + ids[i]);
                }
                m.exec(callback || null)
            }.bind(this));
        }
    },
    Messages:{
        system:function(channel, data, store){
            var message = {};
            message.user = {
                id:0,
                name:'System'
            };
            message.cmd = 'chat';
            message.time = new Date().getTime();
            message.data = data || {};
            message.data.color = '#7B6200';
            message.data.size = 'small';
            this.send(channel, message, store);
        },
        send:function(channel, message, store){
            this.redis.incr('chat:msgid:' + channel, function(err, count){
                if(err){
                    log.error(err);
                    throw new Error(err);
                }
                message.id = 'chat:message:' + channel + ':' + count;
                message.data.id = ('msg' + channel.replace(/\//gi, '-') + '-' + count);
                log.trace('Sending message in ' + channel, message)
                app.sockets.in(channel).json.emit('chat', message);
                if(store)
                    this.store(channel, message);

            }.bind(this));
        },
        store:function(cname, message){
            if(message.data) message.data = JSON.stringify(message.data);
            if(message.user) message.user = JSON.stringify(message.user);
            message.channel = cname;
            log.trace('Storing message: ', cname, message);
            this.redis.multi()                                                          // Start transaction
            .hmset(message.id, message)                                     // Store message
            .expire(message.id, this.parent.options.timeout)          // Expire after (timeout) seconds
            .exec();                                                                      // Execute transaction
        },
        getQueue:function(cname, cb){
            this.redis.keys('chat:message:' + cname + ':*', cb);
        }
    },
    Hooks:{
        /**
         *
         * @param Object message
         * @param Socket socket
         */
        unix:function(message, socket){   
            var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.room || 'null');
            switch(message.data.action){
                case "enter":
                    this.Users.add(cname, message.data);
                    this.Messages.system(cname, {action:'post', message:message.data.name + ' přichází do místnosti.'}, true);
                    break;
                case "leave":
                    this.Users.remove({
                        channel:cname,
                        id: message.data.uid,
                        name : message.data.name
                    }, function(err, res){
                        log.warn('User removed', res);
                        if(!message.data.silent){
                                this.Messages.system(cname, {action:'post', message:message.data.name + ' odchází z místnosti.'}, true);
                        }
                        cname += '/' + message.data.name;
                        this.Messages.system(cname, {'action':'force-leave', silent:true});
                    }.bind(this));
                    break;
                case "user-name-list":
                    this.Users.getIds(cname, function(err, res){
                        socket.write(JSON.stringify(res));
                    })
                    return '';
                    break;
                default:
                    return ('BAD_PARAM');
            }
            return "OK";
            },
            session:function(client, message){
                var cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + (message.data.whisper ? '/' + message.data.whisper : '');
                log.trace('Chat session message\n', message)
                switch(message.data.action){
                    case "enter":
                        log.debug('User joining chat room');
                        client.join(cname);
                        this.redis.hgetall('session-' + message.identity + '-user', function(err, res){
                            if(err || res.id == '0'){
                                log.warn('User tried enter without correct identity, rejected');
                                client.json.emit('chat', {message:'rejected'});
                                return;
                            }
                            client.chat = {
                                id:res.id,
                                channel:cname
                            };
                            client.on('disconnect', function(){
                                log.debug('Client diconnected from chat');
                                /* Setup disconnect as removing user from chat */
                                log.debug('Client disconected without leaving room:', client.chat);
                                this.Users.remove(client.chat);
                            }.bind(this));
                            client.join(cname + '/' + res.name); // Whisper
                        }.bind(this));
                        if(message.data.noqueue)
                            break;
                    case "queue":
                        log.trace('Retrieving queue in ' + cname);
                        this.Messages.getQueue(cname, function(err, queue){
                            if(err) throw err;
                            log.trace('Queue ids', queue);
                            var m = this.redis.multi();
                            for(var i = 0; i < queue.length; i++){
                                m.hgetall(queue[i]);
                            }
                            m.exec(function(err, obj){
                                if(err) throw err;
                                log.trace('Fetched messages in queue', obj);
                                obj.sort(function(a,b){var c = a.time - b.time;return (c < 0 ? -1 : (c ? 1 : 0));});
                                for(var i = 0; i < obj.length; i++){
                                if(obj[i].data) obj[i].data = JSON.parse(obj[i].data);
                                if(obj[i].user) obj[i].user = JSON.parse(obj[i].user);
                                }
                                client.json.emit('chat', {cmd:'chat', data:{action:'queue', queue:obj}});
                            }.bind(this));
                        }.bind(this));
                        break;
                    case 'userlist':
                        this.Users.getList(cname,  function(err, obj){
                            log.trace('Users in chat', obj);
                            client.json.emit('chat', {cmd:'chat', data:{action:'userlist', list:obj}});
                        });
                        break;
                    case 'leave':
                        client.leave(cname);
                        client.leave(cname + '/' + client.session.user.name);
                        break;
                    case 'post':
                        var user = this.Users.create();
                        client.get('session-id', function(err, id){
                            this.redis.hgetall('session-' + id + '-user', function(err, usr){
                                this.redis.sismember(cname + ':users', usr.id, function(errrrr, isMember){
                                    if(isMember != 1){
                                        log.warn('User tried to send message while not in room');
                                        return;
                                    }
                                    user.channel = cname;
                                    user.id = usr.id;
                                    user.reload(function(){
                                        message.user = {
                                            name: user.name
                                        };
                                        if(!message.data.color && user.info && user.info.color)
                                                message.data.color = user.info.color || '#fff' ;
                                        if(message.data.whisper == user.name){
                                            cname = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null');
                                            delete message.data.whisper;
                                        }else if(message.data.whisper){
                                            message.data.message = message.data.message.substr(message.data.message.indexOf('#')+1);
                                            var cname2 = '/chat/' + (message.data.type || 'public') + '/' + (message.data.rid || 'null') + '/' + user.name;
                                            this.Messages.store(cname2, message);
                                        }
                                        message.data.message = this.formatUrl(message.data.message);
                                        client.isAllowed('chat', 'moderator', function(a){
                                            if(!a)
                                                message.data.message = this.stripHTML(message.data.message);
                                            this.Messages.send(cname, message, true);
                                            user.time = new Date().getTime();
                                            user.update();
                                        }.bind(this));
                                    }.bind(this));
                                }.bind(this));
                            }.bind(this));
                        }.bind(this));
                        break;
                    case 'state':
                        //log.warn('State', message);
                        var user2 = this.Users.create();
                        client.get('session-id', function(err, id){
                            this.redis.hgetall('session-' + id + '-user', function(err, usr){
                                user2.channel = cname;
                                user2.id = usr.id;
                                user2.reload(function(){
                                    user2.state = message.data.state;
                                    user2.time = new Date().getTime();
                                    user2.update();
                                }.bind(this));
                            }.bind(this));
                        }.bind(this));
                        break;
                    case 'cmd':
                        switch(message.data.command){
                            case 'kick':
                                client.isAllowed('chat', 'moderator', function(a){
                                    if(a){

                                        /*this.getUserProperty(client, 'name', function(err, name){
                                            this.redis.del(this.uRoomName(cname) + ':' + message.data.params.param);
                                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:name + ' vyhodil uživatele ' + message.data.params.param + ' z místnosti.'}}, true);
                                            this.sysMsg(cname + '/' + message.data.params.param, {cmd:'chat', data:{action:'force-leave', silent:true}});
                                            this.broadcastUserUpdate(cname);
                                        }.bind(this));*/
                                    }else{
                                        client.send('notify', {code:403,msg:'Radši tu moc nezkoušej nikoho kopat, nebo někdo kopne tebe.'});
                                    }
                                }.bind(this));
                                break;
                            case 'kickall':
                                client.isAllowed('chat', 'moderator', function(a){
                                    if(a){

                                        /*this.getUserProperty(client, 'name', function(err, name){
                                            this.sysMsg(cname, {cmd:'chat', data:{action:'force-leave', silent:true}});
                                            this.sysMsg(cname, {cmd:'chat', data:{action:'post', message:name + ' vyhodil všechny z místnosti.'}}, true);
                                            this.redis.keys(this.uRoomName(cname) + ':*', function(err, keys){
                                                var m = this.redis.multi();
                                                for(var i = 0; i < keys.length; i++)
                                                    m.del(keys[i]);
                                                m.exec();
                                                this.broadcastUserUpdate(cname);
                                            }.bind(this));
                                        }.bind(this));*/
                                    }else{
                                        client.send('notify', {code:403,msg:'Nemáš právo to tady celý vyhodit do vzduchu, co si o sobě jako myslíš? :P'});
                                    }
                                }.bind(this));
                                break;
                            case 'sys':
                                client.isAllowed('chat', 'moderator', function(ia){
                                    if(ia){
                                        this.Messages.system(cname, {action:'post', message:message.data.params.param}, true);
                                    }else{
                                        client.send('notify', {code:403,msg:'Sorry, ty systém neovládáš.'});
                                    }
                                }.bind(this));
                                break;
                            default:
                                log.debug('Recieved unknown command', message);
                        }
                        break;
                    case 'delete':
                        client.isAllowed('chat', 'moderator', function(a){
                            log.debug('Client deleting message?', a);
                            if(a){
                                var msgid = 'chat:message:' + cname + ':' + message.data.messid.substr(message.data.messid.lastIndexOf('-') + 1);
                                this.redis.del(msgid);
                                this.Messages.system(cname, {action:'delete', message:message.data.messid});
                            }else{
                                client.send('notify', {code:403,msg:'Nemáte právo mazat příspěvky'});
                            }
                        }.bind(this));
                        break;
                }
            }
        },
    formatUrl:function(url){
        /*var exp =  /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig; // /(\()((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&'()*+,;=:\/?#[\]@%]+)(\))|(\[)((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&'()*+,;=:\/?#[\]@%]+)(\])|(\{)((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&'()*+,;=:\/?#[\]@%]+)(\})|(<|&(?:lt|#60|#x3c);)((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&'()*+,;=:\/?#[\]@%]+)(>|&(?:gt|#62|#x3e);)|((?:^|[^=\s'"\]])\s*['"]?|[^=\s]\s+)(\b(?:ht|f)tps?:\/\/[a-z0-9\-._~!$'()*+,;=:\/?#[\]@%]+(?:(?!&(?:gt|#0*62|#x0*3e);|&(?:amp|apos|quot|#0*3[49]|#x0*2[27]);[.!&',:?;]?(?:[^a-z0-9\-._~!$&'()*+,;=:\/?#[\]@%]|$))&[a-z0-9\-._~!$'()*+,;=:\/?#[\]@%]*)*[a-z0-9\-_~$()*+=\/#[\]@%])/img;
        url = url.replace(exp, '<a href="$1" target="_blank" class="permalink">$1</a>');
        log.trace('Formating url: ' + url);*/
        return url.trim();
    },
    stipHTML:function(str){
        return str.replace(/<(?:.|\n)*?>/gm, '');
    }
});
