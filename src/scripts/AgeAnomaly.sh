#!/bin/sh

cd /data/project/bambots/Bambots/src/cli; jsub -N DatabaseReportBot -cwd -mem 512m php DatabaseReportBot.php MiscReports AgeAnomaly
