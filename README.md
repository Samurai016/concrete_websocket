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
2. Copy and paste the [example code](https://github.com/Samurai016/concrete_websocket/blob/master/example/ExampleSocketServer.php).
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
