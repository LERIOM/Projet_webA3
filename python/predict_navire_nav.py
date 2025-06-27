import pandas as pd
import joblib

# Fichiers
input_csv_path = "/var/www/html/Projet_webA3/csv/vessel-total-clean-final.csv"
model_path = "/var/www/html/Projet_webA3/python/model_1.pkl"
scaler_path = "/var/www/html/Projet_webA3/python/scale_1.pkl"
output_clusters_path = "/var/www/html/Projet_webA3/python/resultats_clusters.csv"
base_cleaned_path = "/var/www/html/Projet_webA3/python/data_base_150.csv"
merged_output_path = "/var/www/html/Projet_webA3/python/data_base_150_with_clusters.csv"

model = joblib.load(model_path)
scaler = joblib.load(scaler_path)

df = pd.read_csv(input_csv_path)

feature_cols = ['lat', 'lon', 'sog', 'cog', 'heading']

print("Valeurs manquantes par colonne :")
print(df[feature_cols].isna().sum())

# Imputation moyenne des NaN
df[feature_cols] = df[feature_cols].fillna(df[feature_cols].mean())

X_scaled = scaler.transform(df[feature_cols].values)
df['predicted_cluster'] = model.predict(X_scaled)

df.to_csv(output_clusters_path, index=False)
print(f"Clusters prédits et fichier enregistré : {output_clusters_path}")

df_base = pd.read_csv(base_cleaned_path)
df_merged = df_base.merge(df[['mmsi', 'predicted_cluster']], on='mmsi', how='left')
df_merged.to_csv(merged_output_path, index=False)
print(f"Fichier fusionné avec clusters enregistré sous : {merged_output_path}")
