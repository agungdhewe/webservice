#!/bin/bash

PS1='\[\033[32m\]\u@\h\[\033[0m\]:\[\033[34m\]\W\[\033[0m\]\$ '


URL="http://localhost:8005/template/assets/style.css"
# URL="http://localhost:8005/testhalaman"

echo GET $URL
curl -D - $URL