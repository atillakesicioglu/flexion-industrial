import json, pymysql
base = r"C:\Users\atill\OneDrive\MASAST~1\projects\flexion"
with open(base + r"\website\admin\import_data\products.json", encoding="utf-8") as f:
    data = json.load(f)
rows = [p for p in data if (p.get("category_raw", "").strip().upper() in ["ALIZE 200°C", "ALIZE 200�C"])]
print("COUNT", len(rows))

db = pymysql.connect(
    host="185.210.94.106",
    user="flexionindustria_admin",
    password="v2.Dm{bW$NzobaaJ",
    db="flexionindustria_main",
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor,
)
cur = db.cursor()
for p in rows:
    code = (p.get("code") or "").strip()
    name = (p.get("name") or "").strip()
    cur.execute("SELECT p.id, p.name as db_name, c.name as cat_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.code=%s LIMIT 1", (code,))
    ex = cur.fetchone()
    has_img = "Y" if p.get("main_image_file") else "N"
    icon_cnt = len(p.get("icon_files") or [])
    if ex:
        print(f"{code}\t{name}\timage={has_img}\ticons={icon_cnt}\texists=Y\tdb_id={ex['id']}\tdb_name={ex['db_name']}\tdb_cat={ex['cat_name']}")
    else:
        print(f"{code}\t{name}\timage={has_img}\ticons={icon_cnt}\texists=N")
db.close()
