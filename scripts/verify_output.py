# coding: utf-8
import json, os

BASE     = r'C:\Users\atill\OneDrive\MASAST~1\projects\flexion'
PROD_JSON = os.path.join(BASE, 'website', 'admin', 'import_data', 'products.json')
IMG_DIR   = os.path.join(BASE, 'website', 'admin', 'import_data', 'images')
ICON_DIR  = os.path.join(BASE, 'website', 'admin', 'import_data', 'icons')

with open(PROD_JSON, 'r', encoding='utf-8') as f:
    products = json.load(f)

print("=== OZET ===")
print("Toplam urun: %d" % len(products))
has_img  = sum(1 for p in products if p.get('main_image_file'))
has_icon = sum(1 for p in products if p.get('icon_files'))
no_name  = sum(1 for p in products if not p.get('name','').strip())
print("Urun gorseli olan: %d" % has_img)
print("Ikon olan: %d" % has_icon)
print("Adi olmayanlar: %d" % no_name)

# Kategori dagilimi
cats = {}
for p in products:
    cr = (p.get('category_raw') or '').upper().strip()
    cats[cr] = cats.get(cr, 0) + 1
print("\nKategori dagilimi (%d kategori):" % len(cats))
for k in sorted(cats):
    print("  %-40s: %d" % (k[:40], cats[k]))

# Sample HTML descriptions
print("\n=== ORNEK HTML ACIKLAMA (ilk urun) ===")
for p in products[:1]:
    print("Ad: %s" % p.get('name',''))
    print("Kod: %s" % p.get('code',''))
    print("Kategori: %s" % p.get('category_raw',''))
    print("HTML aciklama (ilk 500 karakter):")
    print(p.get('html_description','')[:500])

# Image file integrity
print("\n=== GORSEL DOGRULAMA ===")
missing_imgs = 0
for p in products:
    f = p.get('main_image_file')
    if f:
        path = os.path.join(IMG_DIR, f)
        if not os.path.isfile(path):
            missing_imgs += 1
            print("  MISSING: %s" % f)
print("Eksik gorsel: %d" % missing_imgs)

# Icon file integrity
missing_icons = 0
for p in products:
    for icon_f in p.get('icon_files', []):
        path = os.path.join(ICON_DIR, icon_f)
        if not os.path.isfile(path):
            missing_icons += 1
print("Eksik ikon: %d" % missing_icons)

# Category mapping check - which cats are NOT in CAT_MAP
CAT_MAP_KEYS = {
    'WATER HOSES','AIR-GAS','AIR - GAS HOSES','OIL & PETROLEUM','WELDING','FOOD',
    'FOOD & BEVERAGE HOSES','MATERIAL HANDLING','SEWER & ROAD CLEANING',
    'SEWER JETTING & CLEANING HOSES','STEAM','CHEMICAL','HYDRAULIC HOSES','HYDRAULIK HOSES',
    'ABRASIVE FOOD','ALIZE 200\u00b0C','ALIZE SD 200\u00b0C','CABLE PROTECTION',
    'FIRE FIGHTING','HOT WATER','HOT AIR BLOWER HOSES','OFFSHORE',
    'PETROLEUM DISPENSING','PVC HOSES','SILICONE','STEEL MILL','THERMOPLASTIC HOSES',
}
print("\n=== KATEGORISI ESLESMEYENLER ===")
unmatched_cats = {k: v for k, v in cats.items() if k not in CAT_MAP_KEYS and k}
if unmatched_cats:
    for k, v in unmatched_cats.items():
        print("  '%s': %d urun" % (k, v))
else:
    print("  Hepsi eslestirildi.")

print("\nDOGRULAMA TAMAMLANDI.")
