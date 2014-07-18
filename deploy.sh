#!/bin/bash

#Initialize env var

gitaddr=$(rhc apps | grep "Git URL:" | head -n 1 | awk '{print $3}')
sshaddr=$(rhc apps | grep "SSH:" | head -n 1 | awk '{print $2}')
mysqlattr='-u "$OPENSHIFT_MYSQL_DB_USERNAME" --password="$OPENSHIFT_MYSQL_DB_PASSWORD" -h "$OPENSHIFT_MYSQL_DB_HOST" -P "$OPENSHIFT_MYSQL_DB_PORT"'

#pre-push hook scripts

pre-push(){
if ! git diff-index --quiet HEAD --; then
	echo "Warning:unstaged changed files"
	echo "Do you wish to add to latest commit ?"
	select yn in "Yes" "No"; do
		case $yn in
		    Yes ) git commit -a --amend --no-edit
				break
				;;
			No ) exit 0
				;;
		esac
	done
fi

if ! rhc ssh dialoquad --command '[ -f ~/app-root/data/.bashrc ]';
then
	echo "Sync remote .bashrc setting"
	scp './.wp-cli/bin/.bashrc' "$sshaddr:app-root/data/"
fi

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

#post-push hook scripts

post-push(){
echo "Cleaning app-root/repo/wp-content/uploads and restoring data/uploads/ for CDN"
rhc ssh dialoquad --command 'rm -rf app-root/repo/wp-content/uploads && mv app-root/data/uploads app-root/repo/wp-content/'
echo "Restoring cache setting files/"
rhc ssh dialoquad --command 'mv app-root/data/wp-cache-config.php app-root/repo/wp-content/'

if rhc ssh dialoquad --command '[ -f ~/app-root/data/.bashrc ]'; then
	echo "Found bashrc setting, loading..."
	if rhc ssh dialoquad --command '. ~/app-root/data/.bashrc;wp --user=dialoquad --path=app-root/repo/ super-cache flush;wp --user=dialoquad --path=app-root/repo/ super-cache preload'; then
		echo "Cache Regenerated"
	fi
fi
}

git-push(){
git remote set-url --push origin "$gitaddr" 
git push origin $1 $2
git remote set-url --push  origin no_push
}

#remove rhc git repostory and do full-upload

init-push(){
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

#cherry-pick to deploy branch and push for deployment

push(){
	git checkout deploy
	if git merge --strategy=recursive --no-edit -X theirs master; then
		echo "Merged change ready to deploy"	
	else
		post-push
		exit 1
	fi
	git-push 'deploy' $1
	git checkout master
}


#check if delete .sql file on remote data folder

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

#download and dump into local mysql

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

#dump local database and upload and import

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

#create database and image archive tar.gz

archive(){
	DATE="$(date +%m-%d)"
	mysqldump dialoquad --add-drop-table > ./wp-content/dialoquad.sql
	tar zcvf "${HOME}/Downloads/db_uploads_${DATE}.tar.gz" -C ./wp-content/ dialoquad.sql uploads
	rm ./wp-content/dialoquad.sql
}


archive-all(){
DATE="$(date +%m-%d)"
mysqldump dialoquad --add-drop-table > ../dialoquad.sql
tar zcvf "${HOME}/Downloads/dialoquad_${DATE}.tar.gz" -C ../. dialoquad.sql dialoquad
rm ../dialoquad.sql
}

#sync media folder with upload foler

media-upload(){
if rhc ssh dialoquad --command '[ -d app-root/repo/wp-content/uploads ]'
then	
	echo "Found uploads folder"
	rsync -ravz ./wp-content/uploads "${sshaddr}:app-root/repo/wp-content/" 
else
	echo "Error /repo/wp-content/uploads folder doesn't exist"
	exit 1
fi

}

if [ "$1" = "pre-push" ]; then
	pre-push
elif [ "$1" = "post-push" ]; then
	post-push
elif [ "$1" = "init" ]; then
	pre-push
	init-push
	post-push
elif [ "$1" = "archive" ]; then
	if [ -z "$2" ]; then
		archive
	elif [ "$2" = "all" ]; then
		archive-all	
	fi
elif [ "$1" = "mysql" ]; then
	if [ "$2" = "upload" ]; then
		mysql-upload
	elif [ "$2" = "download" ]; then
		mysql-download
	fi
elif [ "$1" = "-f" ]; then
	pre-push
	push '-f'
	post-push
elif [ "$1" = "media" ]; then
	if [ "$2" = "upload" ]; then
		media-upload
	fi
elif [ -z "$*" ]; then
	pre-push
	push
	post-push
fi
exit 0

