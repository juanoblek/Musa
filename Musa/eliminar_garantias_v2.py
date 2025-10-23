import re

# Leer el archivo
with open('c:/xampp/htdocs/Musa/Musa/index.html', 'r', encoding='utf-8') as file:
    content = file.read()

print("🔧 Eliminando secciones de garantías...")

# Buscar solo el div específico
simple_pattern = r'<div class="guarantees mt-3">.*?</div>\s*</div>'

# Contar ocurrencias antes
before_count = len(re.findall(simple_pattern, content, re.DOTALL))
print(f"📊 Divs de garantías encontrados: {before_count}")

# Reemplazar 
new_content = re.sub(simple_pattern, '<!-- Sección de garantías eliminada --></div>', content, flags=re.DOTALL)

# Verificar resultado
after_count = len(re.findall(r'guarantees mt-3', new_content))
print(f"📊 Referencias restantes: {after_count}")

# Guardar el archivo
with open('c:/xampp/htdocs/Musa/Musa/index.html', 'w', encoding='utf-8') as file:
    file.write(new_content)

print("✅ Proceso completado")