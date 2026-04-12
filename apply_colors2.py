import re
import os

files_to_update = [
    'templates/programs-landing.php',
    'templates/single-nucleus_program.php'
]

color_map = {
    r'#94a3b8': 'var(--ncl-text-muted)',
    r'#cbd5e1': 'var(--ncl-border)',
    r'#000(?![0-9a-fA-F])': 'var(--ncl-text-heading)',
    r'#f0fdf4': 'rgba(var(--ncl-accent-green-rgb), 0.1)',
    r'#16a34a': 'var(--ncl-accent-green)',
    r'#bbf7d0': 'rgba(var(--ncl-accent-green-rgb), 0.3)',
    r'#dcfce7': 'rgba(var(--ncl-accent-green-rgb), 0.2)',
    r'rgba\(\s*0\s*,\s*0\s*,\s*0\s*,\s*': 'rgba(var(--ncl-text-heading-rgb), ',
    # For products-landing
    r'#38a169': 'var(--ncl-accent-green)',
    r'#0056b3': 'var(--ncl-primary-hover)',
    r'#0051a8': 'var(--ncl-primary-hover)',
    r'#0062cc': 'var(--ncl-primary)',
    r'#f8f9fa': 'var(--ncl-bg)',
    r'#ffffff': 'var(--ncl-bg)',
    r'#fff(?![0-9a-fA-F])': 'var(--ncl-bg)',
    r'#e2e8f0': 'var(--ncl-border)',
    r'#f1f5f9': 'var(--ncl-surface-hover)',
    r'#e5e7eb': 'var(--ncl-border)',
    r'#111827': 'var(--ncl-text-heading)',
    r'#6B7280': 'var(--ncl-text-muted)',
    r'#9CA3AF': 'var(--ncl-text-muted)',
    r'#D1D5DB': 'var(--ncl-border)',
    r'#1e293b': 'var(--ncl-text-heading)',
}

def convert_file(path):
    if not os.path.exists(path):
        return
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

for f in files_to_update:
    convert_file(f)
