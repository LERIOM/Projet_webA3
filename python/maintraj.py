import argparse
import joblib
import numpy as np
import pandas as pd
import os
import json

# Charger le modèle entraîné
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "trajectoireLN.pkl")

model = joblib.load(model_path)

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

features = np.array([
    args.lat,
    args.lon,
    args.sog,
    args.cog,
    args.heading,
    args.length,
    args.draft,
    args.delta_seconds
]).reshape(1, -1)

X_new = pd.DataFrame(
    features,
    columns=[
        "lat", "lon", "sog", "cog",
        "heading", "length", "draft", "delta_seconds"
    ]
)

predicted_position = model.predict(X_new)

pred_lat, pred_lon = map(float, predicted_position[0])

result = [{
    "lat": pred_lat,
    "lon": pred_lon
}]

# Imprimer le JSON (une seule ligne) — côté PHP, json_decode($output, true) renverra un tableau associatif
print(json.dumps(result))