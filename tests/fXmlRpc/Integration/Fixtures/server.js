var xmlrpc = require('xmlrpc'),
    xmlRpcServer = xmlrpc.createServer({host: '127.0.0.1', port: 9090}),
    http = require('http'),
    httpServer = http.createServer();

var currentRequest;
xmlRpcServer.httpServer.on('request', function(req) {
    currentRequest = req;
});

xmlRpcServer.on('system.header', function(err, params, callback) {
    var value = currentRequest.headers[params[0]];
    callback(null,  typeof value === "undefined" ? null : value);
});

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
httpServer.listen(9091, '127.0.0.1');
