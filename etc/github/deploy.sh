eval $(ssh-agent)
ssh-add -k ~/.ssh/id_rsa_ntwglobal_ople-backoffice
/usr/bin/git reset --hard origin/master
/usr/bin/git clean -f -d
/usr/bin/git pull origin master