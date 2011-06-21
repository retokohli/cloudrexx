#!/bin/bash
./doctrine orm:schema-tool:drop --force; ./doctrine orm:schema-tool:create; php test.php
