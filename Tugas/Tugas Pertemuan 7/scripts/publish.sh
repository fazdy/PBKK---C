#!/usr/bin/env bash
# =====================================================================
# SIBASA - membuat paket rilis siap unggah (Google Drive)
#   release/SIBASA-<versi>-Windows-x64.zip  -> berisi SIBASA.exe (tanpa instal .NET)
#   release/SIBASA-<versi>-macOS-Intel.zip  -> berisi SIBASA.app untuk Mac Intel
#
# Cara pakai (dari folder "Tugas Pertemuan 7"):
#   ./scripts/publish.sh
# Bila perintah dotnet di PATH belum memuat SDK 10, tunjuk lokasinya:
#   DOTNET=/usr/local/share/dotnet/dotnet ./scripts/publish.sh
# =====================================================================
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="$ROOT/src/SIBASA/SIBASA.csproj"
VERSION="1.0.0"
DOTNET="${DOTNET:-dotnet}"
PUBLISH_DIR="$ROOT/publish"
RELEASE_DIR="$ROOT/release"

rm -rf "$PUBLISH_DIR" "$RELEASE_DIR"
mkdir -p "$PUBLISH_DIR" "$RELEASE_DIR"

echo "==> [1/2] Windows x64 (single-file, self-contained)"
"$DOTNET" publish "$PROJECT" -c Release -r win-x64 --self-contained true \
  -p:PublishSingleFile=true \
  -p:IncludeNativeLibrariesForSelfExtract=true \
  -p:DebugType=None -p:DebugSymbols=false \
  -o "$PUBLISH_DIR/win-x64"
cp "$ROOT/scripts/BACA-SAYA.txt" "$PUBLISH_DIR/win-x64/BACA-SAYA.txt"
(cd "$PUBLISH_DIR/win-x64" && zip -qr "$RELEASE_DIR/SIBASA-$VERSION-Windows-x64.zip" .)

echo "==> [2/2] macOS Intel x64 (.app, self-contained)"
"$DOTNET" publish "$PROJECT" -c Release -r osx-x64 --self-contained true \
  -p:UseAppHost=true \
  -p:DebugType=None -p:DebugSymbols=false \
  -o "$PUBLISH_DIR/osx-x64"

# Susun bundle .app: Contents/MacOS (program), Contents/Resources (ikon), Contents/Info.plist (metadata).
MAC_DIR="$PUBLISH_DIR/SIBASA-macOS"
APP="$MAC_DIR/SIBASA.app"
mkdir -p "$APP/Contents/MacOS" "$APP/Contents/Resources"
cp -R "$PUBLISH_DIR/osx-x64/." "$APP/Contents/MacOS/"
cp "$ROOT/src/SIBASA/Packaging/macOS/Info.plist" "$APP/Contents/Info.plist"
cp "$ROOT/src/SIBASA/Packaging/macOS/sibasa.icns" "$APP/Contents/Resources/sibasa.icns"
chmod +x "$APP/Contents/MacOS/SIBASA"

# Tanda tangan ad-hoc agar macOS mengenali bundle sebagai satu aplikasi utuh.
# (Bukan sertifikat Apple Developer, jadi Gatekeeper tetap meminta "Open Anyway" sekali.)
codesign --force --deep --sign - "$APP" >/dev/null 2>&1 || echo "    (codesign ad-hoc dilewati)"

cp "$ROOT/scripts/BACA-SAYA.txt" "$MAC_DIR/BACA-SAYA.txt"
(cd "$PUBLISH_DIR" && ditto -c -k --keepParent "SIBASA-macOS" "$RELEASE_DIR/SIBASA-$VERSION-macOS-Intel.zip")

echo ""
echo "Selesai. File siap diunggah ke Google Drive:"
ls -lh "$RELEASE_DIR"
