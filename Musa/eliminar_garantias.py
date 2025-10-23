import re

# Leer el archivo
with open('c:/xampp/htdocs/Musa/Musa/index.html', 'r', encoding='utf-8') as file:
    content = file.read()

print("🔧 Eliminando secciones de garantías...")

# Patrón para encontrar las secciones de garantías
pattern = r'<!-- Garant[íiÃ­]as destacadas -->\s*<div class="guarantees mt-3">.*?</div>\s*</div>'

# Contar ocurrencias antes
before_count = len(re.findall(pattern, content, re.DOTALL))
print(f"📊 Secciones encontradas: {before_count}")

# Reemplazar todas las ocurrencias
new_content = re.sub(pattern, '<!-- Sección de garantías eliminada -->', content, flags=re.DOTALL)

# Contar después
after_pattern = r'guarantees mt-3'
after_count = len(re.findall(after_pattern, new_content))
print(f"📊 Secciones restantes: {after_count}")

# Guardar el archivo
with open('c:/xampp/htdocs/Musa/Musa/index.html', 'w', encoding='utf-8') as file:
    file.write(new_content)

print("✅ Proceso completado")