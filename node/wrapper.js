/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var server = require('./server.js');

while(true){
    try{
        server.run();
    }
    catch(e){
        console.log(e);
    }
}