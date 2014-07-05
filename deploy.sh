#!/bin/bash

#Initialize env var

gitaddr=$(rhc apps | grep "Git URL:" | head -n 1 | awk '{print $3}')
sshaddr=$(rhc apps | grep "SSH:" | head -n 1 | awk '{print $2}')
mysqlattr='-u "$OPENSHIFT_MYSQL_DB_USERNAME" --password="$OPENSHIFT_MYSQL_DB_PASSWORD" -h "$OPENSHIFT_MYSQL_DB_HOST" -P "$OPENSHIFT_MYSQL_DB_PORT"'


#pre-push hook scripts

pre-push(){
if rhc ssh dialoquad --command '[ -d app-root/repo/wp-content/uploads ]'
then	
	echo "Found uploads folder"
	if rhc ssh dialoquad --command '[ -d app-root/data/uploads ]'
	then
		echo "Error the app-root/data/ is not clean for moving folder"
		exit 1
	fi
	echo "Moving cache setting files"
	rhc ssh dialoquad --command 'mv app-root/repo/wp-content/wp-cache-config.php app-root/data/'
	echo "Moving app-root/repo/wp-content/uploads to app-root/data/"
	rhc ssh dialoquad --command 'mv app-root/repo/wp-content/uploads app-root/data/'

else
	echo "Error /repo/wp-content/uploads folder doesn't exist"
	exit 1
fi
}

git-push(){
	git remote set-url --push origin "$gitaddr" 
	git push origin $1 $2
	git remote set-url --push origin no_push
}


clean-push(){
	rhc ssh dialoquad --command 'rm -rf git/dialoquad.git' >/dev/null 2>&1
	echo "Removed remote .git"

	if ! rhc ssh dialoquad --command 'cd git/dialoquad.git; git init --bare'; then
		post-push
		exit 1
	fi
	git checkout --orphan deploy
	git commit -am "Initial Commit" >/dev/null 2>&1
	git-push 'deploy' $1
	git checkout master
}

push(){
	git checkout deploy
	if git cherry-pick -X theirs master; then
		echo "Merged change ready to deploy"	
	else
		git checkout master
		git branch -D deploy
		post-push
		exit 1
	fi
	git-push 'deploy' $1
	git checkout master
}

#post-push hook scripts

post-push(){
echo "Cleaning app-root/repo/wp-content/uploads and restoring data/uploads/ for CDN"
rhc ssh dialoquad --command 'rm -rf app-root/repo/wp-content/uploads && mv app-root/data/uploads app-root/repo/wp-content/'
echo "Restoring cache setting files/"
rhc ssh dialoquad --command 'mv app-root/data/wp-cache-config.php app-root/repo/wp-content/'
}

#mysql scripts

mysql-remote-clean(){
	if rhc ssh dialoquad --command '[ -e app-root/data/dialoquad.sql ]'; then
		echo "Warning: Remote mysql dump existed!"
		echo "Do you wish to delete app-root/data/dialoquad.sql ?"
		select yn in "Yes" "No"; do
			case $yn in
		    	Yes ) rhc ssh dialoquad --command 'rm app-root/data/dialoquad.sql'
					break
					;;
				No ) exit 0
					;;
			esac
		done
	fi
	
}

mysql-download(){
	echo "Cleaning remote folder for mysql dump"
	mysql-remote-clean
	if rhc ssh dialoquad --command "mysqldump ${mysqlattr} dialoquad --add-drop-table > ./app-root/data/dialoquad.sql"; then
		echo "Dumping database remotely"
	else
		exit 1
	fi
	echo "Downloading database dumped file"
	scp "$sshaddr":app-root/data/dialoquad.sql ./
	echo "Importing into local mysql"
	if mysql -e 'drop database dialoquad;'; then 
		echo "Database dropped"
	else
		exit 1
	fi
	if mysql -e 'create database dialoquad;'; then
		echo "Database created for import"
	else
		exit 1
	fi
	if mysql dialoquad < dialoquad.sql; then
		echo "Successfully imported database"
	else
		exit 1
	fi
	echo "Cleaning local sql file"
	rm -f dialoquad.sql

}

mysql-upload(){
	echo "Cleaning local sql and start dumping database"
	rm -f dialoquad.sql
	mysqldump dialoquad --add-drop-table > dialoquad.sql
	mysql-remote-clean
	echo "Uploading database"
	scp dialoquad.sql "$sshaddr":app-root/data
	if rhc ssh dialoquad --command "mysql ${mysqlattr} -e 'drop database dialoquad'"; then
		echo "Dropped database dialoquad"
	else
		exit 1
	fi
	if rhc ssh dialoquad --command "mysql ${mysqlattr} -e 'create database dialoquad'"; then
		echo "Created database dialoquad for import"
	else
		exit 1
	fi
	if rhc ssh dialoquad --command "mysql ${mysqlattr} dialoquad < app-root/data/dialoquad.sql"; then
		echo "Successfully import database"
	else
		exit 1
	fi
	rm -f dialoquad.sql
}

if [ "$1" = "pre-push" ]; then
	pre-push
elif [ "$1" = "post-push" ]; then
	post-push
elif [ "$1" = "-f" ]; then
	pre-push
	push '-f'
	post-push
elif [ "$1" = "clean" ]; then
	pre-push
	clean-push
	post-push
elif [ "$1" = "mysql" ]; then
	if [ "$2" = "upload" ]; then
		mysql-upload
	elif [ "$2" = "download" ]; then
		mysql-download
	fi
elif [ -z "$*" ]; then
	pre-push
	push
	post-push
fi
exit 0

