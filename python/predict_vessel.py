import argparse
import joblib
import numpy as np
import os

# Charger le modèle entraîné
model = joblib.load("model_vessel_type_one_line_per_mmsi.pkl")

# Définir les arguments de la ligne de commande
parser = argparse.ArgumentParser(description="Prédire le type de navire")
parser.add_argument('--sog', type=float, required=True, help="Speed Over Ground")
parser.add_argument('--cog', type=float, required=True, help="Course Over Ground")
parser.add_argument('--heading', type=float, required=True, help="Heading")
parser.add_argument('--length', type=float, required=True, help="Length of the vessel")
parser.add_argument('--width', type=float, required=True, help="Width of the vessel")
parser.add_argument('--draft', type=float, required=True, help="Draft of the vessel")

args = parser.parse_args()

# Créer un tableau numpy avec les valeurs d'entrée
input_data = np.array([[args.sog, args.cog, args.heading, args.length, args.width, args.draft]])

# Faire la prédiction
predicted_class = model.predict(input_data)[0]

# Afficher le résultat
print(f"Type de navire prédit : {predicted_class}")
