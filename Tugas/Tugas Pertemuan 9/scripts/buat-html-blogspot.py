#!/usr/bin/env python3
"""
Mengubah docs/Laporan-Blogspot.md menjadi HTML sederhana yang siap ditempel ke Blogger.

Aturan HTML yang dihasilkan:
- Tanpa CSS eksternal, tanpa <style>, dan tanpa <script>. Semua gaya ditulis inline.
- Hanya karakter ASCII (simbol seperti em dash dan panah diubah menjadi entitas HTML),
  sehingga teks tetap benar walaupun file dibuka atau ditempel di mana saja.
- Setiap blok ditulis dalam satu baris. Baris kode di dalam <pre> memakai <br>,
  jadi tampilan tidak rusak bila Blogger mengubah baris baru menjadi <br>.
- Gambar (docs/images/...) dan screenshot ditandai kotak kuning agar diunggah lewat
  editor Blogger. Bila --base-url diisi dan file gambarnya ada, gambar langsung ditautkan.
- Penanda seperti [SCREENSHOT: ...], [ISI SETELAH DIUJI], dan [LINK ...] disorot kuning.

Pemakaian (dari folder "Tugas Pertemuan 9"):
    python3 scripts/buat-html-blogspot.py
    python3 scripts/buat-html-blogspot.py --base-url https://raw.githubusercontent.com/fazdy/PBKK---C/main/Tugas/Tugas%20Pertemuan%209/docs

Hanya memakai pustaka standar Python 3.8+.
"""

import argparse
import html
import pathlib
import re
import sys
import urllib.parse

ROOT = pathlib.Path(__file__).resolve().parent.parent
DOCS = ROOT / "docs"

MONO = "Menlo,Consolas,'Courier New',monospace"
GAYA = {
    "h2": "margin:36px 0 12px;padding-bottom:6px;border-bottom:2px solid #16a34a;font-size:1.5em;line-height:1.3",
    "h3": "margin:26px 0 10px;font-size:1.22em;line-height:1.35",
    "h4": "margin:20px 0 8px;font-size:1.06em;line-height:1.4",
    "p": "margin:0 0 14px;line-height:1.7",
    "list": "margin:0 0 14px;padding-left:26px;line-height:1.7",
    "list_cek": "margin:0 0 14px;padding-left:6px;list-style:none;line-height:1.7",
    "li": "margin:3px 0",
    "wrap_table": "overflow-x:auto;margin:0 0 16px",
    "table": "border-collapse:collapse;width:100%;font-size:14px;line-height:1.5",
    "th": "border:1px solid #cbd5e1;padding:7px 9px;background:#f1f5f9;color:#0f172a;vertical-align:top",
    "td": "border:1px solid #cbd5e1;padding:7px 9px;vertical-align:top",
    "pre": ("margin:0 0 16px;padding:14px 16px;background:#0f172a;color:#e2e8f0;border-radius:8px;"
            f"overflow-x:auto;font-size:13px;line-height:1.55;white-space:pre;font-family:{MONO}"),
    "pre_code": "font-family:inherit;font-size:inherit;background:none;color:inherit;padding:0",
    "code": f"background:#eef2f7;color:#0f172a;padding:1px 5px;border-radius:4px;font-family:{MONO};font-size:0.9em",
    "quote": ("margin:0 0 16px;padding:12px 16px;border-left:4px solid #16a34a;background:#f0fdf4;"
              "color:#14532d;border-radius:0 8px 8px 0;line-height:1.7"),
    "mark": "background:#fef08a;color:#713f12;padding:1px 4px;border-radius:3px",
    "penanda": ("margin:0 0 16px;padding:12px 14px;background:#fefce8;color:#713f12;border:1px dashed #ca8a04;"
                "border-radius:8px;font-size:14px;line-height:1.6"),
    "hr": "border:none;border-top:1px solid #e2e8f0;margin:30px 0",
    "figure": "margin:0 0 18px;text-align:center",
    "img": "max-width:100%;height:auto;border:1px solid #e2e8f0;border-radius:8px",
    "caption": "font-size:13px;color:#64748b;margin-top:6px;line-height:1.5",
}

