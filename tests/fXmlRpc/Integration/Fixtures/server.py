from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler
from xmlrpclib import Fault
from SimpleHTTPServer import SimpleHTTPRequestHandler
from SocketServer import TCPServer
from threading import Thread

server = SimpleXMLRPCServer(("localhost", 8000), allow_none = True)
server.register_introspection_functions()

def echo(v):
    return v

def echoNull(v):
    return None

def fault():
    raise Fault(123, "ERROR")

server.register_function(echo, 'system.echo')
server.register_function(echoNull, 'system.echoNull')
server.register_function(fault, 'system.fault')


class ErrorHTTPHandler(SimpleHTTPRequestHandler):
    def do_POST(self):
        self.send_response(500, 'Service unavailable')

error_server = TCPServer(("", 8001), ErrorHTTPHandler)

threads = []
threads.append(Thread(target=server.serve_forever))
threads.append(Thread(target=error_server.serve_forever))

for thread in threads:
    thread.start()

for thread in threads:
    thread.join()
