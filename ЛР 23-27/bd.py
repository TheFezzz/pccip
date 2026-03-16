import pandas as pd
import sqlite3

# Путь к CSV-файлу
csv_path = "peopleqqqqq.csv"  # файл должен лежать рядом со скриптом
sqlite_path = "bd_from_csv.sqlite"

# Загрузка CSV с правильным разделителем
df = pd.read_csv(csv_path, sep=";")

# Без удаления пустых колонок
# Сохраняем все колонки как есть, включая Unnamed: и т.п.

# Создание SQLite-базы
conn = sqlite3.connect(sqlite_path)

# Импорт всей таблицы в базу
df.to_sql("fallen_soldiers", conn, if_exists="replace", index=False)

conn.close()
print(f"База данных создана: {sqlite_path}")
