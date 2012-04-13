#!/bin/bash
cd $(dirname $0)
./doctrine orm:generate-entities  entities