POLA_ITEM = re.compile(r"^(\s*)([-*+]|\d+\.)\s+(.*)$")
POLA_JUDUL = re.compile(r"^(#{1,6})\s+(.*)$")
POLA_GARIS = re.compile(r"^(-{3,}|\*{3,}|_{3,})$")
POLA_PEMISAH_TABEL = re.compile(r"^\|?\s*:?-{3,}")
POLA_GAMBAR = re.compile(r"^!\[([^\]]*)\]\(([^)\s]+)\)$")
POLA_PENANDA_BLOK = re.compile(r"^\[(SCREENSHOT|GAMBAR):.*\]$")
POLA_PENANDA = re.compile(r"\[(?:SCREENSHOT:|GAMBAR:|ISI |LINK )[^\]]*\]")
POLA_KODE = re.compile(r"`([^`]+)`")
POLA_TAUTAN = re.compile(r"\[([^\]]+)\]\(([^)\s]+)\)")
POLA_ID = re.compile(r"\b(?:TC|FR|NFR|AC|US)-\d{2}(?:\.\d+)?(?:–\d{2}(?:\.\d+)?)?")
POLA_TEBAL = re.compile(r"\*\*(.+?)\*\*")
POLA_MIRING = re.compile(r"(?<![\w*])\*(?![\s*])(.+?)(?<![\s*])\*(?![\w*])")


def esc(teks):
    return html.escape(teks, quote=True)


def kode_inline(isi):
    return f'<code style="{GAYA["code"]}">{html.escape(isi, quote=False)}</code>'


def inline(teks):
    """Format inline: `kode`, [tautan](url), penanda, **tebal**, dan *miring*."""
    kode = []

    def simpan_kode(m):
        kode.append(m.group(1))
        return f"\x00{len(kode) - 1}\x00"

    teks = POLA_KODE.sub(simpan_kode, teks)
    teks = html.escape(teks, quote=False)

    def tautan(m):
        label, url = m.group(1), m.group(2)
        if url.startswith("#"):
            return f'<a href="{esc(url)}">{label}</a>'
        if url.startswith(("http://", "https://")):
            return f'<a href="{esc(url)}" target="_blank" rel="noopener">{label}</a>'
        return label  # tautan relatif ke file repository tidak berlaku di Blogger

    teks = POLA_TAUTAN.sub(tautan, teks)
    teks = POLA_PENANDA.sub(lambda m: f'<mark style="{GAYA["mark"]}">{m.group(0)}</mark>', teks)
    # Kode seperti TC-01 atau AC-01.1 tidak boleh terpenggal di tanda hubung pada tabel sempit.
    teks = POLA_ID.sub(lambda m: f'<span style="white-space:nowrap">{m.group(0)}</span>', teks)
    teks = POLA_TEBAL.sub(r"<strong>\1</strong>", teks)
    teks = POLA_MIRING.sub(r"<em>\1</em>", teks)
    return re.sub("\x00(\\d+)\x00", lambda m: kode_inline(kode[int(m.group(1))]), teks)


class Slug:
    """Membuat id judul dengan aturan yang sama seperti GitHub, agar daftar isi berfungsi di keduanya."""

    def __init__(self):
        self.terpakai = {}

    def buat(self, teks):
        dasar = re.sub(r"[`*]", "", teks).strip().lower()
        dasar = re.sub(r"[^\w\- ]", "", dasar).replace(" ", "-")
        jumlah = self.terpakai.get(dasar, 0)
        self.terpakai[dasar] = jumlah + 1
        return dasar if jumlah == 0 else f"{dasar}-{jumlah}"


def awal_blok(baris, i):
    s = baris[i].strip()
    return bool(
        s.startswith("```")
        or POLA_JUDUL.match(s)
        or s.startswith(">")
        or POLA_ITEM.match(s)
        or POLA_GARIS.match(s)
        or POLA_GAMBAR.match(s)
        or POLA_PENANDA_BLOK.match(s)
        or (s.startswith("|") and i + 1 < len(baris) and POLA_PEMISAH_TABEL.match(baris[i + 1].strip()))
    )


def pecah_baris_tabel(baris):
    kode = []

    def simpan(m):
        kode.append(m.group(0))
        return f"\x01{len(kode) - 1}\x01"

    baris = re.sub(r"`[^`]*`", simpan, baris.strip())
    if baris.startswith("|"):
        baris = baris[1:]
    if baris.endswith("|"):
        baris = baris[:-1]
    sel = re.split(r"(?<!\\)\|", baris)
    return [re.sub("\x01(\\d+)\x01", lambda m: kode[int(m.group(1))], s).replace("\\|", "|").strip() for s in sel]


