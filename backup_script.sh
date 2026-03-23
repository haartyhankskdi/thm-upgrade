#! /bin/bash 
workDir = /home/1479223.cloudwaysapps.com/dtsmwubyqx/public_html

echo  "backup file" > backup.txt
cd /home/1479223.cloudwaysapps.com/dtsmwubyqx/public_html/Backup
tar -cvzf "Backup_$(date +%d-%m-%Y).tar"  /home/1479223.cloudwaysapps.com/dtsmwubyqx/public_html/app/code  /home/1479223.cloudwaysapps.com/dtsmwubyqx/public_html/app/design
