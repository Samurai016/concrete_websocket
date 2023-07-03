# ⚡️ How to run example

Follow these steps to run the example server and access the demo client application:
1. 📦 [Install the package](https://github.com/Samurai016/concrete_websocket/#-installation).
2. 📂 Copy the `websocket` folder inside `application` folder.
3. ✏️ At line 14 of the `ExampleSocketServer.php` file, replace `'localhost'` with your domain name to enable server connectivity.
4. 🌐 Visit `yourdomain.com/index.php/dashboard/websocket` (or navigate to "Websocket Dashboard" from the Concrete nav panel)
5. 👀 You should see a new server called `MyWebSocketServer`, start it by clicking `Start`.
6. 🚀 Access the demo client application by visiting `yourdomain.com/packages/concrete_websocket/example/client.html`.

The provided example server works as an echo server.  
It will reply the same message you send to it.  
Upon opening a connection, the server will greet you with a friendly _"Welcome"_ message.  
Please note that the server only allows connections from Concrete authenticated users (thanks to the `ConcreteAuthentication` middleware) and from the same domain of the server (thanks to the `OriginCheck` middleware).  
Any other connection will be reject even before switching the protocol.