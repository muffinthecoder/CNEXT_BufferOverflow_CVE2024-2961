## This code creates the ssh file used during mitigation:

nano /root/fix2961.sh


#!/bin/bash
# fix2961.sh — Harden glibc against CVE-2024-2961

echo "[*] Searching for vulnerable encodings in iconv..."
iconv -l | grep -Ei 'CN[-_]?EXT|ISO[-_]?2022[-_]?CN[-_]?EXT'

echo "[*] Searching for gconv config files..."
CONFIG_FILES=$(find /usr/lib* -type f -name 'gconv-modules*' 2>/dev/null)

if [ -z "$CONFIG_FILES" ]; then
    echo "[!] No gconv config files found. Cannot proceed."
    exit 1
fi

for FILE in $CONFIG_FILES; do
    echo "[*] Patching $FILE"
    cp "$FILE" "$FILE.bak"
    sed -i '/CN[-_]\?EXT/d' "$FILE"
    sed -i '/ISO[-_]\?2022[-_]\?CN[-_]\?EXT/d' "$FILE"
done

echo "[+] Patch complete. Restarting container to flush glibc cache is recommended."

