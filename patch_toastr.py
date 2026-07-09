import re

filepath = r"d:\xampp\htdocs\RHT\app\Views\invoices\form.php"

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r'toastr\.error\((.*?)\);', r'alert(\1);', content)
content = re.sub(r'toastr\.success\((.*?)\);', r'alert(\1);', content)
content = re.sub(r'toastr\.warning\((.*?)\);', r'alert(\1);', content)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched form.php successfully.")
