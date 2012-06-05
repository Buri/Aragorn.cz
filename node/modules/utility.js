exports.apply = function(o){
    o.Array.prototype.binarySearch = function(v, cb){
        if(!v && v !== 0) return -1;
        if(!cb || cb == null)
            cb = function(a,b){return (a > b ? 1 : (a == b ? 0 : -1))};
        var l = 0, r = this.length - 1, p = l/2;
        while(l <= r){
            p = parseInt((l + r)/2);
            switch(cb(this[p], v)){
                case 1:
                    r = p - 1;
                    break;
                case 0:
                    return p;
                case -1:
                    l = p + 1;
                    break;
            }
        }
        return -1;
    };

    o.Number.prototype.bytes2string = function(){
        var b = this, u = ['B', 'KB', 'MB', 'GB', 'TB', 'PT', 'EB'], i = 0;
        while(b >= 1024){
            b /= 1024;
            i++;
        }
        var c = '' + b;
        return c.substr(0, c.indexOf('.') + 3) + ' ' + u[i];
    }

    o.dump = new exports.Logger;
}

exports.serverUptime = function(starttime){
    var diff = parseInt((new Date().getTime() - starttime)/1000), tstr = '';
    if(diff > 31536000){
        var y = parseInt(diff / 31536000);
        tstr += y + ' year' + (y > 1 ? 's' : '') + ' ';
        diff -= y * 31536000;
    }
    if(diff > 2678400){
        var m = parseInt(diff / 2678400);
        tstr += m + ' month' + (m > 1 ? 's' : '') + ' ';
        diff -= m * 26784000;
    }
    if(diff > 604800){
        var w = parseInt(diff / 604800);
        tstr += w + ' week' + (w > 1 ? 's' : '') + ' ';
        diff -= w * 604800;
    }
    if(diff > 86400){
        var d = parseInt(diff / 86400);
        tstr += d + ' day' + (d > 1 ? 's' : '') + ' ';
        diff -= d * 86400;
    }
    if(diff > 3600){
        var h = parseInt(diff / 3600);
        tstr += h + ' hour' + (h > 1 ? 's' : '') + ' ';
        diff -= h * 3600;
    }
    if(diff > 60){
        var mi = parseInt(diff / 60);
        tstr += mi + ' minute' + (mi > 1 ? 's' : '') + ' ';
        diff -= mi * 60;
    }
    tstr += diff + ' second' + (diff > 1 ? 's' : '');
    return tstr;
}

exports.Logger = new Class({
        Implements: [Options, Events],
        options:{
            level: 5,   // 0 - none, 1 - errors, 2 - warnings, 3 - notices, 4 - debug, 5 - trace
            color: true
        },
        init:function(options){
            this.setOptions(options);
        },
        write:function(level){
            this.fireEvent('write', arguments);
            if(level <= this.options.level){
                console.log(arguments);
            }
        }
    });