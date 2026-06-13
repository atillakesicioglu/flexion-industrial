import json, pymysql
from collections import defaultdict

base = r"C:\Users\atill\OneDrive\MASAST~1\projects\flexion"
with open(base + r"\website\admin\import_data\products.json", encoding="utf-8") as f:
    data = json.load(f)

db = pymysql.connect(
    host="185.210.94.106",
    user="flexionindustria_admin",
    password="v2.Dm{bW$NzobaaJ",
    db="flexionindustria_main",
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor,
)
cur = db.cursor()

stats = defaultdict(lambda: {"total":0,"exists":0,"missing":0,"noimg":0,"with_icons":0})
missing_rows = []

for p in data:
    cat = (p.get("category_raw") or "").strip().upper()
    code = (p.get("code") or "").strip()
    name = (p.get("name") or "").strip()
    if not name:
        continue

    stats[cat]["total"] += 1
    if not p.get("main_image_file"):
        stats[cat]["noimg"] += 1
    if len(p.get("icon_files") or []) > 0:
        stats[cat]["with_icons"] += 1

    ex = None
    if code:
        cur.execute("SELECT id FROM products WHERE code=%s LIMIT 1", (code,))
        ex = cur.fetchone()
    else:
        cur.execute("SELECT id FROM products WHERE name=%s LIMIT 1", (name,))
        ex = cur.fetchone()

    if ex:
        stats[cat]["exists"] += 1
    else:
        stats[cat]["missing"] += 1
        missing_rows.append((cat, code, name))

print("=== CATEGORY SUMMARY ===")
for cat in sorted(stats.keys()):
    s = stats[cat]
    print(f"{cat}\tTOTAL={s['total']}\tEXISTS={s['exists']}\tMISSING={s['missing']}\tNOIMG={s['noimg']}\tWITH_ICONS={s['with_icons']}")

print("\n=== OVERALL ===")
total = sum(s['total'] for s in stats.values())
exists = sum(s['exists'] for s in stats.values())
missing = sum(s['missing'] for s in stats.values())
noimg = sum(s['noimg'] for s in stats.values())
print(f"TOTAL={total} EXISTS={exists} MISSING={missing} NOIMG={noimg}")

print("\n=== SAMPLE MISSING (max 30) ===")
for i, m in enumerate(missing_rows[:30], 1):
    print(f"{i}. {m[0]} | {m[1]} | {m[2]}")

db.close()
