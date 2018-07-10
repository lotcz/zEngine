perms:
    chown -R www-data:www-data ../zEngine

pull:
	git pull    

checkout:
    git checkout $1
        
update: pull perms
    	
upgrade: pull checkout perms

init:
	php src/init.php