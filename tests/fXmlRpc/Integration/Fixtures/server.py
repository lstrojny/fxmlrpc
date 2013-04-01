from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler
from xmlrpclib import Fault
from SimpleHTTPServer import SimpleHTTPRequestHandler
from SocketServer import TCPServer
from threading import Thread
import signal
import sys
from collections import namedtuple

server = SimpleXMLRPCServer(("localhost", 28000), allow_none = True)
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

error_server = TCPServer(("", 28001), ErrorHTTPHandler)

def signal_handler(s, f):
    error_server.shutdown()
    server.shutdown()
    sys.exit(0)

signal.signal(signal.SIGINT, signal_handler)
signal.signal(signal.SIGTERM, signal_handler)

threads = []

thread = Thread(target=lambda: server.serve_forever(poll_interval=0.5))
thread.daemon = True
threads.append(thread)

thread = Thread(target=lambda: error_server.serve_forever(poll_interval=0.5))
thread.daemon = True
threads.append(thread)

for thread in threads:
    thread.start()

while True:
    for thread in threads:
        thread.join(0.5)
