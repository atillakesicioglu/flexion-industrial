# -*- coding: utf-8 -*-
import re, json, os

BASE = r'C:\Users\atill\OneDrive\MASAST~1\projects\flexion'
SQL_PATH = os.path.join(BASE, 'database.sql')
OUT_DIR = os.path.join(BASE, 'scripts', 'output')

with open(SQL_PATH, 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find INSERT INTO `categories` blocks
pattern = r"INSERT INTO `categories`[^;]+;"
inserts = re.findall(pattern, content, re.DOTALL)
print(f"Found {len(inserts)} INSERT blocks for categories")

# Parse VALUES from each block
categories = []
for ins in inserts:
    # Extract individual value tuples
    val_pattern = r'\((\d+),\s*(\d+|NULL),\s*\'((?:[^\'\\]|\\.)*)\'[^)]*\)'
    matches = re.findall(val_pattern, ins)
    for m in matches:
        cat_id, parent_id, name = m
        categories.append({
            'id': int(cat_id),
            'parent_id': int(parent_id) if parent_id != 'NULL' else 0,
            'name': name.replace("\\'", "'"),
        })

print("DB Categories found:")
for c in sorted(categories, key=lambda x: x['id']):
    print(f"  ID={c['id']:3d} parent={c['parent_id']} name='{c['name']}'")

# If no results, try broader search
if not categories:
    # Show a sample of the SQL around 'categories'
    idx = content.find('categories')
    print("\nSQL sample around 'categories':")
    print(content[max(0,idx-50):idx+500])

# Save
out_path = os.path.join(OUT_DIR, 'db_categories.json')
os.makedirs(OUT_DIR, exist_ok=True)
with open(out_path, 'w', encoding='utf-8') as f:
    json.dump(categories, f, indent=2, ensure_ascii=False)
print(f"\nSaved to: {out_path}")
