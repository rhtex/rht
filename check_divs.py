with open('app/Views/invoices/form.php', 'r', encoding='utf-8') as f:
    content = f.read()

for tag in ['tr', 'td', 'table', 'tbody', 'thead', 'tfoot', 'th', 'form', 'select']:
    start = content.count('<' + tag)
    end = content.count('</' + tag)
    print(f"{tag}: {start} / {end}")
