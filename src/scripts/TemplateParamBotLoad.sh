#!/bin/bash
if ! pgrep -f "TemplateParamBot.php processloads"; then
    nohup sh -c "cd /var/www/projects/bambots/Bambots/src/cli ; php TemplateParamBot.php processloads" > /dev/null 2>&1 &
fi
