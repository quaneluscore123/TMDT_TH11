# -*- coding: utf-8 -*-
import csv, os, io, sys, textwrap
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
from PIL import Image, ImageDraw, ImageFont

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DS = os.path.join(BASE, '02-dataset')
IMG_DIR = os.path.join(BASE, '01-cua-hang', 'img')
os.makedirs(IMG_DIR, exist_ok=True)

PASTEL = [
    (255, 218, 224), (255, 240, 200), (210, 245, 220), (200, 230, 255), (230, 210, 255),
    (255, 220, 200), (200, 255, 240), (255, 230, 210), (220, 220, 255), (255, 210, 230),
]

def get_font(size):
    for p in [r'C:\Windows\Fonts\arial.ttf', r'C:\Windows\Fonts\segoeui.ttf']:
        if os.path.exists(p):
            return ImageFont.truetype(p, size)
    return ImageFont.load_default()

def make_image(sku, name, idx):
    W, H = 800, 800
    bg = PASTEL[idx % len(PASTEL)]
    img = Image.new('RGB', (W, H), bg)
    d = ImageDraw.Draw(img)
    d.rectangle([20, 20, W-20, H-20], outline=(180, 180, 180), width=3)
    f_big = get_font(48)
    f_small = get_font(28)
    sku_text = sku
    bbox = d.textbbox((0, 0), sku_text, font=f_big)
    d.text(((W - (bbox[2]-bbox[0]))/2, 80), sku_text, fill=(60, 60, 60), font=f_big)
    lines = textwrap.wrap(name, width=18)
    y = 200
    for line in lines:
        bbox = d.textbbox((0, 0), line, font=f_small)
        d.text(((W - (bbox[2]-bbox[0]))/2, y), line, fill=(80, 80, 80), font=f_small)
        y += 40
    out = os.path.join(IMG_DIR, f'{sku.lower()}.webp')
    img.save(out, 'WEBP', quality=70)
    return os.path.getsize(out)

def main():
    with open(os.path.join(DS, 'products.csv'), encoding='utf-8-sig') as f:
        rows = list(csv.DictReader(f))
    total = 0
    for i, r in enumerate(rows):
        sz = make_image(r['sku'], r['ten_san_pham'], i)
        total += sz
        print(f"{r['sku']}.webp  {sz//1024}KB")
    print(f'Total: {total//1024}KB, avg: {total//len(rows)//1024}KB')

if __name__ == '__main__':
    main()
