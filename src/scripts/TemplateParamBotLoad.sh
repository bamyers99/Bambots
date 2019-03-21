#!/bin/bash
if [ $(pgrep -f "TemplateParamBot.php processloads") != "0" ]; then
    nohup cd /var/www/projects/bambots/Bambots/src/cli ; php TemplateParamBot.php processloads > /dev/null 2>&1 &
fi
