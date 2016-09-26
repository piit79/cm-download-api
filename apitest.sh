#!/bin/bash

curl -vX POST -H "Content-Type: application/json; charset=utf-8" -d '{"method":"get_all_builds","params":{"device":"shamu","channels":["nightly"],"source_incrementa":"39e618492a"}}' http://errorek/api
