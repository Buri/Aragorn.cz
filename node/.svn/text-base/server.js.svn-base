require.paths.push('/var/node/lib/');
require('./lib/mootools.js');

/* Main WS server@8000 */
var http = require('http'),
    io = require('socket.io'),
    net = require('net'),
    fs = require('fs'),
    redis = require('node-redis'),
    domains = ["*:*"],
    Clients = {},
    socket = {},
    Session = {},
    server = http.createServer(function(req, res){
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.end('<h1>You shouldnt be here.</h1>');
    }),
    phpUnixSocket = net.createServer(function(s) {
        console.log('Connection established on unix socket');
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
                            console.log('User ' + json.data.username + ' loged in.');
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
                            console.log('User ' + uname + ' loged out.');
                            this.write('OK');
                        }else{
                            this.write("SESSION_NOT_FOUND");
                        }
                        break;
                    default:
                        this.write("BAD_CMD");
                }
            }else{
                this.write("BAD_FORMAT");
            }
            //this.write(data);
        }.bind(s));
    });

server.listen(8000);
console.log('Server listening at port 8000');

var oldUmask = process.umask(0000);
phpUnixSocket.listen('/tmp/nodejs.socket', function() {
  process.umask(oldUmask);
});
console.log('Unix socket opened in /tmp/nodejs.socket');

/* Backup Flash server@843 */
/*try{
    net.createServer(function(socket){
        socket.write("<?xml version=\"1.0\"?>\n");
        socket.write("<!DOCTYPE cross-domain-policy SYSTEM \"http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd\">\n");
        socket.write("<cross-domain-policy>\n");
        domains.forEach(
            function(domain)
            {
                var parts = domain.split(':');
                socket.write("<allow-access-from domain=\""+parts[0]+"\"to-ports=\""+(parts[1]||'80')+"\"/>\n");
            }
        );
        socket.write("</cross-domain-policy>\n");
        require("sys").log("Wrote policy file.");
        socket.end();
    }
    ).listen(843);
console.log('Server listening at port 843');
}
catch(e){
    console.log('Flash policy file is already being distributed.');
}*/

Session = new Class({
    Implements:[Options, Events],
    options:{},
    initialize:function(sid, options){
        this.setOptions(options);
        this.sessionId = sid;
        this.redis = redis.createClient();
    },
    clients:[],
    sessionId:0,
    user:{
        id:0,
        name:''
    },
    phpid:'',
    redis:{},
    registerClient:function(client){
        console.log('Registering client ' + client.sessionId);
        if(this.eraseTimeout){
            clearTimeout(this.eraseTimeout);
            this.eraseTimeout = {};
//            console.log('Session reinitiated: ' + this.sessionId);
        }
        this.clients.push(client.sessionId);

        client.redis = redis.createClient();
        client.redis.on('subscribe', function(channel){console.log('Subscribe ' + channel)});
        client.redis.on('unsubscribe', function(channel){console.log('Unsubscribe ' + channel)});
        client.redis.on('message', this.handleRedis.bind(this));
        client.redis.subscribe('/system');
        client.sendToChannel = this.sendToChannel.bind(this);
    },
    removeClient:function(client){
//        console.log('Removing client ' + client.sessionId);
        client.redis.quit();
        delete client.redis;
        this.clients.erase(client.sessionId);
        if(this.clients.length == 0){
//            console.log('Session ready for clearout: ' + this.sessionId);

            /* UPDATE, SET LARGER TIMEOUT, FOR DEBUG PURPOSES ONLY */
            this.eraseTimeout = setTimeout(this.erase.bind(this), 1000 * 5);
        }
    },
    exit:function(){
        this.redis.quit();
        delete this.redis;
    },
    eraseTimeout:{},
    erase:function(){
        this.exit();
//        console.log('Session terminated: ' + this.sessionId);
        this.fireEvent('disconnect', this);
        Clients['session' + this.sessionId].fireEvent('user_disconnect', this);
        delete Clients['session' + this.sessionId];
    },
    handleRedis:function(channel, message){
        message = JSON.decode(message);
        console.log('Recieved message on channel ' + channel);
        console.log(message);
        try{
            this.broadcastToClients(message);
        }
        catch(e){
            console.log(e);
        }
    },
    handleMessage:function(message, client){
        console.log('Handling message');
        console.log(message);
        switch(message.cmd){
            case 'chat':
                this.sendToChannel(message.channel, message);
                break;
            default:
                this.sendToClient(client, message);
        }
    }, //require('./modules/session-handleMessage.js').h,
    sendToChannel:function(channel, message){
        message.from = this.sessionId;
        message.time = new Date().getTime();
        message.from = this.user.name;
        this.redis.publish(channel, JSON.encode(message));
    },
    sendToClient:function(client, message){
        if(typeof(client) == 'string'){
            if(this.clients.indexOf('client') == -1) throw new Exception('Client not found.');
            client = socket.clients[client];
        }
        client.send(message);
    },
    broadcastToClients:function(message, clients){
        if(!clients || !clients.length) clients = this.clients;
        clients.each(function(client){
            socket.clients[client].send(message);
        });
    }
});

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
                
                Clients['session' + this.identity] = new Session(this.identity);
                Clients['session' + this.identity].registerClient(this);
                
                //console.log('Registered identity: ' + this.identity);
                this.send({cmd:'SESSION_REGISTER_SID', identity:this.identity});
                break;
            case 'SESSION_SID':
                //console.log('Recieved identity: ' + msg.identity + ', checking');
                if(Clients['session' + msg.identity] != undefined){
                    //console.log('Identity verified');
                    this.identity = msg.identity;
                    this.send({cmd:'SESSION_CONFIRMED_SID'});
                    Clients['session' + msg.identity].registerClient(this);
                }else{
                    //console.log('Invalid identity, requesting clearout');
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

    /* When all events are set up, client is requested to identifi himself, otherwise server will register him as new client. */
    client.send({cmd:'SESSION_REQUEST_IDENTITY'});
});