def render_tabel(baris):
    kepala = pecah_baris_tabel(baris[0])
    rata = []
    for s in pecah_baris_tabel(baris[1]):
        if s.startswith(":") and s.endswith(":"):
            rata.append("center")
        elif s.endswith(":"):
            rata.append("right")
        else:
            rata.append("left")

    def sel(tag, isi, kolom):
        gaya = GAYA[tag] + f";text-align:{rata[kolom] if kolom < len(rata) else 'left'}"
        return f'<{tag} style="{gaya}">{inline(isi)}</{tag}>'

    th = "".join(sel("th", isi, k) for k, isi in enumerate(kepala))
    tr = "".join(
        "<tr>" + "".join(sel("td", isi, k) for k, isi in enumerate(pecah_baris_tabel(b))) + "</tr>"
        for b in baris[2:]
    )
    return (f'<div style="{GAYA["wrap_table"]}"><table style="{GAYA["table"]}">'
            f"<thead><tr>{th}</tr></thead><tbody>{tr}</tbody></table></div>")


def render_daftar(baris):
    item = []
    for b in baris:
        m = POLA_ITEM.match(b)
        if m:
            item.append([len(m.group(1).expandtabs(4)), m.group(2), m.group(3).strip()])
        elif item:
            item[-1][2] += " " + b.strip()

    hasil = []
    tumpukan = []
    for indent, penanda, teks in item:
        tag = "ol" if penanda.endswith(".") else "ul"
        if not tumpukan or indent > tumpukan[-1][0]:
            cek = all(re.match(r"^\[[ xX]\] ", t) for i2, _, t in item if i2 == indent)
            gaya = GAYA["list_cek"] if cek else GAYA["list"]
            if tumpukan:  # sub-daftar: tanpa jarak bawah agar butir berikutnya tidak renggang
                gaya = gaya.replace("margin:0 0 14px", "margin:4px 0 0")
            mulai = ""
            if tag == "ol" and penanda[:-1] != "1":
                mulai = f' start="{penanda[:-1]}"'
            hasil.append(f'<{tag}{mulai} style="{gaya}">')
            tumpukan.append((indent, tag))
        else:
            while tumpukan and indent < tumpukan[-1][0]:
                hasil.append(f"</li></{tumpukan.pop()[1]}>")
            hasil.append("</li>")
        if re.match(r"^\[ \] ", teks):
            teks = "☐ " + teks[4:]  # kotak centang kosong
        elif re.match(r"^\[[xX]\] ", teks):
            teks = "☑ " + teks[4:]  # kotak centang terisi
        hasil.append(f'<li style="{GAYA["li"]}">{inline(teks)}')
    while tumpukan:
        hasil.append(f"</li></{tumpukan.pop()[1]}>")
    return "".join(hasil)


def render_kode(baris):
    isi = html.escape("\n".join(baris), quote=False).replace("\n", "<br>")
    return f'<pre style="{GAYA["pre"]}"><code style="{GAYA["pre_code"]}">{isi}</code></pre>'


def render_figur(url, keterangan):
    return (f'<div style="{GAYA["figure"]}"><img src="{esc(url)}" alt="{esc(keterangan)}" style="{GAYA["img"]}">'
            f'<div style="{GAYA["caption"]}">{inline(keterangan)}</div></div>')


def render_gambar(alt, src, base_url):
    if base_url and (DOCS / src).exists():
        return render_figur(f"{base_url.rstrip('/')}/{urllib.parse.quote(src)}", alt)
    penanda = f"[GAMBAR: docs/{src} — {alt}]"
    return (f'<p style="{GAYA["penanda"]}"><strong>{html.escape(penanda, quote=False)}</strong><br>'
            "Unggah gambar ini lewat ikon <em>Insert image</em> di editor Blogger, lalu hapus kotak ini.</p>")


def render_penanda_blok(teks, base_url):
    m = re.search(r"docs/(screenshots/[\w.\-]+\.(?:png|jpg|jpeg))", teks)
    if base_url and m and (DOCS / m.group(1)).exists():
        keterangan = re.sub(r"^\[SCREENSHOT:\s*", "", teks)
        keterangan = re.sub(r"\s*Simpan sebagai .*\]$", "", keterangan).rstrip("]").rstrip(".")
        return render_figur(f"{base_url.rstrip('/')}/{urllib.parse.quote(m.group(1))}", keterangan)
    return (f'<p style="{GAYA["penanda"]}"><strong>{html.escape(teks, quote=False)}</strong><br>'
            "Ganti kotak ini dengan tangkapan layar yang sesuai.</p>")


