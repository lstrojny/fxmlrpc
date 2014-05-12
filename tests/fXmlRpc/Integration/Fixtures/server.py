from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler
from xmlrpclib import Fault
from SimpleHTTPServer import SimpleHTTPRequestHandler
from SocketServer import TCPServer
from threading import Thread
import signal
import sys

class Server(SimpleXMLRPCServer):
    def __init__(self, *args, **kargs):
        class RequestHandler(SimpleXMLRPCRequestHandler):
                def _dispatch(self, method, params):
                    """
                    Overridden to pass request handler to the handling function so that the function can play around
                    with HTTP headers and stuff
                    """
                    params = (self, ) + params
                    func = None
                    try:
                        func = self.server.funcs[method]
                    except KeyError:
                        if self.server.instance is not None:
                            if hasattr(self.server.instance, '_dispatch'):
                                return self.server.instance._dispatch(method, params)
                            else:
                                try:
                                    func = _resolve_dotted_attribute(self.server.instance, method)
                                except AttributeError:
                                    pass

                    if func is not None:
                        return apply(func, params)
                    else:
                        raise Exception('method "%s" is not supported' % method)

        SimpleXMLRPCServer.__init__(self, requestHandler=RequestHandler, *args, **kargs)

server = Server(("127.0.0.1", 28000), allow_none = True)
server.register_introspection_functions()

def echo(handler, v):
    return v

def echoNull(handler, v):
    return None

def echoHeader(handler, header):
    return handler.headers.get(header, None)

def fault(handler):
    raise Fault(123, "ERROR")

server.register_function(echo, 'system.echo')
server.register_function(echoNull, 'system.echoNull')
server.register_function(fault, 'system.fault')
server.register_function(echoHeader, 'system.header')


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
