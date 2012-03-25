#!/usr/bin/env node
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var xmpp = require('node-xmpp');
var c2s = new xmpp.C2SServer({
        port: 5222,
        domain: ''
    });
c2s.on("connect", function(client) {
    // That's the way you add mods to a given server.
    
    // Allows the developer to authenticate users against anything they want.
    client.on("authenticate", function(opts, cb) {
        //console.log('Authenticate: ', opts);
        //opts.client.authenticated = true;
        cb(null); // cb(false);
    });
    client.on("online", function() {
        client.send(new xmpp.Message({ type: 'chat' }).c('body').t("Hello there, little client."));
    });

    // Stanza handling
    client.on("stanza", function(stanza) {
//        console.log(stanza);
    });

    // On Disconnect event. When a client disconnects
    client.on("disconnect", function(client) {
        console.log('Disconnect: ', client);
    });

});
// Allows the developer to register the jid against anything they want
c2s.on("register", function(opts, cb) {
    console.log('Register: ', opts);
    cb(true);
});
