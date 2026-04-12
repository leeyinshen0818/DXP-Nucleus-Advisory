import re
import os

color_map = {
    # Whites / Backgrounds
    r'#ffffff': 'var(--ncl-bg)',
    r'#fff(?![a-zA-Z0-9])': 'var(--ncl-bg)',
    r'#F8FAFC': 'var(--ncl-bg)',
    r'#f8fafc': 'var(--ncl-bg)',
    r'#eff6ff': 'var(--ncl-surface)',
    r'#f1f5f9': 'var(--ncl-surface)',
    r'rgba\(\s*255\s*,\s*255\s*,\s*255\s*,\s*': 'rgba(var(--ncl-bg-rgb), ',

    # Primary Blues
    r'#2563eb': 'var(--ncl-primary)',
    r'#1d4ed8': 'var(--ncl-primary-hover)',
    r'#60a5fa': 'var(--ncl-primary-muted)',
    r'#93c5fd': 'var(--ncl-primary-muted)',
    r'rgba\(\s*37\s*,\s*99\s*,\s*235\s*,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(\s*96\s*,\s*165\s*,\s*250\s*,\s*': 'rgba(var(--ncl-primary-rgb), ',

    # Dark Navys / Text Headings
    r'#0A1628': 'var(--ncl-text-heading)',
    r'#0a1628': 'var(--ncl-text-heading)',
    r'#0f172a': 'var(--ncl-text-heading)',
    r'#1e293b': 'var(--ncl-text-heading)',
    r'#1E3A5F': 'var(--ncl-text-heading)',
    r'#0f2557': 'var(--ncl-text-heading)',
    r'rgba\(\s*10\s*,\s*22\s*,\s*40\s*,\s*': 'rgba(var(--ncl-text-heading-rgb), ',
    r'rgba\(\s*30\s*,\s*58\s*,\s*95\s*,\s*': 'rgba(var(--ncl-text-heading-rgb), ',

    # Grays / Text Muted / Borders
    r'#475569': 'var(--ncl-text-muted)',
    r'#64748b': 'var(--ncl-text-muted)',
    r'#e2e8f0': 'var(--ncl-border)',
}

for root, _, files in os.walk('assets/css/backup/home2'):
    for file in files:
        if file.endswith('.css'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            original = content
            for pattern, replacement in color_map.items():
                content = re.sub(pattern, replacement, content, flags=re.IGNORECASE)
            
            if original != content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated {path}")
