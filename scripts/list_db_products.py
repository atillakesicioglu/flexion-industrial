#!/usr/bin/env python3
import re
from pathlib import Path

import pymysql

cfg = Path(__file__).resolve().parents[1] / "website" / "includes" / "config.php"
text = cfg.read_text(encoding="utf-8")


def get_define(name: str) -> str:
    m = re.search(rf"define\('{name}',\s*'([^']*)'\)", text)
    if not m:
        raise SystemExit(f"Missing {name} in config.php")
    return m.group(1)


conn = pymysql.connect(
    host=get_define("DB_HOST"),
    user=get_define("DB_USER"),
    password=get_define("DB_PASS"),
    database=get_define("DB_NAME"),
    charset=get_define("DB_CHARSET"),
    connect_timeout=25,
)
cur = conn.cursor()
cur.execute("SELECT COUNT(*) FROM products")
total = cur.fetchone()[0]
cur.execute("SELECT COUNT(*) FROM products WHERE is_active=1")
active = cur.fetchone()[0]
print("TOPLAM:", total)
print("AKTIF:", active)

cur.execute(
    """
    SELECT c.name AS cat, COUNT(*) AS cnt
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    GROUP BY c.name
    ORDER BY cnt DESC
    """
)
print("\nKATEGORI:")
for cat, cnt in cur.fetchall():
    print(f"  {cnt:3d}  {cat or '(kategori yok)'}")

cur.execute(
    """
    SELECT p.id, p.code, p.name, c.name AS cat, p.is_active
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY c.name, p.name
    """
)
rows = cur.fetchall()
out = Path(__file__).resolve().parent / "output" / "urun_listesi_db.txt"
lines = [
    "FLEXION DB URUN LISTESI",
    f"Toplam: {total} | Aktif: {active}",
    "",
    "No | ID | Kod | Urun | Kategori | Aktif",
    "-" * 100,
]
for i, (pid, code, name, cat, is_active) in enumerate(rows, 1):
    lines.append(
        f"{i:3d} | {pid} | {code or '-'} | {name} | {cat or '-'} | {'evet' if is_active else 'hayir'}"
    )
out.write_text("\n".join(lines), encoding="utf-8")
print("\nListe:", out)
conn.close()
