#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
HOOK_PATH="$ROOT_DIR/.git/hooks/commit-msg"

cat > "$HOOK_PATH" <<'EOF'
#!/usr/bin/env bash
set -euo pipefail

MSG_FILE="${1:-}"
if [[ -z "$MSG_FILE" || ! -f "$MSG_FILE" ]]; then
  exit 0
fi

# Elimina trazas de atribución automática de Cursor.
sed -E -i.bak \
  -e '/^[[:space:]]*Made-with:[[:space:]]*Cursor[[:space:]]*$/d' \
  -e '/^[[:space:]]*Co-authored-by:[[:space:]].*Cursor.*$/d' \
  "$MSG_FILE"
rm -f "${MSG_FILE}.bak"
EOF

chmod +x "$HOOK_PATH"
echo "Hook instalado en: $HOOK_PATH"
