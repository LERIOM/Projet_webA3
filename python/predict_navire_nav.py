import argparse
import joblib
import numpy as np

# Chargement du modèle pré-entraîné 
model_path = "C:\\Users\\alban\\Documents\\projet_ia\\Projet_A3IA\\Besoin_Client_1\\model_kmeans_trajectoires.pkl"
model = joblib.load(model_path)

# Définition des arguments d’entrée en ligne de commande
parser = argparse.ArgumentParser(description="Prédiction du cluster ou type de navire à partir des données de navigation.")

parser.add_argument('--lat_mean', type=float, required=True, help="Latitude moyenne")
parser.add_argument('--lat_std', type=float, required=True, help="Écart-type de latitude")
parser.add_argument('--lon_mean', type=float, required=True, help="Longitude moyenne")
parser.add_argument('--lon_std', type=float, required=True, help="Écart-type de longitude")
parser.add_argument('--sog_mean', type=float, required=True, help="Vitesse moyenne (SOG)")
parser.add_argument('--sog_std', type=float, required=True, help="Écart-type de SOG")
parser.add_argument('--cog_mean', type=float, required=True, help="Cap moyen (COG)")
parser.add_argument('--cog_std', type=float, required=True, help="Écart-type de COG")

args = parser.parse_args()

# Mise en forme des données d'entrée
input_data = np.array([[args.lat_mean, args.lat_std, args.lon_mean, args.lon_std,
                        args.sog_mean, args.sog_std, args.cog_mean, args.cog_std]])

# Prédiction du cluster ou type
pred = model.predict(input_data)[0]

# Affichage du résultat
print("👉 Résultat de la prédiction :")
print(f"    Cluster ou type de navire prédit : {pred}")