#!/bin/bash
cd $(dirname $0)
./doctrine orm:schema-tool:drop --force
./doctrine orm:schema-tool:create