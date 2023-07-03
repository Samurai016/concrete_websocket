# Concrete Websocket
![Last release](https://img.shields.io/github/v/release/Samurai016/concrete_websocket?style=flat-square)  
A plugin to add support for WebSocket to [Concrete CMS](https://www.concretecms.com/) (known also as concrete5)

## Installation
* Download the [latest release](https://github.com/Samurai016/concrete_websocket/releases/latest) package (*concrete_websocket.zip*)
* Unzip the package in your /packages directory
* Visit your website's "Extend Concrete" page 
* Install the package.

## Usage
The package provide an interface to run a custom WebSocket server.  
The package is based on [Ratchet PHP](http://socketo.me/), so you can refer to Ratchet documentation for more details.
> **Warning!** The websocket server are run outside Concrete environment, so, in the server class, you can't use classes or methods from the Concrete environment.

Refer to [example README](https://github.com/Samurai016/concrete_websocket/blob/master/example/README.md) to create a custom WebSocket server.

### Dashboard explaining
The package will automatically detect your server classes.  
* **Start** the server clicking on the *Start* button.  
Now you can connect to your server through *ws://yourdomain:port/*
* **Stop** the server by clicking the *Stop* button.  
* **Restart** the server by clicking the *Restart* button.  

The **PID** column of the table is meant to be used for debugging and/or to track the process on your server.

## WebSocketServer class

## Middlewares
When a new connection arrive to the server, it will run the middlwares defined by the `getMiddlewares()` function, they are run from top to bottom (LIFO queue).
Every middleware is defined by a [`Middleware`](https://github.com/Samurai016/concrete_websocket/blob/master/websocket/src/middleware/Middleware.php) object, which is composed by:
* `$class`: a class that must implements `Ratchet\Http\HttpServerInterface` interface.
* `$params`: an array of params that will be passed to the `$class` constructor when the middlware is build (when the server is started from the dashboard).

### `ConcreteAuthentication` Middleware
The `ConcreteAuthentication` middleware is a built-in middleware in the package.  
It prevent not-logged users to access the server.
It works by checking the log status querying a special endpoint defined by the package and by closing the http connection even before the protocol switching.

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

## FAQ

## I got the `exec` disabled error, how can I enable it?
Enabling `exec` is crucial for concrete_websocket and enabling it is different between webservers.
In general, you have to edit the `disable_functions` in your `php.ini` configuration file, but refers to your webserver documentation for more informations.

If you use one of the following admin panels, I give you some useful links to follow to edit the `php.ini`:
* [CPanel](https://docs.cpanel.net/knowledge-base/security/how-to-edit-your-php-ini-file/)
* [Plesk](https://support.plesk.com/hc/en-us/articles/213936565-How-to-find-and-edit-PHP-configuration-files-in-Plesk-for-a-domain-or-for-global-PHP-handler)
* ISPConfig
You can edit php.ini for every site by editing the field `Custom php.ini settings` in the _Options_ tab of the site page.

If you've made the changes but don't see them applied to your site, you may need to restart your webserver.

## I am unable to connect to websocket due to insecure connection  
When you try to connect to a `ws://` unsecure connection from a `https://` secure connection, you may run into the following error message in the console:
```
Mixed Content: The page at '...' was loaded over HTTPS, but attempted to connect to the insecure WebSocket endpoint 'ws://...'. This request has been blocked; this endpoint must be available over WSS.
```
This is because your browser prevent running unsecure connection from a secure environment.
The easiest way to solve this is to configure a proxy server.

For Apache server (source: [StackOverflow](https://stackoverflow.com/questions/16979793/php-ratchet-websocket-ssl-connect#answer-28393526)):
* Enable `mod_proxy.so` and `mod_proxy_wstunnel.so`
* Add this lines to your `httpd.conf` file:
  ```
  ProxyPass /wss/ ws://yourdomain.com:port/
  ProxyPassReverse /wss/ ws://yourdomain.com:port/
  ```
* Restart your server with
  ```bash
  sudo systemctl restart apache2
  ```
* Now instead of connecting to `wss://yourdomain.com:port/`, connecto to `wss://yourdomain.com/wss` (no port and add the `/wss` path)

For Nginx user maybe [this solution](https://stackoverflow.com/questions/16979793/php-ratchet-websocket-ssl-connect#answer-43012985) could work but I not tested it personally so I don't guarantee.