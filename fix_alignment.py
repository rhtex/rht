import re

filepath = r"d:\xampp\htdocs\RHT\app\Views\invoices\form.php"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix 1: colspan="5" -> colspan="6"
content = content.replace('<td colspan="5" class="text-end">IGST', '<td colspan="6" class="text-end">IGST')
content = content.replace('<td colspan="5" class="text-end">CGST', '<td colspan="6" class="text-end">CGST')
content = content.replace('<td colspan="5" class="text-end">SGST', '<td colspan="6" class="text-end">SGST')

# Fix 2: HTML structure
# Replace <tfoot class="table-secondary"> with <tbody class="table-secondary">
content = content.replace('<tfoot class="table-secondary">', '<tbody class="table-secondary">')
# Replace </tfoot> with </tbody>
content = content.replace('</tfoot>', '</tbody>')

# Replace the inner <tbody id="taxBreakdownBody"> with closing/opening wrapping
target = '<tbody id="taxBreakdownBody">\n                                <!-- Dynamic tax rows -->\n                            </tbody>'
replacement = '</tbody>\n                        <tbody id="taxBreakdownBody" class="table-secondary">\n                                <!-- Dynamic tax rows -->\n                            </tbody>\n                        <tbody class="table-secondary">'
content = content.replace(target, replacement)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed alignment successfully")
