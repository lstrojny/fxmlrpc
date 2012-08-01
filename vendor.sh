#!/bin/bash

repo_install() {
    if [ -d "$3" ]; then
        echo "Updating $3"
        pushd $3
        [ "$1" = svn ] && svn update -q || git pull -q
        popd
    else
        echo "Installing $3"
        mkdir -p $3
        [ "$1" = svn ] && svn checkout -q $2 $3 || git clone -q $2 $3
    fi
}

pushd vendor
repo_install git git://github.com/zendframework/zf2.git zf2
repo_install svn http://framework.zend.com/svn/framework/standard/trunk/library/ zf1
repo_install git git://github.com/kriswallsmith/Buzz.git buzz
repo_install git git://github.com/guzzle/guzzle.git guzzle
repo_install git git://github.com/symfony/EventDispatcher.git Symfony/Component/EventDispatcher
repo_install git git://github.com/symfony/Validator.git Symfony/Component/Validator
repo_install git git://github.com/Seldaek/monolog.git monolog
popd
