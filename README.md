Crée le dockerfile et le start.sh 

crée une image Docker a partir du dockerfile:

 docker build -t projet_weba3-web .

lancer l'image avec les ports 2222 et 8080 ouvert :



clone le projet dans /root/

configurer son adresse git 
git config --global user.name "Votre Nom"
git config --global user.email "votre.email@example.com"

telecharger extention SQLTools PostgreSQL et configurer


faire run cette commande: Plus obligatoire

# rendre chaque dossier traversable + listable
chmod 755 /var/www
chmod 755 /var/www/html
chmod 755 /var/www/html/Projet_webA3
chmod 755 /var/www/html/Projet_webA3/csv

# rendre le fichier lisible
chmod 644 /var/www/html/Projet_webA3/source/csv/vessel-total-clean-final.csv

si erreur http:

tail -f /var/log/apache2/error.log

run : 


apt-get update
apt-get install -y composer

# Puis
cd /var/www/html/Projet_webA3
composer require openai-php/client




git config --global user.name "LERIOM"
git config --global user.email "riom.antoin44@gmail.com"



    $openai = OpenAI::client('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');