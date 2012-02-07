/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*console.mlog = function(x){
        console.log('[' + process.argv[2] + '.js]', x);
};
console.mlog('Initializing...');
require('./node_modules/mootools.js').apply(GLOBAL);
var http = require('http'),
    io = require('socket.io@0.8.5'),
    net = require('net'),
    fs = require('fs'),
    utility = require('./modules/utility.js');
    utility.apply(GLOBAL),
    
    namespace = process.argv[3];

//var mod = require('./modules' + process.argv[1] + '.js');
app = io.listen(80);
app.of('/' + namespace);
console.m1log('Module "' + namespace + '" running.');*/
var redis = require('redis');
var client = redis.createClient();
client.on("error", function (err) {
    console.log("Error " + err);
});
client.set("string key", "string val", redis.print);
client.quit();