#!/bin/sh
set -x
rm -rf var/cache/*
rm -rf var/logs/*
rm -rf var/sessions/*
chmod g+rw web/screenshots/*.png

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
echo $HTTPDUSER
sudo setfacl -R -m u:"$HTTPDUSER":rwX var/cache var/logs app/Resources/exports var/sessions var/spool
sudo setfacl -dR -m u:"$HTTPDUSER":rwX var/cache var/logs app/Resources/exports var/sessions var/spool

# Allow writing by webedit group if it exists or by current user if not
if [ $(getent group webedit) ]
then
    sudo setfacl -R -m g:webedit:rwX .
    sudo setfacl -dR -m g:webedit:rwX .
else
    sudo setfacl -R -m u:"$USER":rwX .
    sudo setfacl -dR -m u:"$USER":rwX .
fi
