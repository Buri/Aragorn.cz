var START_TIME = new Date().getTime();
console.log('Booting cluster... ' + new Date());

require('mootools').apply(GLOBAL);
require('./modules/utility.js').apply(GLOBAL);
var cluster = require('cluster'),
    os = require('os'),
    CPU_NUM = os.cpus().length, 
    WORKER_NUM = CPU_NUM / CPU_NUM;
console.log('================\n' + 
            'Host:   ' + os.hostname() + '\n' +
            'OS:     ' + os.type() + ' (' + os.arch() + ')/' + os.platform() + ' ' + os.release() + '\n' + 
            'CPU:    ' + CPU_NUM + 'x ' + os.cpus()[0].model + '\n' +
            'Memory: ' + (os.totalmem() - os.freemem()).bytes2string() + '/' + os.totalmem().bytes2string() + '\n' +
            '================');

cluster.setupMaster({
    exec:'server.js',
    silent:false
});

console.log('Cluster ready, spawning ' + WORKER_NUM + ' workers.');
var ONLINE_WORKERS = 0;
for(var i = WORKER_NUM; i; i--){
    var worker = cluster.fork();
    worker.on('death', function(w){
        console.log('Worker ' + w.uniqueID + ' died (suicide = ' + (w.suicide) + ').');
        if(!w.suicide){
            var w1 = cluster.fork();
            console.log('Worker ' + w1.uniqueID + ' spawned.');
        }
    });
    worker.on('online', function(){
        ONLINE_WORKERS++;
        if(ONLINE_WORKERS == WORKER_NUM){
            console.log('All workers are ready.\n================');
        }
    });
}

process.on('SIGINT', function(){
    console.log('Graceful shutdown!');
    for(var worker in cluster.workers){
        var w = cluster.workers[worker];
        w.destroy();
    }
    
    // Give workers time to die. (1s)
    setTimeout(function(){
        console.log('Server going down NOW!');
        process.exit();
    }, 1000);
});

console.log('Spawning complete, cluster is on standby.');