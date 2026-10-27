<p align="center">
  <img src="images/logo_projet.png" alt="Project logo" width="120"/>
</p>

# AIS Vessel Tracker: Web App with ML Predictions and Chatbot

> A web app to explore ship traffic in the Gulf of Mexico: interactive map, vessel search, machine-learning predictions (type, cluster, future position) and an AI chatbot.

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?logo=postgresql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)
![Python](https://img.shields.io/badge/Python-3776AB?logo=python&logoColor=white)
![Plotly](https://img.shields.io/badge/Plotly-3F4F75?logo=plotly&logoColor=white)
![OpenAI](https://img.shields.io/badge/OpenAI-gpt--4o--mini-412991)

Part 3 of a 3-part ISEN project (3rd year) on vessel data, after
[Projet_A3R](https://github.com/LERIOM/Projet_A3R) (statistics in R) and
[Projet_A3IA](https://github.com/LERIOM/Projet_A3IA) (machine learning).

<p align="center">
  <img src="images/Capture%20d%E2%80%99%C3%A9cran%202025-06-12%20%C3%A0%2009.18.57.png" alt="Screenshot" width="720"/>
</p>

## Features

- **Map view:** every vessel position plotted with Plotly.
- **Vessel lookup:** search by name or MMSI, see details and position history, add new positions.
- **Predictions**, powered by the scikit-learn models from Projet_A3IA:
  - vessel type from its dimensions and movement,
  - behavior cluster from position, speed and course,
  - future position from the current one.
- **Text-to-SQL chatbot:** ask questions in plain language; `gpt-4o-mini` reads the database schema, writes the SQL query through function calling, and answers from the results.

## Architecture

```
HTML / CSS / JS (AJAX)  ──►  PHP REST router  ──►  PostgreSQL (boats, positions, statuses)
                                   │
                                   ├──►  Python scripts + .pkl models (predictions)
                                   └──►  OpenAI API (chatbot)
```

Main endpoints: `/boatAll`, `/boatMmsi`, `/vesselInfo`, `/positionTab`, `/getAllVesselsPos`, `/predictType`, `/predictCluster`, `/predictTrajectory`, `/chat`.

## Getting started

Requirements: Apache with PHP, Composer, PostgreSQL, Python 3 with `pandas`, `scikit-learn` and `joblib`.

1. Put the project in your web root (for example `/var/www/html/Projet_webA3`).
2. Install the PHP dependencies:
   ```bash
   composer install
   ```
3. Create the database from `sql/vessel_total_clean_final.sql`, then set your connection details in `php/constants.php`.
4. Add your OpenAI API key in `php/chatController.php` (use an environment variable for anything beyond local testing).
5. Open `source/index.html` through your web server.

Troubleshooting: if the CSV cannot be read when building the database, make the folders traversable (`chmod 755`) and the file readable (`chmod 644`). For HTTP errors, check `tail -f /var/log/apache2/error.log`.

## Project layout

```
source/   pages: home, map, predictions, chatbot
js/ css/  front-end logic and styles
php/      REST router, database access, chatbot controller
python/   prediction scripts and trained models
sql/      database schema and data
csv/      cleaned AIS dataset
```
