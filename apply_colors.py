import re
import os

files_to_update = [
    'assets/css/single-product.css',
    'assets/css/products-landing.css',
    'templates/programs-landing.php',
    'templates/single-nucleus_program.php'
]

# We need to map hardcoded colors to --ncl-* variables
# Whites / Lights
# #ffffff -> var(--ncl-bg)
# rgba(255, 255, 255, x) -> rgba(var(--ncl-bg-rgb), x)

# Dark Blues / Primary (e.g. #0a1628, #1a2d4a, #0f2044, #111827 - actually these might be background or text headings)
# Assuming #2563eb is primary blue:
# #2563eb -> var(--ncl-primary)
# #1d4ed8 -> var(--ncl-primary-hover)
# rgba(37, 99, 235, x) -> rgba(var(--ncl-primary-rgb), x)

# Text / Grays
# #374151, #1e293b, #0f172a, #111827 -> var(--ncl-text-heading) or var(--ncl-text-body)
# #6b7280, #64748b, #475569 -> var(--ncl-text-muted)

color_map = {
    # Whites
    r'#ffffff': 'var(--ncl-bg)',
    r'#fff(?![a-zA-Z0-9])': 'var(--ncl-bg)',
    r'rgba\(\s*255\s*,\s*255\s*,\s*255\s*,\s*': 'rgba(var(--ncl-bg-rgb), ',
    
    # Primary Blues
    r'#2563eb': 'var(--ncl-primary)',
    r'#1d4ed8': 'var(--ncl-primary-hover)',
    r'#eff6ff': 'var(--ncl-primary-muted)',
    r'#3b82f6': 'var(--ncl-primary)', # alternative light blue
    r'#60a5fa': 'var(--ncl-primary-muted)',
    r'#93c5fd': 'var(--ncl-primary-muted)',
    r'rgba\(\s*37\s*,\s*99\s*,\s*235\s*,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(\s*147\s*,\s*197\s*,\s*253\s*,\s*': 'rgba(var(--ncl-primary-rgb), ', # kinda mapping to primary-rgb
    
    # Dark Blues / Navy (usually used as hero background or headings)
    # Looking at the code, #0a1628 is dark navy usually var(--ncl-bg-secondary) or similar? 
    r'#0a1628': 'var(--ncl-text-heading)', # Wait, if it's hero background, should it be ncl-secondary or bg? Let's map to var(--ncl-secondary) 
    r'#1a2d4a': 'var(--ncl-secondary-hover)',
    r'#0f2044': 'var(--ncl-secondary-muted)',
    
    # Text/Grays
    r'#111827': 'var(--ncl-text-heading)',
    r'#1e293b': 'var(--ncl-text-heading)',
    r'#0f172a': 'var(--ncl-text-heading)',
    r'#334155': 'var(--ncl-text-heading)',
    r'#374151': 'var(--ncl-text-body)',
    r'#475569': 'var(--ncl-text-body)',
    r'#6b7280': 'var(--ncl-text-muted)',
    r'#64748b': 'var(--ncl-text-muted)',
    r'#9ca3af': 'var(--ncl-text-muted)',
    
    # Borders / light grays
    r'#e5e7eb': 'var(--ncl-border)',
    r'#e2e8f0': 'var(--ncl-border)',
    r'#f8fafc': 'var(--ncl-surface)',
    r'#f9fafb': 'var(--ncl-surface)',
    r'#f1f5f9': 'var(--ncl-surface-hover)',
    
    # Special Greens / others
    r'#10b981': 'var(--ncl-accent-green)',
    r'#059669': 'var(--ncl-accent-green)',
}

def convert_file(path):
    if not os.path.exists(path):
        print(f"Not found: {path}")
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
        print(f"No changes for {path}")

for f in files_to_update:
    convert_file(f)
