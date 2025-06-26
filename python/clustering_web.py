from flask import Flask, jsonify
import pandas as pd
import joblib

app = Flask(__name__)

@app.route('../predict_navire_nav', methods=['GET'])
def predict_clusters():
    try:
        input_csv_path = "/var/www/html/Projet_webA3/python/traj_features.csv"
        model_path = "/var/www/html/Projet_webA3/python/model_kmeans_trajectoires.pkl"

        # Charger modèle et données
        model = joblib.load(model_path)
        df = pd.read_csv(input_csv_path)

        # Colonnes à utiliser
        feature_cols = ['lat_mean', 'lat_std', 'lon_mean', 'lon_std',
                        'sog_mean', 'sog_std', 'cog_mean', 'cog_std']
        if any(col not in df.columns for col in feature_cols):
            return jsonify({"error": "Colonnes manquantes dans le CSV"}), 400

        # Prédiction
        X = df[feature_cols].values
        clusters = model.predict(X)
        df['predicted_cluster'] = clusters

        # Formater la sortie JSON pour la carte
        output = df[['mmsi', 'lat_mean', 'lon_mean', 'vessel_name', 'predicted_cluster']].to_dict(orient='records')
        return jsonify(output)

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)
