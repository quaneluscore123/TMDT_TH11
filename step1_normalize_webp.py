# -*- coding: utf-8 -*-
import os, glob
from PIL import Image, ImageOps

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SRC_DIR = os.path.join(BASE_DIR, 'images')
DEST_DIR = os.path.join(BASE_DIR, '01-cua-hang', 'wp-content', 'uploads', 'products')

os.makedirs(DEST_DIR, exist_ok=True)

files = glob.glob(os.path.join(SRC_DIR, '*.jpg'))
print(f"Found {len(files)} source JPG images in {SRC_DIR}")

processed = 0
for f in files:
    filename = os.path.basename(f)
    sku, _ = os.path.splitext(filename)
    sku_lower = sku.lower()
    dest_file = os.path.join(DEST_DIR, f"{sku_lower}.webp")
    
    with Image.open(f) as im:
        # 1. Handle EXIF orientation
        im = ImageOps.exif_transpose(im)
        im = im.convert('RGB')
        
        # 2. Crop center square 1:1
        w, h = im.size
        min_dim = min(w, h)
        left = (w - min_dim) // 2
        top = (h - min_dim) // 2
        right = left + min_dim
        bottom = top + min_dim
        im_cropped = im.crop((left, top, right, bottom))
        
        # 3. Resize to 800x800
        im_resized = im_cropped.resize((800, 800), Image.LANCZOS)
        
        # 4. Save WebP q80
        im_resized.save(dest_file, 'WEBP', quality=80)
        
    sz = os.path.getsize(dest_file)
    print(f"[{processed+1}/{len(files)}] {sku_lower}.webp -> {sz//1024} KB")
    processed += 1

print(f"\nStep 1 Complete: {processed} images processed and saved to {DEST_DIR}!")
