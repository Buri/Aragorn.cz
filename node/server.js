var starttime = new Date().getTime();
console.log('Thread started at ' + new Date());
require('mootools');

/*
 * Module loading
 */
    /* System modules */
var http = require('http'),
    io = require('socket.io'),
    net = require('net'),
    fs = require('fs'),
    utility = require('./modules/utility.js'),
    child = require('child_process'),
    yaml = require('js-yaml'),
    redis = require('redis');
    RedisStore = require('socket.io/lib/stores/redis'),
    Tracer = require('tracer'),
    log = Tracer.colorConsole();
    
    utility.apply(GLOBAL);
    /* Other variables */
    //var Config = yaml.load(fs.readFileSync('../config/config.neon', 'utf8')).production.parameters;
    var Config = require('./modules/config.js').parse();
    app = null,
    
    
    /* Custom modules */
    //Session = new Class(),//= require('./modules/session.js').Session,
      
    /*
     * Module loading system
     * Mods are loaded from modules directive in config.ini (modules=chat,forum)
     * Public api
     * @object  register(@string name)                      Loads module
     * @void    remove(@string name)                        Unloads module
     * @object  alias(@string target, @string alias)        Creates alias for existing module
     * @object  get(@string name)                           Returns module if exists
     * @bool    isSet(@string name)                         Returns true if module is loaded
     * 
     * Each module is required to have folowing api:
     * @object  create()                                    Returns new instance of module
     * @string  unixHook(@object message)                   Method to handle communication from PHP
     * @var     sessionHook(@object message,
     *                      @object client)                 Method to handle messages comming from client.
     */
     Modules = {
        /* Public api */
        register:function(name){
            var path = fs.readlinkSync('./enabled_modules/' + name);
            console.log('Loading module ' + name + ': ' + path);
            var module = require(path);
            module.launchurl = name;
            var namespace = this.app.sockets;
            var handle = module.info.handle;
            var c = module.create(namespace, Config);
            this.list[handle] = c;
            return this.list[handle];
        },
        remove:function(name){
            if(this.list[name]) delete this.list[name];
        },
        alias:function(target, alias){
            this.list[alias] = this.list[target];
            return this[target];
        },
        get:function(name){return (this.list[name] || false);},
        isSet:function(name){return this.list[name] ? true : false;},        
        
        /* Private api */
        unixHook:function(message, socket){
            return this.list[message.command].unixHook(message, socket);
        },
        sessionHook:function(message, client){
            if(this.list[message.cmd])
                return this.list[message.cmd].sessionHook(message, client);
            console.error('Undefined message', message);
            return false;
        },
        setupClient:function(client){
            this.getList().each(function(mod){
                //console.log(mod);
                client.on(mod, this.list[mod].sessionHook.bind(this.list[mod], client));
            }.bind(this));
        },
        getList:function(){
            var r = [];
            for(var m in this.list) r.push(m);
            return r;
        },
        setapp:function(a){
            this.app = a;
        },
        app:null,
        list:{}
    };
    
    /*
     * Setup servers 
     */
    
    /* Main WS server@8000 */
    var server = http.createServer(function(req, res){        
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.write('<h1>System information</h1>\n');
/*        res.write(
            '<style type="text/css">.b{font-weight:bold;}</style>\n' +
            '<table>\n' +
            '<tr><th colspan="2">Server</th></tr>\n' +
            '<tr><td class="b">Software:</td><td>Node@' + process.version + '</tr></td>\n' +
            '<tr><td class="b">Process:</td><td>' + process.title + ' (pid: ' + process.pid + ')</tr></td>\n' +
            '<tr><td class="b">Platform:</td><td>' + process.platform + '</tr></td>\n' +
            '<tr><td class="b">Memory usage:</td><td>' + process.memoryUsage().rss.bytes2string() + '/1024MB</tr></td>\n' +
            '<tr><td class="b">Uptime:</td><td>' + utility.serverUptime(starttime) + '</tr></td>\n' +
            '<tr><th colspan="2">Software</th></tr>\n' +
            '<tr><td class="b">Modules:</td><td>' + Modules.getList().combine(['session', 'utils']).sort().join(', ') + '</tr></td>\n' +
            '<tr><td class="b">Sessions:</td><td>' + SessionManager.length() + '</tr></td>\n' +
            '<tr><td class="b">Clients:</td><td>' + JSON.stringify(io) + '</tr></td>\n' +
            '</table>\n'
        ); 
        /*res.write('<pre>\n');
        res.write(JSON.stringify(SessionManager.list()));
        res.write('</pre>\n');*/
        res.end('<hr />\nCreated by <a href="http://aragorn.cz/">Aragorn.cz</a> &copy; 2011');
    }),
   
    phpUnixSocket = net.createServer(function(s) {
        s.setEncoding('utf-8');
        s.on('data', function(data){
            var json = JSON.parse(data);
            //console.log(json);
            if(json && json.command){
                switch(json.command){
                    case "user-login":
                        SessionManager.login(json.data.nodeSession, json.data, function(sid){
                            this.write(sid + '');
                        }.bind(this));
                        break;
                    case "user-logout":
                        SessionManager.logout(json.data.nodeSession);
                        this.write('OK');
                        break;
                    case 'get-number-of-sessions':
                        SessionManager.length(function(r){
                            this.write(JSON.stringify(r));
                        }.bind(this));
                        break;
                    case 'get-number-of-clients':
                        SessionManager.redis.get('connection-counter', function(err, res){
                            this.write(JSON.stringify(parseInt(res)));
                        }.bind(this));
                        break;
                    case 'user-is-online':
                        SessionManager.redis.exists('user-' + json.data.uid, function(err, res){
                            this.write(JSON.stringify(parseInt(res)));
                        }.bind(this));

                        break;
                    default:
                        if(Modules.isSet(json.command))
                            this.write(Modules.unixHook(json, this));
                        else
                            this.write('UNKNOWN_CMD');
                }
            }else{
                this.write("BAD_FORMAT");
            }
        }.bind(s));
    });
