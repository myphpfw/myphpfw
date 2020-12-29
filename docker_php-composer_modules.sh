#!/usr/bin/env bash

docker ps &>/dev/null
if [ "$?" -ne 0 ]; then
  # It appears that this user isn't enabled to talk to the Docker daemon
  echo -e "You don't have access to the Docker daemon\nTry executing as \`root\` please."
  exit 1
else
  # List all files' permissions, one username only (uniq), extract uid and gid from respective files
  user=$(cat /etc/passwd | grep -e "^$(ls -l $(pwd) | grep -v "total" | awk '{print $3}' | uniq | head -n1)" | cut -d: -f3)
  group=$(cat /etc/group | grep -e "^$(ls -l $(pwd) | grep -v "total" | awk '{print $4}' | uniq | head -n1)" | cut -d: -f3)
  # Retrieve composer modules using docker, the container gets deleted as soon as finished
  docker run --rm -it --user "$user":"$group" -v `pwd`:/app composer:2.0.8 install
fi
