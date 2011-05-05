require.paths.push('/var/node/modules/');
require('./node_modules/mootools.js').apply(GLOBAL);

/* Main WS server@8000 */
var http = require('http'),
    io = require('socket.io'),
    net = require('net'),
    fs = require('fs'),
    domains = ["*:*"],
    Clients = {},
    Chat = require('./modules/chat.js').Chat,
    ChatServer = new Chat(),
    socket = {},
    Session = require('./modules/session.js').Session,
    server = http.createServer(function(req, res){
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.end('<h1>You shouldnt be here.</h1>');
    }),
    phpUnixSocket = net.createServer(function(s) {
        s.setEncoding('utf-8');
        s.on('data', function(data){
            console.log('@UNIX: ' + data);
            var json = JSON.parse(data);
            if(json && json.command){
                switch(json.command){
                    case "user-login":
                        if(Clients['session' + json.data.nodeSession]){
                            Clients['session' + json.data.nodeSession].phpid = json.data.PHPSESSID;
                            Clients['session' + json.data.nodeSession].user.id = json.data.id;
                            Clients['session' + json.data.nodeSession].user.name = json.data.username;
//                            console.log('User ' + json.data.username + ' loged in.');
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
                        break;
                    case "user-logout":
                        if(Clients['session' + json.data.nodeSession]){
                            var uname = Clients['session' + json.data.nodeSession].user.name;
                            Clients['session' + json.data.nodeSession].phpid = '';
                            Clients['session' + json.data.nodeSession].user.id = 0;
                            Clients['session' + json.data.nodeSession].user.name = '';
//                            console.log('User ' + uname + ' loged out.');
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
                        break;
                    case "chat":
                        switch(json.data.action){
                            case "enter":
                                console.log('User entered chat');
                                this.write('OK');
                                break;
                            case "leave":
                                console.log('User left chat');
                                this.write('OK');
                                break;
                            default:
                                this.write('BAD_PARAM');
                        }
                        break;
                    default:
                        this.write("BAD_CMD");
                }
            }else{
                this.write("BAD_FORMAT");
            }
        }.bind(s));
    });

server.listen(8000);
console.log('Server listening at port 8000');

var oldUmask = process.umask(0000);
phpUnixSocket.listen('/tmp/nodejs.socket', function() {
  process.umask(oldUmask);
});
console.log('Unix socket opened in /tmp/nodejs.socket');

socket = io.listen(server);

socket.on('connection', function (client) {
    /*
     *  Implement basic remote-client <=> node.js <=> redis protocol
     */

    client.on('message', function(msg, client){
        msg = msg || {cmd:''};
        switch(msg.cmd){
            case 'TEST_IDENTITY':
                if(Clients['session' + this.identity].phpid)
                    this.send({cmd:'TEST_IDENTITY', identity:true});
                else
                    this.send({cmd:'TEST_IDENTITY', identity:false});
                break;
            case 'SESSION_REQUEST_SID':
                /* Create new client */
                do{
                    this.identity = Math.round(Math.random() * 10000000000000000);
                }while(Clients['session' + this.identity]);
                
                Clients['session' + this.identity] = new Session(this.identity, {
                                                                    chatCommandHook:ChatServer.sessionHook,
                                                                    chatRedisHook:ChatServer.sessionHookRedis
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
                    Clients['session' + this.identity].handleMessage(msg, this);
                }else{
                    console.log('Unknown command: ', msg);
                    this.send({cmd:'INVALID_SID'});
                }
                break;
        }
    });

    client.on('disconnect', function(){
        if(Clients['session' + this.identity])
            Clients['session' + this.identity].removeClient(this);
    });

    /* When all events are set up, client is requested to identify himself, otherwise server will register him as new client. */
    client.send({cmd:'SESSION_REQUEST_IDENTITY'});
});
