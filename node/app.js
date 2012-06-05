#!/usr/bin/env node

var START_TIME = new Date().getTime();
console.log('Booting cluster... ' + new Date());

require('mootools');
require('./modules/utility.js').apply(GLOBAL);
var cluster = require('cluster'),
    os = require('os'),
    Tracer = require('tracer'),
    fs = require('fs'),
    Config = require('./modules/config.js').parse(),
    CPU_NUM = os.cpus().length, 
    WORKER_NUM = Config.node.jobs || (CPU_NUM + 2),
    log = Config.node.log.file == '' ?  Tracer.colorConsole({
        level: Config.node.log.level,
        format: "{{timestamp}} <{{title}}> {{file}}:{{line}} {{message}} ",
        dateformat : "dd.mm.yyyy HH:MM:ss.L"
    }) : Tracer.console({
        transport : function(data) {
            fs.open(Config.node.log.file, 'a', 0666, function(e, id) {
                fs.write(id, data.output+"\n", null, 'utf8', function() {
                    fs.close(id, function() {
                    });
                });
            });
        }
    });

    
log.info('\n================\n' +
            'Host:   ' + os.hostname() + '\n' +
            'OS:     ' + os.type() + ' (' + os.arch() + ')/' + os.platform() + ' ' + os.release() + '\n' + 
            'CPU:    ' + CPU_NUM + 'x ' + os.cpus()[0].model + '\n' +
            'Memory: ' + (os.totalmem() - os.freemem()).bytes2string() + '/' + os.totalmem().bytes2string() + '\n' +
            '================');

cluster.setupMaster({
    exec:'server.js',
    silent:false
});
try{
    fs.unlinkSync(Config.node.phpbridge.socket);
}catch(e){
    log.warn('Unlink failed on ' + Config.node.phpbridge.socket, e)
}

log.info (' * Cluster ready, spawning ' + WORKER_NUM + ' workers.');
var ONLINE_WORKERS = 0;
var onDeath  = function(w){
    log.error('Worker ' + w.uniqueID + ' died (suicide = ' + (w.suicide) + ').');
    if(!w.suicide){
        var w1 = cluster.fork();
        w1.on('exit', onDeath);
        log.info('Worker ' + w1.uniqueID + ' spawned.');
    }
};
for(var i = WORKER_NUM; i; i--){
    var worker = cluster.fork();
    worker.on('exit', onDeath);
    worker.on('online', function(){
        ONLINE_WORKERS++;
        if(ONLINE_WORKERS == WORKER_NUM){
            log.info(' * All workers are ready.\n================');
        }
    });
}

log.info(' * Spawning complete, cluster is on standby.');
