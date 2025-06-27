##Groupe 12 - Maryse, Alexandre, Yanis

import pickle
import joblib
import argparse
import pandas as pd
import os
import json

# Chemin vers le répertoire du script
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "model_1.pkl")
scale_path = os.path.join(script_dir, "scale_1.pkl")
# Chemin vers le modèle et le scaler

if __name__ == '__main__':
    # Chargement du modèle et du scaler
    model = joblib.load(model_path)
    scaler = joblib.load(scale_path)

    # Lecture des paramètres d'entrée
    parser = argparse.ArgumentParser(description="Prédire le cluster d’un navire")
    parser.add_argument("--LAT", type=float, required=True)
    parser.add_argument("--LON", type=float, required=True)
    parser.add_argument("--SOG", type=float, required=True)
    parser.add_argument("--COG", type=float, required=True)
    parser.add_argument("--Heading", type=float, required=True)
    args = parser.parse_args()

    # Création d’un DataFrame avec les données
    new_data = {
        'LAT': args.LAT,
        'LON': args.LON,
        'SOG': args.SOG,
        'COG': args.COG,
        'Heading': args.Heading
    }
    new_data_df = pd.DataFrame([new_data])

    # Application du scaler
    X_scaled = scaler.transform(new_data_df)

    # Prédiction
    predicted_cluster = model.predict(X_scaled)
    # Format JSON de sortie
    result = [{"cluster": int(predicted_cluster[0])}]
    print(json.dumps(result))
