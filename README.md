Crée le dockerfile et le start.sh 

crée une image Docker a partir du dockerfile:

 docker build -t projet_weba3-web .

lancer l'image avec les ports 2222 et 8080 ouvert :



clone le projet dans /root/

configurer son adresse git 
git config --global user.name "Votre Nom"
git config --global user.email "votre.email@example.com"

telecharger extention SQLTools PostgreSQL et configurer


faire run cette commande: 

chmod 711 /root

# rendre les sous-dossiers traversables + listables
chmod 755 /root/Projet_webA3
chmod 755 /root/Projet_webA3/source
chmod 755 /root/Projet_webA3/source/csv     # ou /source/sql si c’est ton chemin

# rendre le CSV lisible
chmod 644 /root/Projet_webA3/source/csv/vessel-total-clean-final.csv

