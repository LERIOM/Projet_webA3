import pandas as pd
import joblib

# === Fichiers ===
input_csv_path = "/var/www/html/Projet_webA3/python/traj_features.csv"  # Fichier d'entrée
model_path = "/var/www/html/Projet_webA3/python/model_kmeans_trajectoires.pkl"  # Modèle KMeans
output_csv_path = "/var/www/html/Projet_webA3/python/resultats_clusters.csv"  # Fichier de sortie

# === Chargement du modèle ===
model = joblib.load(model_path)

# === Chargement des données ===
df = pd.read_csv(input_csv_path)

# === Colonnes utilisées pour la prédiction ===
feature_cols = ['lat_mean', 'lat_std', 'lon_mean', 'lon_std',
                'sog_mean', 'sog_std', 'cog_mean', 'cog_std']

# Vérification des colonnes
missing = [col for col in feature_cols if col not in df.columns]
if missing:
    raise ValueError(f"❌ Colonnes manquantes dans le CSV : {missing}")

# === Prédiction ===
X = df[feature_cols].values
clusters = model.predict(X)

# === Ajout des clusters à la base ===
df['predicted_cluster'] = clusters

# === Sauvegarde ===
df.to_csv(output_csv_path, index=False)
print(f"✅ Clusters prédits pour {len(df)} navires.")
print(f"📁 Fichier de sortie créé : {output_csv_path}")
