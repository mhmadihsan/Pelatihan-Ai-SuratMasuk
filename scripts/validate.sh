#!/usr/bin/env bash
# Validator blueprint SIMasuk. Jalankan dari root proyek: bash scripts/validate.sh
cd "$(dirname "$0")/.." || exit 2
fail=0; warn=0
F() { echo "[FAIL] $*"; fail=$((fail+1)); }
W() { echo "[WARN] $*"; warn=$((warn+1)); }

# 1. Kelengkapan: 26 dokumen bernomor + CLAUDE.md + INDEX.md + manifest
n=$(ls docs/[0-2][0-9]_*.md 2>/dev/null | wc -l | tr -d ' ')
[ "$n" = "26" ] || F "Dokumen bernomor = $n (harus 26)"
for f in CLAUDE.md INDEX.md docs/_MANIFEST.json; do [ -f "$f" ] || F "Hilang: $f"; done

# 2. Manifest JSON valid
python3 -c "import json;json.load(open('docs/_MANIFEST.json'))" 2>/dev/null || F "docs/_MANIFEST.json bukan JSON valid"

# 3. Tidak ada sisa penanda kerangka
grep -rnE "Status: KERANGKA|\[ISI:|TODO|TBD" CLAUDE.md INDEX.md docs/*.md >/tmp/vcbd_sisa.txt && F "Sisa penanda kerangka: $(head -3 /tmp/vcbd_sisa.txt | tr '\n' ' ')"

# 4. Status tiap dokumen sesuai manifest (collapsed = COLLAPSED, lainnya = FINAL)
python3 - <<'PY' || fail=$((fail+1))
import json,glob,re,sys
m=json.load(open('docs/_MANIFEST.json')); col=set(m.get('collapsed',[])); bad=0
for f in sorted(glob.glob('docs/[0-2][0-9]_*.md')):
    num=f.split('/')[-1][:2]; t=open(f,encoding='utf8').read()
    s=re.search(r'^Status: (\w+)',t,re.M)
    want='COLLAPSED' if num in col else 'FINAL'
    if not s or s.group(1)!=want: print(f"[FAIL] {f}: Status harus {want}"); bad+=1
sys.exit(1 if bad else 0)
PY

# 5. Semua rujukan docs/NN_*.md menunjuk berkas yang ada
for ref in $(grep -rhoE "docs/[0-2][0-9]_[A-Z0-9_]+\.md" CLAUDE.md INDEX.md docs/*.md | sort -u); do
  [ -f "$ref" ] || F "Rujukan rusak: $ref"
done

# 6. INDEX memuat seluruh dokumen bernomor
for f in docs/[0-2][0-9]_*.md; do grep -q "$f" INDEX.md || F "INDEX tidak memuat $f"; done

# 7. Hukum 2: DDL skema hanya di docs/07
x=$(grep -lE "CREATE TABLE" CLAUDE.md INDEX.md docs/*.md | grep -v "docs/07_DATA_MODEL.md")
[ -z "$x" ] || F "CREATE TABLE di luar 07: $x"

# 8. CLAUDE.md tetap ringkas (<= 80 baris)
l=$(wc -l < CLAUDE.md); [ "$l" -le 80 ] || F "CLAUDE.md $l baris (maks 80)"
[ "$l" -le 60 ] || W "CLAUDE.md $l baris, mendekati batas"

echo "---"; echo "Ringkasan: $fail FAIL, $warn WARN"
[ "$fail" -eq 0 ]
