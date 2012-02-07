var starttime = new Date().getTime();
console.log('Server started at ' + new Date());
require('mootools.js').apply(GLOBAL);

/*
 * Module loading
 */
    /* System modules */
var http = require('http'),
    io = require('socket.io@0.8.5'),
    net = require('net'),
    fs = require('fs'),
    utility = require('./modules/utility.js'),
    child = require('child_process'),
    yaml = require('js-yaml'),
    redis = require('redis'),
    
    /* Other variables */
    Config = yaml.load(fs.readFileSync('../config/config.neon', 'utf8')).production.parameters;
    app = null,
    
    
    /* Custom modules */
    Session = require('./modules/session.js').Session,
      
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
     * @var     redisHook(@object message,
     *                    @object client, @string channel)  Method to handle messages comming on pubsub
     * @string  unixHook(@object message)                   Method to handle communication from PHP
     * @var     sessionHook(@object message,
     *                      @object client)                 Method to handle messages comming from client.
     */
    Modules = {
        /* Public api */
        register:function(name){
            var module = require('./modules/' + name + '.js');
            var namespace = this.app.sockets; //of('/' + name);
            return this.list[name] = module.create(namespace, Config);
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
        redisHook:function(message, client, channel){
            return this.list[message.cmd].redisHook(message, client, channel);
        },
        unixHook:function(message){
            return this.list[message.command].unixHook(message);
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
    utility.apply(GLOBAL);
    
    
    /*
     * Setup servers 
     */
    
    /* Main WS server@8000 */
    var server = http.createServer(function(req, res){        
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.write('<h1>System information</h1>\n');
        res.write(
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
        res.write('<pre>\n');
        res.write(JSON.stringify(SessionManager.list()));
        /*SessionManager._s.each(function(item,index){
            res.write(index + '\n');
        });*/
        res.write('</pre>\n');
        res.end('<hr />\nCreated by <a href="http://aragorn.cz/">Aragorn.cz</a> &copy; 2011');
    }),
   
    /* 
     * Session storage 
     * @void    remove(@int id)     Callback function for deleting sessions
     * @object+ session%            Every single session
     * */
     SessionManager = {
        createSession:function(){
            var sid;
            do{
                sid = Math.round(Math.random() * 10000000000000000);
            }while(this._s[sid]);

            this._s[sid] = new Session(sid, {
                parentStorageRemoval:this.remove.bind(this),
                redisHook:Modules.redisHook.bind(Modules),
                //modulesManager:Modules
            });
            this._s[sid].sessid = sid;
            return this._s[sid];
        },
        remove:function(id){
            //console.log(this._s);
            if(this._s[id]) delete this._s[id];
            //console.log(this._s);
        },
        exists:function(id){
            return !!this._s[id];
        },
        get:function(id){
            return this._s[id] || null;
        },
        length:function(){
            //return this._s.lenght;
            var i = 0;
            for(var m in this._s)
                if(typeOf(this._s[m]) != 'function')
                    i++;
            //console.log(this._s);
            return i;
        },
        list:function(){
            var r = [];
            for(var m in this._s)
                if(typeOf(this._s[m]) != 'function'){
                    r.push(this._s[m].sessid);
                }
            return r.sort();
        },
        _s:{}
    },
    phpUnixSocket = net.createServer(function(s) {
        s.setEncoding('utf-8');
        s.on('data', function(data){
            var json = JSON.parse(data);
            //console.log(json);
            if(json && json.command){
                switch(json.command){
                    case "user-login":
                        var s = SessionManager.get(json.data.nodeSession) || SessionManager.createSession(json.data.PHPSESSID);
                        s.user.id = json.data.id;
                        s.user.name = json.data.username;
                        s.user.preferences = json.data.preferences;
                        s.user.permissions = json.data.permissions;
                        s.user.roles = json.data.roles;
                        //console.log(s.user);
                        this.write(s.sessionId + '');
                        break;
                    case "user-logout":
                        var sess = SessionManager.get(json.data.nodeSession);
                        if(sess){
                            sess.erase();
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
                        break;
                    case 'get-number-of-sessions':
                        this.write(SessionManager.length());
                        break;
                    case 'get-number-of-clients':
                        this.write(io.sockets.clients().length);
                        break;
                    default:
                        if(Modules.isSet(json.command))
                            this.write(Modules.unixHook(json));
                        else
                            this.write('UNKNOWN_CMD');
                }
            }else{
                this.write("BAD_FORMAT");
            }
        }.bind(s));
    });
server.listen(parseInt(Config.port));
console.log('Socket.io listening at port ' + Config.port);

/*
 * Creates unix socket at target location for PHP => node.js communication
 * Faster than standart socket + safe from outer connections
 */

var oldUmask = process.umask(0000);
phpUnixSocket.listen(Config.usock, function() {
  process.umask(oldUmask);
});
console.log('Unix socket opened in ' + Config.usock);


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
});

app.sockets.on('connection', function (client) {
    /*
     *  Implement basic remote-client <=> node.js <=> redis protocol
     */    
    client.on('SESSION_SID', function(sid){
        if(!SessionManager.exists(sid)){
            client.emit('SESSION_RESET_SID');
        }else{
            var s = SessionManager.get(sid);
            s.registerClient(client);
            client.set('session', s);
            client.emit('SESSION_CONFIRMED_SID', sid);
        }
    });
    client.on('SESSION_REQUEST_SID', function(){
        var s = SessionManager.createSession();
        var sid = s.sessid;
        s.registerClient(this);
        client.emit('SESSION_REGISTER_SID', sid);
    });
    Modules.setupClient(client);
    client.on('disconnect', function(){
        var s = client.session;
        if(s) s.removeClient(client);
    });
    
    /* When all events are set up, client is requested to identify himself, otherwise server will register him as new client. */
    client.on('PING', function(){ 
        client.emit('PING');
    });
    client.emit('SESSION_REQUEST_IDENTITY');
    client.on('SESS', function(){console.log(client.session.user);});
});

/*
 * Load modules
 * Module list in config.ini
 * Names separated by comma (,)
 */
var mods = Config.modules.split(',');
Modules.setapp(app);
if(mods.length && mods[0]){
    console.log('Modules found: ' + mods.length);
    mods.each(function(name){
        console.log('Registering module ' + name);
        Modules.register(name);
        /*child.spawn('node', ['/var/www/node/module.js', '/var/www/node/modules/' + name, name], {cwd:process.cwd(), customFds: [-1, 1, 1]});*/
    });
    console.log(mods.length + ' modules were launched.');
}else{
    console.log('Server loaded without modules.');
}
