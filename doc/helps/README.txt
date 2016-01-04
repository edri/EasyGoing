Tout d'abord, nous conseillons fortement d'utiliser les navigateurs Firefox ou Google Chrome, sur lesquels nous avons effectué tous nos tests. Il se peut que sur d'autres navigateurs des erreurs d'interface apparaissent.

Nous recommandons fortement au client de ne pas installer l'environnement Zend sur sa machine, car elle peut s'avérer complexe, et beaucoup de technologies entrent en jeu.
Nous avons pu héberger momentanément le site sur le domaine http://easygoing.my-chic-paradise.com/, qui sera accessible jusqu'au rendu final des notes du projet.
Cependant, si l'installation sur une machine tierce est tout de même désirée, voici les instructions pour Windows :
1	Installer Wamp avec PHP en version 5.4 minimum (recommandé 5.5).
1.	Placer le dossier du projet dans le répertoire "www" de "Wamp".
2.	Se rendre dans "C:/Windows/System32/drivers/etc/hosts" et ajouter un nouvel host :
		127.0.0.1	easygoing
	Si problème survient à la sauvegarde => ouvrir le fichier en tant qu'administrateur.
3. 	Se rendre dans "Wamp/bin/apache/conf/httpd.conf"
	Décommenter les lignes suivantes :
		# Include conf/extra/httpd-vhosts.conf		
		# LoadModule rewrite_module modules/mod_rewrite.so
4. 	Ajouter un vhost dans "Wamp/bin/apache/conf/extra/httpd-vhosts.conf"
		<VirtualHost *:80>
			ServerName easygoing
			DocumentRoot "REPERTOIRE_DE_WAMP/www/EasyGoing/public" # ATTENTION A METTRE DES "/" ET NON DES "\" ; ne pas oublier de changer REPERTOIRE_DE_WAMP !
			SetEnv APPLICATION_ENV "development"
			<Directory "REPERTOIRE_DE_WAMP/www/EasyGoing/public">
				DirectoryIndex index.php
				AllowOverride All
				Order allow,deny
				Allow from all
				Require all granted
			</Directory>
		</VirtualHost>
5. 	Configurer la DB (cf. premier paragraphe de "ComminicateWithDatabase.txt").
6.	Exécuter le script de création de la base de données fourni.

Ensuite, lancer les serveurs d'événements pour pouvoir utiliser les WebSockets :
	1. Installer Node.js.
	1. Se rendre dans EasyGoing/eventsServers, puis ouvrir une console.
	2. Tapper "npm install".
	3. Tapper "node index.js".

Le serveur est désormais prêt ! Le projet peut être accédé depuis "http://easygoing/".


