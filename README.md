# Mettre le dockerfile et le start.sh dans un dossier puis y acceder
 cd /chemin/vers/ton/dossier
# crée une image Docker a partir du dockerfile:

 docker build -t projet_weba3-web .

lancer l'image avec les ports 2222 et 8080 ouvert et 5432

# run : 

apt-get update
apt-get install -y composer

# Puis
cd /var/www/html/Projet_webA3
composer require openai-php/client

# n'oubliez as d'ajoutter votre clé API d'open IA dans le fichier chatControler.php

# telecharger extention SQLTools PostgreSQL et configurer puis lancer la création de la BD dirrectement sur le fichier

# si erreur de lecture du fichier csv a la création de la BD

# rendre chaque dossier traversable + listable
chmod 755 /var/www
chmod 755 /var/www/html
chmod 755 /var/www/html/Projet_webA3
chmod 755 /var/www/html/Projet_webA3/csv

# rendre le fichier lisible
chmod 644 /var/www/html/Projet_webA3/source/csv/vessel-total-clean-final.csv

# si erreur http:

tail -f /var/log/apache2/error.log

# Merci pour votre test et bonne découverte!!


