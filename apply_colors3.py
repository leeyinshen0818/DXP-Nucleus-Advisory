import re
import os

files_to_update = [
    'assets/css/products-landing.css',
    'assets/css/single-product.css',
]

color_map = {
    r'#7c3aed': 'var(--ncl-tertiary)',
    r'#e0f2fe': 'var(--ncl-surface-hover)',
    r'#e8ecf1': 'var(--ncl-border)',
    r'#94a3b8': 'var(--ncl-text-muted)',
    r'#eef2f7': 'var(--ncl-bg)',
    r'rgba\(\s*0\s*,\s*0\s*,\s*0\s*,\s*': 'rgba(var(--ncl-text-heading-rgb), ',
    r'rgba\(\s*124\s*,\s*58\s*,\s*237\s*,\s*': 'rgba(var(--ncl-tertiary-rgb), '
}

def convert_file(path):
    if not os.path.exists(path): return
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    original = content
    for pattern, replacement in color_map.items():
        content = re.sub(pattern, replacement, content, flags=re.IGNORECASE)
    if original != content:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {path}")
    else:
        print(f"No changes {path}")

for f in files_to_update: convert_file(f)