server.listen(parseInt(Config.node.port));
console.log('Socket.io listening at port ' + Config.node.port);

/*
 * Creates unix socket at target location for PHP => node.js communication
 * Faster than standart socket + safe from outer connections
 */
phpUnixSocket.listen(Config.node.phpbridge.socket, function() {
//    console.log('server listening');
    fs.chmodSync(Config.node.phpbridge.socket, 0777);
});
console.log('Unix socket opened in ' + Config.node.phpbridge.socket);

/*
 * Socket.io server
 * Handles low-level clinent-server comunication
 * Implements basic handshake
 * Implements basic session: modules/session.js 
 * Protocol based on json
 * Protocol structure:
 * [{
 * @string  cmd         Command to be executed
 * @int     time        Time when message has been send
 * @int     identity    SID, null if not registerd
 * @object  data        Parameters of command
 * @int?    itime       Time when message was intended to be send, not actual send time (see message batching in client script)
 * }, {...}, {...}, ...]
 * 
 * Step 1: Setup event handling
 * Step 2: Asign low-level commands (< - outgoing, > incoming):
 *         i/o    Name                              Params      Comment
 *          <   SESSION_REQUEST_IDENTITY            ()          All events are set up, client is ready for basic authentication
 *          >   SESSION_SID                         (int sid)   Server recieves client SID (session id) and looks for existing session. If session doesn't exist replies SESSION_RESET_SID, else replies SESION_CONFIRMED_SID and register new client to session
 *          <   SESSION_RESET_SID                   ()          Client is told to reset his SID and send SESSION_REQUEST_SID
 *          <   SESSION_CONFIRMED_SID               ()          Session found, client registered
 *          >   SESSION_REQUEST_SID                 ()          Server generates unique sid and creates new Session() (see module comments for api), then sends SID to client with SESSION_REGISTER_SID
 *          <   SESSION_REGISTER_SID                (int sid)   Sends new SID to client
 *          >   SESSION_HAS_PHPSESSID_REGISTERED    ()          Syncs PHPSESSID and SID
 *          >   %                                   (...)       If clients is registered to session calls Session.handleMessgae(message, client), otherwise logs cmd and replies INVALID_SID
 * Step 3: Send SESSION_REQUEST_IDENTITY command
 */
