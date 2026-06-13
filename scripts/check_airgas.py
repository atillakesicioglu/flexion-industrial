import json, pymysql
base = r"C:\Users\atill\OneDrive\MASAST~1\projects\flexion"
with open(base + r"\website\admin\import_data\products.json", encoding="utf-8") as f:
    data = json.load(f)
rows = [p for p in data if (p.get("category_raw", "").strip().upper() == "AIR-GAS")]
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
exists_count = 0
for p in rows:
    code = (p.get("code") or "").strip()
    name = (p.get("name") or "").strip()
    cur.execute("SELECT id FROM products WHERE code=%s LIMIT 1", (code,))
    ex = cur.fetchone()
    if ex:
        exists_count += 1
    has_img = "Y" if p.get("main_image_file") else "N"
    icon_cnt = len(p.get("icon_files") or [])
    exists = "Y" if ex else "N"
    print(f"{code}\t{name}\timage={has_img}\ticons={icon_cnt}\texists={exists}")
print("EXISTS_TOTAL", exists_count)
print("MISSING_TOTAL", len(rows)-exists_count)
db.close()
