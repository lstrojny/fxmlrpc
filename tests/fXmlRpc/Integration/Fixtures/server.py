from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

server = SimpleXMLRPCServer(("localhost", 8000), allow_none = True)
server.register_introspection_functions()

def echo(v):
    return v

def echoNull(v):
    return None

server.register_function(echo, 'system.echo')
server.register_function(echoNull, 'system.echoNull')

server.serve_forever()
