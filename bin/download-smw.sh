#!/usr/bin/env bash
cd ~/Downloads
sudo wget --progress=bar:force https://releases.wikimedia.org/mediawiki/1.31/mediawiki-1.31.1.tar.gz
tar -xvzf ./mediawiki-1.31.1.tar.gz
# sudo rm -rf /var/lib/mediawiki
sudo mkdir -p /var/lib/mediawiki
sudo mv mediawiki-1.31.1/* /var/lib/mediawiki
cd /var/www/museo/smw
sudo ln -s /var/lib/mediawiki mediawiki
ls /var/www/museo/mediawiki