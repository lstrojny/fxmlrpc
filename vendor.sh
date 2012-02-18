#!/bin/sh

repo_install() {
    if [ -d $3 ]; then
        echo "Updating $3"
        cd $3
        [ $1 == svn ] && svn update -q || git pull -q
        cd ..
    else
        echo "Installing $3"
        [ $1 == svn ] && svn checkout -q $2 || git clone -q $2
    fi
}

cd vendor
repo_install git git://github.com/zendframework/zf2.git zf2
repo_install svn http://framework.zend.com/svn/framework/standard/trunk/library/ zf1
repo_install git git://github.com/kriswallsmith/Buzz.git buzz
repo_install git git://github.com/guzzle/guzzle.git guzzle
repo_install git git://github.com/baalexander/node-xmlrpc.git node-xmlrpc
repo_install git git://github.com/oozcitak/xmlbuilder-js.git xmlbuilder-js
cd ..