def ubah(md, base_url):
    baris = md.split("\n")
    blok = []
    judul = None
    slug = Slug()
    i = 0
    while i < len(baris):
        b = baris[i]
        s = b.strip()
        if not s:
            i += 1
            continue

        if s.startswith("```"):
            i += 1
            isi = []
            while i < len(baris) and not baris[i].strip().startswith("```"):
                isi.append(baris[i])
                i += 1
            i += 1
            blok.append(render_kode(isi))
            continue

        m = POLA_JUDUL.match(s)
        if m:
            level, teks = len(m.group(1)), m.group(2).strip()
            if level == 1 and judul is None:
                judul = teks  # judul postingan diisi di kolom "Judul" Blogger
            else:
                tag = f"h{min(max(level, 2), 4)}"
                blok.append(f'<{tag} id="{slug.buat(teks)}" style="{GAYA[tag]}">{inline(teks)}</{tag}>')
            i += 1
            continue

        if POLA_GARIS.match(s):
            blok.append(f'<hr style="{GAYA["hr"]}">')
            i += 1
            continue

        if s.startswith("|") and i + 1 < len(baris) and POLA_PEMISAH_TABEL.match(baris[i + 1].strip()):
            isi = []
            while i < len(baris) and baris[i].strip().startswith("|"):
                isi.append(baris[i])
                i += 1
            blok.append(render_tabel(isi))
            continue

        if s.startswith(">"):
            isi = []
            while i < len(baris) and baris[i].strip().startswith(">"):
                isi.append(re.sub(r"^\s*>\s?", "", baris[i]).strip())
                i += 1
            blok.append(f'<div style="{GAYA["quote"]}">{inline(" ".join(isi))}</div>')
            continue

        if POLA_ITEM.match(b):
            isi = []
            while i < len(baris):
                if POLA_ITEM.match(baris[i]):
                    isi.append(baris[i])
                elif baris[i].strip() and baris[i].startswith((" ", "\t")):
                    isi.append(baris[i])
                else:
                    break
                i += 1
            blok.append(render_daftar(isi))
            continue

        m = POLA_GAMBAR.match(s)
        if m:
            blok.append(render_gambar(m.group(1), m.group(2), base_url))
            i += 1
            continue

        if POLA_PENANDA_BLOK.match(s):
            blok.append(render_penanda_blok(s, base_url))
            i += 1
            continue

        # Paragraf: baris berurutan digabung dengan spasi, kecuali baris yang diakhiri "\" atau
        # dua spasi (pemisah baris eksplisit Markdown) yang diganti <br>.
        segmen = [[]]
        while i < len(baris) and baris[i].strip() and not (segmen != [[]] and awal_blok(baris, i)):
            mentah = baris[i]
            teks = mentah.strip()
            keras = teks.endswith("\\") or mentah.endswith("  ")
            segmen[-1].append(teks[:-1].rstrip() if teks.endswith("\\") else teks)
            if keras:
                segmen.append([])
            i += 1
        isi = "<br>".join(inline(" ".join(s)) for s in segmen if s)
        blok.append(f'<p style="{GAYA["p"]}">{isi}</p>')

    return judul, blok


def main():
    parser = argparse.ArgumentParser(description="Membuat HTML Blogger dari docs/Laporan-Blogspot.md")
    parser.add_argument("--input", default=str(DOCS / "Laporan-Blogspot.md"))
    parser.add_argument("--output", default=str(DOCS / "Laporan-Blogspot.html"))
    parser.add_argument("--base-url", default=None,
                        help="URL folder docs/ yang sudah online (mis. raw.githubusercontent.com/...). "
                             "Gambar yang file-nya ada akan ditautkan langsung.")
    args = parser.parse_args()

    md = pathlib.Path(args.input).read_text(encoding="utf-8")
    judul, blok = ubah(md, args.base_url)

    kepala = [
        "<!-- Dibuat otomatis oleh scripts/buat-html-blogspot.py dari docs/Laporan-Blogspot.md. -->",
        f"<!-- Judul postingan (isi di kolom Judul Blogger): {judul or '-'} -->",
    ]
    hasil = "\n".join(kepala + blok) + "\n"
    hasil = hasil.encode("ascii", "xmlcharrefreplace").decode("ascii")
    keluaran = pathlib.Path(args.output)
    keluaran.write_text(hasil, encoding="utf-8")

    sisa = [
        ("[ISI SETELAH DIUJI]", md.count("[ISI SETELAH DIUJI]")),
        ("[ISI TANGGAL]", md.count("[ISI TANGGAL]")),
        ("[LINK ...]", len(re.findall(r"\[LINK [^\]]*\]", md))),
        ("Kotak gambar", hasil.count("[GAMBAR: ")),
        ("Kotak screenshot", hasil.count("[SCREENSHOT: ")),
    ]
    print(f"Selesai: {keluaran} ({len(hasil) / 1024:.0f} KB, {len(blok)} blok)")
    print(f"Judul postingan: {judul}")
    print("Penanda yang masih perlu dilengkapi:")
    for nama, jumlah in sisa:
        print(f"  {nama:<20}: {jumlah}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
