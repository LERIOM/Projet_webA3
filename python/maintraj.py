import argparse
import joblib
import numpy as np
import pandas as pd
import os

# Charger le modèle entraîné
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "trajectoire.pkl")

model = joblib.load(model_path)
print("Modèle chargé.")

# Définir les arguments de la ligne de commande
parser = argparse.ArgumentParser(description="Prédire le type de navire")
parser.add_argument('--lat', type=float, required=True)
parser.add_argument('--lon', type=float, required=True)
parser.add_argument('--sog', type=float, required=True)
parser.add_argument('--cog', type=float, required=True)
parser.add_argument('--heading', type=float, required=True)
parser.add_argument('--length', type=float, required=True)
parser.add_argument('--draft', type=float, required=True)
parser.add_argument('--delta_seconds', type=int, required=True)

args = parser.parse_args()

input = np.array([[args.lat, args.lon, args.sog, args.cog, args.heading, args.length, args.draft, args.delta_seconds]])

# Convertir en DataFrame 
X_new = pd.DataFrame([input])

# Prédire la position future
predicted_position = model.predict(X_new)


# Afficher le résultat
print("\n Position future estimée pour delta_seconds =", input["delta_seconds"], "secondes :")
print("Latitude :", predicted_position[0][0])
print("Longitude:", predicted_position[0][1])