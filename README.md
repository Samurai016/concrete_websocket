# Concrete Websocket
![Last release](https://img.shields.io/github/v/release/Samurai016/concrete_websocket?style=flat-square)  
A plugin to add support for WebSocket to [Concrete CMS](https://www.concretecms.com/) (known also as concrete5)

## Installation
The package can be downloaded, unzipped into the /packages directory (ensuring the folder name is simply 'concrete_websocket') and installed via the 'Extend Concrete' option within the dashboard.   
It is recommended that a 'release' be used instead of the master branch - [https://github.com/Samurai016/concrete_websocket/releases](https://github.com/Samurai016/concrete_websocket/releases))

## Usage
The package provide an interface to run a custom WebSocket server.  
The package is based on [Ratchet PHP](http://socketo.me/), so you can refer to Ratchet documentation for more details.
> **Warning!** The websocket server are run outside Concrete environment, so, in the server class, you can't use classes or methods from the Concrete environment.

To create a new server:  
1. Create a file under application/websocket folder, for example `ExampleSocketServer.php`.  
This file will handle the Websocket requests.
2. Copy and paste the [example code](https://github.com/Samurai016/concrete_websocket/blob/master/example/websocket/ExampleSocketServer.php).
3. Go to your website and navigate to `yourdomain/index.php/dashboard/websocket` or use the dashboard left panel to navigate to the Websocket Dashboard.
4. The package will automatically detect your server class. 
Start the server clicking on the *Start* button.  
Now you can connect to your server through ws://yourdomain:port/
5. Stop the server by clicking the *Stop* button.  

## Example explaining
In the [example code](https://github.com/Samurai016/concrete_websocket/blob/master/example/ExampleSocketServer.php) you can find an implementation of an echo server (a server the reply with the same message you send).

### Middlewares
When a new connection arrive to the server, it will run the middlwares defined by the `getMiddlewares()` function, they are run from top to bottom (first defined, first run).
Every middleware is defined by a [`Middleware`](https://github.com/Samurai016/concrete_websocket/blob/master/websocket/src/middleware/Middleware.php) object, which is composed by:
* `$class`: a class that must implements `Ratchet\Http\HttpServerInterface` interface.
* `$params`: an array of params that will be passed to the `$class` constructor when the middlware is build (when the server is started from the dashboard).

### On Open
After the middlewares, if the http connection is successful, the server switch to WebSocket protocol and the `onOpen` method is run.
The base server `onOpen` method add the connection to the array of connected clients. So, if you want to attach a connection you can do it directly or by calling `parent::onOpen($conn);`.

More info [here](http://socketo.me/api/class-Ratchet.WebSocket.WsServer.html#_onOpen);
### On Message
Every time a message is sent from clients, the `onMessage` function is run.  

More info [here](http://socketo.me/api/class-Ratchet.WebSocket.WsServer.html#_onMessage);
### On Error
Every time an error occurs, the `onError` function is run.  

More info [here](http://socketo.me/api/class-Ratchet.WebSocket.WsServer.html#_onError);
### On Close
Every time a connection is closed, the `onClose` function is run.  
The base server `onClose` method remove the connection from the array of connected clients. So, if you want to detach a connection you can do it directly or by calling `parent::onClose($conn);`.

More info [here](http://socketo.me/api/class-Ratchet.WebSocket.WsServer.html#_onClose);

## `ConcreteCheck` Middleware
The `ConcreteCheck` middleware is a built-in middleware in the package.  
It prevent not-logged users to access the server.
It works by checking the log status querying a special endpoint defined by the package and by closing the http connection even before the protocol switching.

## FAQ

## I got the `exec` disabled error, how can I enable it?
Enabling `exec` is crucial for concrete_websocket and enabling it is different between webservers.
In general, you have to edit your `php.ini` file. Where this file is placed should be showed you by concrete_websocket in the Websocket Dashboard page when it detects that `exec` is disabled.   
If you don't know where `php.ini` is placed, create a php file and place this code inside:
```php
<?php phpinfo(); ?>
```
Then run this code and you should see a table with a lot of infos, included the `php.ini` location.  

Once you know where `php.ini` is placed, edit it.  
You should find a string like this: `disable_functions=...exec,...`  
Remove `exec` from that list of comma-separated names, save and restart your webserver.

If you use one of the following admin panels, I give you some useful links to follow to edit the `php.ini`:
* [CPanel](https://docs.cpanel.net/knowledge-base/security/how-to-edit-your-php-ini-file/)
* [Plesk](https://support.plesk.com/hc/en-us/articles/213936565-How-to-find-and-edit-PHP-configuration-files-in-Plesk-for-a-domain-or-for-global-PHP-handler)
* ISPConfig
You can edit php.ini for every site by editing the field `Custom php.ini settings` in the _Options_ tab of the site page.

If you've made the changes but don't see them applied to your site, you may need to restart your webserver.