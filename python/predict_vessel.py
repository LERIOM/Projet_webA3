import argparse
import joblib
import pandas as pd
import json
import os   

# Charger le modèle entraîné
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "model_vessel_type_logistic.pkl")
model = joblib.load(model_path)

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
input_df = pd.DataFrame(
    [[args.sog, args.cog, args.heading, args.length, args.width, args.draft]],
    columns=['sog', 'cog', 'heading', 'length', 'width', 'draft']
)

# Faire la prédiction
predicted_class = model.predict(input_df)[0]

predicted_class = int(predicted_class)


result = {
    "type": predicted_class
}

# Imprimer le JSON (une seule ligne) — côté PHP, json_decode($output, true) renverra un tableau associatif
print(json.dumps(result))
