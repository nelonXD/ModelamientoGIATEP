from __future__ import annotations

import argparse
import hashlib
import json
import re
import unicodedata
from datetime import UTC, datetime
from pathlib import Path

from pypdf import PdfReader, __version__ as pypdf_version


ROOT = Path(__file__).resolve().parents[2]
DEFAULT_SOURCE = ROOT / "Documentacion GIATEP" / "D057-PR-500-02-001_Guia_Investigacion_Accidentes_v1_(2).pdf"
DEFAULT_OUTPUT = Path(__file__).resolve().parents[1] / "database" / "data" / "referencias" / "guia_mac_isp_v1.json"

HEADER_LINES = {
    "departamento salud ocupacional.",
    "instituto de salud publica de chile.",
    "guia para la investigacion de accidentes.",
    "metodologia arbol de causas",
}
HEADING = re.compile(r"^(?:\d+(?:\.\d+)*\.?\s+[A-ZÁÉÍÓÚÜÑ]|ANEXO\s+N[º°o]?\s*\d+|[A-ZÁÉÍÓÚÜÑ][A-ZÁÉÍÓÚÜÑ /()¿?.,:-]{8,})")


def clean_page(text: str) -> str:
    # En este PDF pypdf expone como U+FFFD ciertos bytes Windows-1252 para
    # vocales acentuadas. Recuperarlos antes de limpiar; el marcador de viñeta
    # permanece como U+FFFD y se convierte a un separador legible.
    if "\ufffd" in text:
        # pypdf pierde el byte acentuado en ciertas fuentes del PDF.
        # Recuperar vocabulario metodológico frecuente y limpiar viñetas restantes.
        for broken, fixed in {
            "P￺bl": "Público", "investigación": "investigación",
            "información": "información", "metodología": "metodología",
            "árbol": "árbol", "lesión": "lesión", "relación": "relación",
            "conjunción": "conjunción", "disyunción": "disyunción",
            "último": "último", "última": "última", "seg￺n": "según",
            "a￱os": "años", "da￱os": "daños", "se￱ala": "señala",
            "·": " · ",
        }.items():
            text = text.replace(broken, fixed)
    text = text.replace("\u00ad", "")
    text = re.sub(r"(?<=\w)-\s*\n\s*(?=\w)", "", text)
    lines = []
    for raw_line in text.splitlines():
        line = re.sub(r"\s+", " ", raw_line).strip()
        if not line or line.isdigit():
            continue
        normalized = unicodedata.normalize("NFKD", line)
        normalized = "".join(char for char in normalized if not unicodedata.combining(char)).casefold()
        if normalized in HEADER_LINES:
            continue
        lines.append(line)
    return " ".join(lines).strip()


def extract(source: Path, output: Path) -> None:
    reader = PdfReader(str(source))
    if reader.is_encrypted:
        raise SystemExit("El PDF está cifrado y no se pudo extraer.")

    pages = []
    active_section = "Portada y antecedentes"
    for number, page in enumerate(reader.pages, start=1):
        raw = page.extract_text() or ""
        text = clean_page(raw)
        headings = [line.strip() for line in raw.splitlines() if HEADING.match(line.strip())]
        headings = list(dict.fromkeys(headings))
        if headings:
            active_section = headings[-1]
        pages.append({
            "page": number,
            "section": active_section,
            "headings": headings,
            "text": text,
            "text_chars": len(text),
            "needs_visual_review": len(text) < 500,
        })

    payload = {
        "reference_id": "ISP-D057-PR-500-02-001-v1",
        "title": "Guía para la investigación de accidentes: Metodología Árbol de Causas",
        "publisher": "Instituto de Salud Pública de Chile · Departamento Salud Ocupacional",
        "version": 1,
        "resolution": "Resolución Exenta N.º 151, 23-01-2020",
        "document_code": "D057-PR-500-02-001",
        "source_file": source.name,
        "source_sha256": hashlib.sha256(source.read_bytes()).hexdigest(),
        "page_count": len(reader.pages),
        "extracted_at_utc": datetime.now(UTC).isoformat(timespec="seconds"),
        "extractor": f"pypdf {pypdf_version}",
        "limitations": "Extracción textual por página. Diagramas, simbología e imágenes pueden no quedar íntegramente representados; revisar el PDF original en las páginas marcadas para revisión visual.",
        "pages": pages,
    }
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Extraídas {len(pages)} páginas a {output}")
    print(f"Páginas con poco texto para revisar visualmente: {[page['page'] for page in pages if page['needs_visual_review']]}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Extrae por página la guía MAC del ISP para la referencia local de la IA.")
    parser.add_argument("--source", type=Path, default=DEFAULT_SOURCE)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()
    extract(args.source, args.output)
