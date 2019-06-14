#!/usr/bin/env bash
set -x

echo "run the following line";
echo "drop database museo; create database museo; grant all privileges on database news to news;"
sudo -u postgres psql
rm /tmp/latest.dump -f && heroku pg:backups:capture && heroku pg:backups:download -o /tmp/latest.dump
pg_restore -U news -h localhost -d museo /tmp/latest.dump --no-owner

# bin/create-admins.sh

# sudo -u postgres pg_restore -d news -1 latest.dump --no-owner -v




