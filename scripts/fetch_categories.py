# -*- coding: utf-8 -*-
"""
Veritabanindaki kategorileri cek, Excel kategorileriyle eslestir.
"""
import pymysql
import json, os

DB = {
    'host':    '185.210.94.106',
    'user':    'flexionindustria_admin',
    'password':'v2.Dm{bW$NzobaaJ',
    'db':      'flexionindustria_main',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor,
}

BASE = r'C:\Users\atill\OneDrive\MASAST~1\projects\flexion'
OUT_DIR = os.path.join(BASE, 'scripts', 'output')

conn = pymysql.connect(**DB)
cur = conn.cursor()
cur.execute('SELECT id, name, slug, parent_id FROM categories ORDER BY parent_id, sort_order, id')
rows = cur.fetchall()
conn.close()

print("=== DB Kategorileri ===")
for r in rows:
    print(f"  ID={r['id']:3d} parent={r['parent_id']} slug='{r['slug']}' name='{r['name']}'")

# Excel kategori degerleri (analyze_xlsx2.py'den)
EXCEL_CATS = {
    'ABRASIVE FOOD': 6,
    'AIR-GAS': 15,
    'ALIZE 200\u00b0C': 1,
    'ALIZE SD 200\u00b0C': 1,
    'CABLE PROTECTION': 6,
    'CHEMICAL': 19,
    'FIRE FIGHTING': 5,
    'FOOD': 13,
    'HOT WATER': 9,
    'HYDRAULIC HOSES': 90,
    'MATERIAL HANDLING': 22,
    'OFFSHORE': 11,
    'OIL & PETROLEUM': 37,
    'PETROLEUM DISPENSING': 4,
    'PVC HOSES': 66,
    'SEWER & ROAD CLEANING': 9,
    'SILICONE': 2,
    'STEAM': 7,
    'STEEL MILL': 8,
    'THERMOPLASTIC HOSES': 16,
    'WATER HOSES': 12,
    'WELDING': 5,
}

# Otomatik eslestirme deneme
db_name_to_id = {r['name'].upper().strip(): r['id'] for r in rows}
db_name_to_slug = {r['name'].upper().strip(): r['slug'] for r in rows}

def try_match(excel_cat):
    eu = excel_cat.upper().strip()
    # Tam eslesme
    if eu in db_name_to_id:
        return db_name_to_id[eu], db_name_to_slug[eu], 'exact'
    # "HOSES" ekle
    if eu + ' HOSES' in db_name_to_id:
        return db_name_to_id[eu + ' HOSES'], db_name_to_slug[eu + ' HOSES'], 'hoses_added'
    # "HOSES" cikar
    if eu.endswith(' HOSES') and eu[:-6] in db_name_to_id:
        k = eu[:-6]
        return db_name_to_id[k], db_name_to_slug[k], 'hoses_removed'
    # Kismi eslesme
    for db_name, db_id in db_name_to_id.items():
        if eu in db_name or db_name in eu:
            return db_id, db_name_to_slug[db_name], 'partial'
    return None, None, 'no_match'

print("\n=== Kategori Eslestirme ===")
mapping = {}
unmatched = []
for excel_cat, count in sorted(EXCEL_CATS.items()):
    db_id, db_slug, match_type = try_match(excel_cat)
    if db_id:
        print(f"  [{match_type}] '{excel_cat}' ({count}) -> DB id={db_id} slug='{db_slug}'")
        mapping[excel_cat] = {'db_id': db_id, 'db_slug': db_slug, 'match_type': match_type}
    else:
        print(f"  [NO_MATCH] '{excel_cat}' ({count}) -> ???")
        unmatched.append({'excel_cat': excel_cat, 'count': count})
        mapping[excel_cat] = {'db_id': None, 'db_slug': None, 'match_type': 'no_match'}

print(f"\nEslesen: {len(mapping) - len(unmatched)}/{len(mapping)}")
print(f"Eslesmeyenler: {len(unmatched)}")
for u in unmatched:
    print(f"  - '{u['excel_cat']}' ({u['count']} urun)")

# Kaydet
mapping_path = os.path.join(OUT_DIR, 'category_mapping.json')
with open(mapping_path, 'w', encoding='utf-8') as f:
    json.dump(mapping, f, indent=2, ensure_ascii=False)
print(f"\nEslestirme kaydedildi: {mapping_path}")
db_cats_path = os.path.join(OUT_DIR, 'db_categories.json')
with open(db_cats_path, 'w', encoding='utf-8') as f:
    json.dump(rows, f, indent=2, ensure_ascii=False)
print(f"DB kategoriler kaydedildi: {db_cats_path}")
