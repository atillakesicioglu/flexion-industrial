# coding: utf-8
import os, io, json, shutil, re
from PIL import Image

BASE       = r'C:\Users\atill\OneDrive\MASAST~1\projects\flexion'
SRC_IMG    = os.path.join(BASE, 'scripts', 'output', 'images')
SRC_JSON   = os.path.join(BASE, 'scripts', 'output', 'products.json')
DEST_DIR   = os.path.join(BASE, 'website', 'admin', 'import_data')
DEST_IMG   = os.path.join(DEST_DIR, 'images')
DEST_ICONS = os.path.join(DEST_DIR, 'icons')

os.makedirs(DEST_IMG, exist_ok=True)
os.makedirs(DEST_ICONS, exist_ok=True)

def normalize_image(src_path, dest_path):
    try:
        with Image.open(src_path) as img:
            img = img.convert('RGB')
            img.save(dest_path, 'JPEG', quality=85)
        return True
    except Exception as e:
        print("  [SKIP] %s: %s" % (os.path.basename(src_path), e))
        return False

with open(SRC_JSON, 'r', encoding='utf-8') as f:
    products = json.load(f)

print("Toplam urun: %d" % len(products))
updated = 0
failed_images = 0

for p in products:
    main_file = p.get('main_image_file')
    if main_file:
        src = os.path.join(SRC_IMG, main_file)
        dest = os.path.join(DEST_IMG, main_file)
        if os.path.exists(src):
            ok = normalize_image(src, dest)
            if ok:
                updated += 1
            else:
                p['main_image_file'] = None
                failed_images += 1
        else:
            p['main_image_file'] = None

    new_icon_files = []
    for icon_file in p.get('icon_files', []):
        src = os.path.join(SRC_IMG, icon_file)
        dest = os.path.join(DEST_ICONS, icon_file)
        if os.path.exists(src):
            ok = normalize_image(src, dest)
            if ok:
                new_icon_files.append(icon_file)
    p['icon_files'] = new_icon_files

print("Guncellenen gorsel: %d, Basarisiz: %d" % (updated, failed_images))

dest_json = os.path.join(DEST_DIR, 'products.json')
with open(dest_json, 'w', encoding='utf-8') as f:
    json.dump(products, f, indent=2, ensure_ascii=False)
print("JSON kaydedildi: %s" % dest_json)

img_files = os.listdir(DEST_IMG)
icon_files_list = os.listdir(DEST_ICONS)
print("Urun gorseli: %d, Ikon gorseli: %d" % (len(img_files), len(icon_files_list)))