app = io.listen(server);
app.configure(function(){
    app.set('log level', 0);                    // reduce logging
    app.set('transports', [                     // enable all transports (optional if you want flashsocket)
        'websocket'
      , 'flashsocket'
      , 'htmlfile'
      , 'xhr-polling'
      , 'jsonp-polling'
    ]);
    app.set('store', new RedisStore({
        redisPub:redis.createClient(), 
        redisSub:redis.createClient(), 
        redisClient:redis.createClient()
    }));
});
/* 
     * Session storage 
     * @void    remove(@int id)     Callback function for deleting sessions
     * @object+ session%            Every single session
     * */
     var SessionManager = {
        storageKey:'session-',
        redis:redis.createClient(),
        init:function(){
            /* Clear all sessions from old run */
            console.log('Clearing up old sessions...');
            this.redis.keys('session-*', function(err, res){
                if(err) return;
                var m = this.redis.multi();
                for(var i = 0; i < res.length; i++){
                    m.del(res[i]);
                }
                /* Reset connections counter */
                m.set('connection-counter', 0);
                m.exec();
            }.bind(this));
        },
        removeConnection:function(client){
            var r = this.redis;
            client.get('session-id', function(err, res){
            if(!r.connected) r = redis.createClient();
                var c = this.storageKey + res;
                r.srem(c, client.id, function(err, res){
                    if(err) return;
                    r.smembers(c, function(err, mem){
                        if(err) return;
                        if(!mem || !mem.length){
                            r.multi().expire(c, 35).expire(c + '-user', 35).exec();
                        }
                    });
                });
            }.bind(this));    
            SessionManager.broadcastSessionNumber();
        },
        handleNewConnection:function(client){
            client.redis = this.redis;
            client.isAllowed = this.isAllowed.bind(client);
            
            this.redis.incr('connection-counter');
            
            client.on('SESSION_SID', function(sid){
                SessionManager.exists(sid, function(ex){
                //console.log('Sid exists: ' + (ex ? 'yes' : 'no'));
                if(!ex){ 
                    client.emit('SESSION_RESET_SID');
                }else {
                    SessionManager.redis.sadd(SessionManager.storageKey + sid, client.id);
                    client.set('session-id', sid);
                    client.emit('SESSION_CONFIRMED_SID', sid);
                    client.join('/sessions/' + sid);
                    SessionManager.broadcastSessionNumber();
                }
                }.bind(this));
            });
            client.on('SESSION_REQUEST_SID', function(){
                SessionManager.createSession(function(sid){
                    SessionManager.redis.sadd(SessionManager.storageKey + sid, client.id);
                    SessionManager.setEmptyUser(sid);
                    SessionManager.redis.HMSET(SessionManager.storageKey + sid + '-user', 
                        'ips', JSON.stringify([client.handshake.address.address])
                    );
                    client.set('session-id', sid);
                    client.emit('SESSION_REGISTER_SID', sid);
                    client.join('/sessions/' + sid);
                }.bind(this));
            });
            client.on('disconnect', function(){
                this.redis.decr('connection-counter');
                SessionManager.removeConnection(this);
            });

            /* When all events are set up, client is requested to identify himself, otherwise server will register him as new client. */
            client.on('PING', function(){ 
                client.emit('PING');
            });
            

            client.emit('SESSION_REQUEST_IDENTITY');
        },
        createSession:function(cb){
            var sid = Math.round(Math.random() * 10000000000000000);
            this.redis.exists(this.storageKey + sid + '-user', function(err, res){
                if(err) return;
                if(res){
                    this.createSession(cb);
                }else{
                    cb(sid);
                }
            }.bind(this));
        },
        exists:function(id, cb){
            this.redis.exists(this.storageKey +  id + '-user', function(err, res){
                if(err) return;
                cb(res);
            }.bind(this));
        },
        login:function(sid, user, cb){
            //console.log(sid + ' - ', user);
            this.redis.exists('user-' + user.id, function(err, r){
                if(!r) this.redis.incr('onlineusers-counter');
            }.bind(this));
            var m = this.redis.multi();
            m.hmset(this.storageKey + sid + '-user',
                'id', user.id,
                'name', user.username,
                'preferences', JSON.stringify(user.preferences),
                'permissions', JSON.stringify(user.permissions),
                'roles', JSON.stringify(user.roles)
            );
            m.set('user-' + user.id, sid);
            m.exec(function(err){
                    if(err) return;
                    /*this.redis.hgetall(this.storageKey + sid + '-user'/*, function(e, o){console.log(o);});*/
                    cb(sid);
                }.bind(this));
        },
        logout:function(sid){
            this.redis.decr('onlineusers-counter');
            this.setEmptyUser(sid);
        },
        setEmptyUser:function(sid){
            SessionManager.redis.hgetall(SessionManager.storageKey + sid + '-user', function(err, usr){
//                console.log(usr);
                if(usr)
                    SessionManager.redis.del('user-' + usr.id);
            });
            SessionManager.redis.HMSET(SessionManager.storageKey + sid + '-user', 
                'id', 0,
                'roles', '[]',
                'name', '',
                'preferences', '{}',
                'permissions', '{}',
                'ips', '[]'
            );
        },
        length:function(cb){
            if(!cb) return; 
            this.redis.get('onlineusers-counter', function(err, res){
                cb(res == 'null' ? 0 : parseInt(res) - 1);
            });
        },
        broadcastSessionNumber:function(){
            SessionManager.length(function(sess){
                this.redis.get('connection-counter',function(err, res){
                    app.sockets.emit('SYSTEM_UPDATE_USERS_ONLINE', [sess, res]); 
                });
            }.bind(this));
            
        },
        isAllowed:function(res, op, cb){
            var parseUser = function(err, user){
                    //console.log(user);
                    var ps = user.permissions || {'_ALL':false}; /* This method doesn't care about real permissions, it simply uses what's pushed from PHP */
                    /* User is not logged in */
                    if(!user.id || !user.roles){ 
                        cb(false);
                        return false;
                    }

                    /* If user is root */
                    if(user.roles.indexOf('0') != -1 || !user.id){
                        cb(true);
                        return true; 
                    }

                    var p = ps[res];
                    if(typeOf(p) != 'null'){
                        if(typeOf(p[op]) != 'null'){
                            cb(p[op]);
                            return p[op];
                        }
                        if(typeOf(p['_ALL']) != 'null'){
                            cb(p['_ALL']);
                            return p['_ALL'];
                        }
                        cb(false);
                        return false;
                    }else if(typeOf(ps['_ALL']) != 'null'){
                        cb(ps['_ALL']);
                        return ps['_ALL'];
                    }
                    cb(false);
                    return false; /* Fallback */
                }
            if(this.userData){
                var fetch = false;
                if(this.userData.cacheTime + 30*1000 > new Date().getTime()){ // Use 30s cache if aviable
                    //console.log('Using cached perms');
                    parseUser(null, this.userData);
                }else{
                    fetch = true;
                }
            }else{
                fetch = true;
            }
            if(fetch){
                this.get('session-id', function(err, id){
                    delete this.userData;
                    if(!this.redis.connected) this.redis = redis.createClient();
                    this.redis.hgetall('session-' + id + '-user', function(err, user){
                        if(err) return;
                        console.log(user);
                        user.permissions = JSON.parse(user.permissions);
                        user.roles = JSON.parse(user.roles);
                        this.userData = user;
                        this.userData.cacheTime = new Date().getTime();
                        //console.log('Using fetched perms');
                        parseUser(err, user);
                    }.bind(this));
                }.bind(this));
            }
        }
    };
    SessionManager.init();
    
/* Handle new connection */    
app.sockets.on('connection', function (client) {   
    SessionManager.handleNewConnection(client);
    Modules.setupClient(client);
});

/*
 * Load modules
 * Module list in config.ini
 * Names separated by comma (,)
 */
var mods = fs.readdirSync('./enabled_modules');
Modules.setapp(app);
if(mods.length && mods[0]){
    console.log('Modules found: ' + mods.join(', '));
    mods.each(function(name){
        //console.log('Registering module ' + name);
        Modules.register(name);
    });
    console.log(mods.length + ' modules were launched.');
}else{
    console.log('Server loaded without modules.');
}
