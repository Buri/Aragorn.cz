var starttime = new Date().getTime();
require.paths.push('/var/node/modules/');
require('./node_modules/mootools.js').apply(GLOBAL);
//require('v8-profiler');

/*
 * Module loading
 */
    /* System modules */
var http = require('http'),
    io = require('socket.io'),
    net = require('net'),
    fs = require('fs'),
    utility = require('./modules/utility.js'),
    
    /* Custom modules */
    Session = require('./modules/session.js').Session,
    
    /* Other variables */
    Config = require('node-iniparser').parseSync(__dirname + '/../config/config.ini'),
    socket = null,
    
    /* 
     * Session storage 
     * @void    remove(@int id)     Callback function for deleting sessions
     * @object+ session%            Every single session
     * */
    Clients = {
        remove:function(id){
            if(this['session' + id]) delete this['session' + id];
        },
        length:function(){
            var i = 0;
            for(var m in this)
                if(typeOf(this[m]) != 'function')
                    i++;
            return i;
        }
    },
    
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
            return this.list[name] = module.create(Config);
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
            return this.list[message.cmd].sessionHook(message, client);
        },
        getList:function(){
            var r = [];
            for(var m in this.list) r.push(m);
            return r;
        },
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
            // '<tr><td class="b">Configuration:</td><td>' + JSON.stringify(Config) + '</tr></td>\n' +
            '<tr><td class="b">Modules:</td><td>' + Modules.getList().combine(['session', 'utils']).sort().join(', ') + '</tr></td>\n' +
            '<tr><td class="b">Sessions:</td><td>' + Clients.length() + '</tr></td>\n' +
            '<tr><td class="b">Clients:</td><td>' + JSON.stringify(io) + '</tr></td>\n' +
            '</table>\n'
        );
        res.end('<hr />\nCreated by <a href="http://aragorn.cz/">Aragorn.cz</a> &copy; 2011');
    }),
   
    /*
     *
     *
     */
    phpUnixSocket = net.createServer(function(s) {
        s.setEncoding('utf-8');
        s.on('data', function(data){
            var json = JSON.parse(data);
            if(json && json.command){
                switch(json.command){
                    case "user-login":
                        if(Clients['session' + json.data.nodeSession]){
                            Clients['session' + json.data.nodeSession].phpid = json.data.PHPSESSID;
                            Clients['session' + json.data.nodeSession].user.id = json.data.id;
                            Clients['session' + json.data.nodeSession].user.name = json.data.username;
                            Clients['session' + json.data.nodeSession].user.preferences = json.data.preferences;
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
                        break;
                    case "user-logout":
                        if(Clients['session' + json.data.nodeSession]){
                            Clients['session' + json.data.nodeSession].phpid = '';
                            Clients['session' + json.data.nodeSession].user.id = 0;
                            Clients['session' + json.data.nodeSession].user.name = '';
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
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
server.listen(parseInt(Config.common.port));
console.log('Server listening at port ' + Config.common.port);

/*
 * Load modules
 * Module list in config.ini
 * Names separated by comma (,)
 */
var mods = Config.common.modules.split(',');
if(mods.length && mods[0]){
    console.log('Modules found: ' + mods.length);
    mods.each(function(name){
        console.log('Registering module ' + name);
        Modules.register(name);
    });
}else{
    console.log('Server loaded without modules.');
}

/*
 * Creates unix socket at target location for PHP => node.js communication
 * Faster than standart socket + safe from outer connections
 */
var oldUmask = process.umask(0000);
phpUnixSocket.listen(Config.common.usock, function() {
  process.umask(oldUmask);
});
console.log('Unix socket opened in ' + Config.common.usock);


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
socket = io.listen(server, {log:null});
socket.handleMessage = function(msg){
        msg = msg || {cmd:''};
        switch(msg.cmd){
            case 'SESSION_HAS_PHPSESSID_REGISTERED':
                if(Clients['session' + this.identity].phpid)
                    this.send({cmd:'SESSION_HAS_PHPSESSID_REGISTERED', identity:true});
                else
                    this.send({cmd:'SESSION_HAS_PHPSESSID_REGISTERED', identity:false});
                break;
            case 'SESSION_REQUEST_SID':
                /* Create new client */
                do{
                    this.identity = Math.round(Math.random() * 10000000000000000);
                }while(Clients['session' + this.identity]);
                
                Clients['session' + this.identity] = new Session(this.identity, {
                    parentStorageRemoval:Clients.remove,
                    redisHook:Modules.redisHook.bind(Modules)
                });
                Clients['session' + this.identity].registerClient(this);
                
                this.send({cmd:'SESSION_REGISTER_SID', identity:this.identity});
                break;
            case 'SESSION_SID':
                if(Clients['session' + msg.identity] != undefined){
                    this.identity = msg.identity;
                    this.send({cmd:'SESSION_CONFIRMED_SID'});
                    Clients['session' + msg.identity].registerClient(this);
                }else{
                    this.send({cmd:'SESSION_RESET_SID'});
                }
                break;
            case 'PRINT_CLIENTS':
                console.log(Clients);
                break;
            default:
                if(this.identity){
                    Modules.sessionHook(msg, this);
                }else{
                    console.log('Unknown command: ', msg);
                    this.send({cmd:'INVALID_SID'});
                }
                break;
        }
    };

socket.on('connection', function (client) {
    /*
     *  Implement basic remote-client <=> node.js <=> redis protocol
     */
    
    client.on('message', function(msg){
        if(typeOf(msg) == 'array')
            msg.each(socket.handleMessage.bind(this));
        else
            socket.handleMessage.bind(this)(msg);
    });

    client.on('disconnect', function(){
        if(Clients['session' + this.identity])
            Clients['session' + this.identity].removeClient(this);
    });

    /* When all events are set up, client is requested to identify himself, otherwise server will register him as new client. */
    client.send({cmd:'SESSION_REQUEST_IDENTITY'});
});
