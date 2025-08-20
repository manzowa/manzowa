#!/bin/bash

if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé."
    exit 1
fi

php "$(dirname "$0")/resize.php"