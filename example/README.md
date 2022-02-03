# How to run example

1. Install the package
2. Copy the `websocket` folder inside `application` folder
3. Visit `yoursite/index.php/dashboard/websocket`
4. You should see a new server called `MyWebSocketServer`, start it by clicking `Start`
5. Now visit `visit/packages/concrete_websocket/example/client.html` to access the demo client application.


The provided example server works as an echo server.  
It will reply the same message you send to it.  
When you open the connection the server will send a _"Welcome"_ message.  
The server allow connection only from concrete5 authenticated users (thanks to the `ConcreteCheck` middleware) and from the same domain of the server (thanks to the `OriginCheck` middleware). Any other connection will be reject even before switching the protocol.