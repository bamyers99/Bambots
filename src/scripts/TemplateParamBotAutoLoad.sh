#!/bin/bash
if ! pgrep -f "TemplateParamBot.php autoextract"; then
    nohup sh -c "cd /var/www/projects/bambots/Bambots/src/cli ; php TemplateParamBot.php autoextract" > /dev/null 2>&1 &
fi
