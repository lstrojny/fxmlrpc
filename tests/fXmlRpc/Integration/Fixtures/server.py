from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

server = SimpleXMLRPCServer(("localhost", 8000))
server.register_introspection_functions()

def echo(v):
    return x

def echoNull(v):
    return Null

server.register_function(echo, 'system.echo')
server.register_function(echoNull, 'system.echoNull')

server.serve_forever()
