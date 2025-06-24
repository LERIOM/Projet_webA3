import pandas as pd

# === Fichiers ===
base_cleaned_path = "/var/www/html/Projet_webA3/csv/vessel-total-clean-final.csv"  # <- Fichier contenant les données nettoyées
clusters_path = "/var/www/html/Projet_webA3/python/resultats_clusters_150.csv"  # <- Fichier avec les 150 clusters
output_path = "/var/www/html/Projet_webA3/data_base_150_with_clusters.csv"  # <- Fichier final

# === Chargement des fichiers ===
df_cleaned = pd.read_csv(base_cleaned_path)
df_clusters = pd.read_csv(clusters_path)

# === Fusion des deux tables sur la colonne 'mmsi'
df_final = df_cleaned.merge(df_clusters[['mmsi', 'cluster_kmeans']], on='mmsi', how='left')

# === Remplacement des valeurs nulles par 0
df_final.fillna("NA", inplace=True)

# === Sauvegarde du fichier final
df_final.to_csv(output_path, index=False)
print(f"✅ Fusion réussie. Fichier enregistré sous : {output_path}")