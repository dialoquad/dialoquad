#!/bin/bash


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

push(){
	git remote set-url --push origin ssh://53af5dd2500446ddea00097b@dialoquad-four.rhcloud.com/~/git/dialoquad.git/
	git push
	git remote set-url --push origin no_push
}


force-push(){
	git remote set-url --push origin ssh://53af5dd2500446ddea00097b@dialoquad-four.rhcloud.com/~/git/dialoquad.git/ 
	git push -f
	git remote set-url --push origin no_push
}

#post-push hook scripts

post-push(){
echo "Cleaning app-root/repo/wp-content/uploads and restoring data/uploads/ for CDN"
rhc ssh dialoquad --command 'rm -rf app-root/repo/wp-content/uploads && mv app-root/data/uploads app-root/repo/wp-content/'
echo "Restoring cache setting files/"
rhc ssh dialoquad --command 'mv app-root/data/wp-cache-config.php app-root/repo/wp-content/'
}


#pre-push hook scripts

if [ "$1" = "pre-push" ]; then
	pre-push
elif [ "$1" = "post-push" ]; then
	post-push
elif [ "$1" = "-f" ]; then
	pre-push
	force-push
	post-push
elif [ -z "$*" ]; then
	pre-push
	push
	post-push
fi
exit 0

