var xmlrpc = require('xmlrpc'),
    xmlRpcServer = xmlrpc.createServer({host: 'localhost', port: 9090}),
    http = require('http'),
    httpServer = http.createServer();


xmlRpcServer.on('system.echo', function(err, params, callback) {
    callback(null, params[0]);
});

xmlRpcServer.on('system.echoNull', function(err, params, callback) {
    callback(null, null);
});
xmlRpcServer.on('system.fault', function(err, params, callback) {
    callback({faultCode: 123, faultString: 'ERROR'});
});


httpServer.on('request', function(request, response) {
    response.writeHead(500, {});
    response.end();
});
httpServer.listen(9091, 'localhost');