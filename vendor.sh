#!/bin/sh

rm -rf vendor/*

cd vendor
echo "Installing zf2"
git clone -q git://github.com/zendframework/zf2.git

echo "Installing zf1"
svn checkout -q http://framework.zend.com/svn/framework/standard/trunk/library/ zf1

echo "Installing Buzz"
git clone -q git://github.com/kriswallsmith/Buzz.git buzz

echo "Installing Guzzle"
git clone -q git://github.com/guzzle/guzzle.git

cd -
