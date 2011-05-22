Aragorn.cz v4
=============

#### Principles ####

* KISS (Keep it simple, stupid)
* YAGNI (You ain't gonna need it)
* UNIX Philosophy ([http://en.wikipedia.org/wiki/Unix_philosophy](http://en.wikipedia.org/wiki/Unix_philosophy "External link"))
#### Development version ####

**System requirements**

* **PHP/5.3**
* **Redis**
* **Memcached**
* **Node.js**
* **Webserver** Nginx / apache
* **Database** Preferably MySQL

#### Installation ####

* Deploy required technologies
* Make sure all files are readable by server and .../temp and .../logs are writeable
* Create database
* Setup your webserver for main site, static mirror, userdata mirror and node.js proxy (required for opera ajax polling)
* Run node.js as root since it needs access to port 843 (Flash policy file distribution)
* Try your site

**Authors:**

* Core:   
  * Jakub Buriánek
  * Jakub Korál

* Modules:
  * Jakub Buriánek
