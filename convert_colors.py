import os
import re

directories = ['assets/css/backup/home2', 'assets/css/backup/assessment', 'assets/css/backup/contact']

replacements = {
    # Whites and brights
    r'#ffffff': 'var(--ncl-bg)',
    r'#fff': 'var(--ncl-bg)',
    r'rgba\(255,\s*255,\s*255,\s*': 'rgba(var(--ncl-bg-rgb), ',
    
    # Dark blues / primary like #1A224D, #1d4ed8, #0a1628
    r'#0[a-f0-9]{5}': 'var(--ncl-primary)',
    r'#1[a-f0-9]{5}': 'var(--ncl-primary)',
    r'#2[a-f0-9]{5}': 'var(--ncl-primary)',
    r'rgba\(10,\s*22,\s*40,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(30,\s*58,\s*95,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(37,\s*99,\s*235,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(96,\s*165,\s*250,\s*': 'rgba(var(--ncl-primary-rgb), ',
    r'rgba\(124,\s*58,\s*237,\s*': 'rgba(var(--ncl-primary-rgb), ',
    
    # Greens
    r'#A7C539': 'var(--ncl-accent-green)',
    
    # Grays and texts
    r'#333333': 'var(--ncl-text-heading)',
    r'#1E293B': 'var(--ncl-text-heading)',
    r'#0F172A': 'var(--ncl-text-heading)',
    r'#64748b': 'var(--ncl-text-muted)',
    r'#475569': 'var(--ncl-text-muted)',
    r'#EFF6FF': 'var(--ncl-surface)',
    r'#F8FAFC': 'var(--ncl-bg)'
}

def convert_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    new_content = content
    for pattern, replacement in replacements.items():
        new_content = re.sub(pattern, replacement, new_content, flags=re.IGNORECASE)
        
    if content != new_content:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {path}")

for d in directories:
    for root, dirs, files in os.walk(d):
        abs_root = os.path.join(os.getcwd(), d) if not os.path.isabs(d) else d # wait d is already relative to cwd
        for file in files:
            if file.endswith('.css'):
                convert_file(os.path.join(root, file))
