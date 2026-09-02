from __future__ import annotations

import argparse
import io
import math
import json
from pathlib import Path

from reportlab.graphics.barcode import qr
from reportlab.lib import colors
from reportlab.lib.pagesizes import landscape
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas
from reportlab.graphics.shapes import Drawing, Line
from reportlab.graphics import renderPDF
from reportlab.lib.utils import ImageReader


CARD_W = 85.60 * mm
CARD_H = 53.98 * mm
MARGIN = 3.0 * mm


def register_fonts() -> None:
    candidates = [
        r"C:\Windows\Fonts\prompt.ttf",
        r"C:\Windows\Fonts\Sarabun-Regular.ttf",
        r"C:\Windows\Fonts\arial.ttf",
    ]
    for font_name, path in [("Prompt", candidates[0]), ("Sarabun", candidates[1]), ("Arial", candidates[2])]:
        try:
            pdfmetrics.registerFont(TTFont(font_name, path))
        except Exception:
            pass


def get_font(primary: str, fallback: str = "Helvetica") -> str:
    try:
        pdfmetrics.getFont(primary)
        return primary
    except Exception:
        return fallback


def make_guilloche(width: float, height: float) -> Drawing:
    d = Drawing(width, height)
    blue = colors.HexColor("#1f5fa8")
    light = colors.HexColor("#8db9e8")
    cx = width / 2.0
    cy = height / 2.0
    steps = 28
    for i in range(steps):
        r1 = min(width, height) * (0.10 + i * 0.016)
        r2 = r1 + 6
        d.add(Line(cx - r1, cy, cx + r2, cy + r2 * 0.08, strokeColor=light, strokeWidth=0.35, strokeOpacity=0.35))
        d.add(Line(cx - r2, cy + r2 * 0.08, cx + r1, cy, strokeColor=blue, strokeWidth=0.25, strokeOpacity=0.18))
    for i in range(0, int(width // 4) + 1):
        y = i * 4 * mm
        d.add(Line(0, y, width, y + 1.8 * mm, strokeColor=blue, strokeWidth=0.18, strokeOpacity=0.10))
        d.add(Line(0, y + 1.8 * mm, width, y, strokeColor=light, strokeWidth=0.18, strokeOpacity=0.08))
    return d


def card_qr_data(host_url: str, house_id: int) -> str:
    return f"{host_url.rstrip('/')}/house_detail.php?id={house_id}"


def draw_card(c: canvas.Canvas, house: dict[str, object], host_url: str) -> None:
    c.setFillColor(colors.white)
    c.rect(0, 0, CARD_W, CARD_H, stroke=0, fill=1)
    renderPDF.draw(make_guilloche(CARD_W, CARD_H), c, 0, 0)

    border = colors.HexColor("#1f5fa8")
    c.setStrokeColor(border)
    c.setLineWidth(0.8)
    c.roundRect(1.6 * mm, 1.6 * mm, CARD_W - 3.2 * mm, CARD_H - 3.2 * mm, 3.0 * mm, stroke=1, fill=0)

    prompt = get_font("Prompt")
    sarabun = get_font("Sarabun", "Helvetica")

    house_no = str(house["house_no"])
    owner_name = str(house["owner_name"])
    soi_name = str(house["soi_name"])
    member_count = str(house["member_count"])
    house_id = int(house["id"])

    qr_size = 18 * mm
    qr_data = card_qr_data(host_url, house_id)
    qr_code = qr.QrCodeWidget(qr_data)
    bounds = qr_code.getBounds()
    w = bounds[2] - bounds[0]
    h = bounds[3] - bounds[1]
    d_qr = Drawing(qr_size, qr_size, transform=[qr_size / w, 0, 0, qr_size / h, 0, 0])
    d_qr.add(qr_code)
    renderPDF.draw(d_qr, c, CARD_W - qr_size - 4.2 * mm, CARD_H - qr_size - 5.0 * mm)

    c.setFillColor(border)
    c.setFont(prompt, 11)
    c.drawString(5.0 * mm, CARD_H - 8.0 * mm, "บัตรประจำบ้าน")
    c.setFont(sarabun, 6.8)
    c.setFillColor(colors.black)
    c.drawString(5.0 * mm, CARD_H - 12.2 * mm, "Community Waste Management")

    c.setLineWidth(0.4)
    c.setStrokeColor(colors.HexColor("#c6d7eb"))
    c.line(5.0 * mm, CARD_H - 14.0 * mm, CARD_W - 5.0 * mm, CARD_H - 14.0 * mm)

    label_font = sarabun
    value_font = prompt
    y0 = CARD_H - 20.0 * mm
    gap = 6.7 * mm
    items = [
        ("เลขที่บ้าน", house_no),
        ("เจ้าของบ้าน", owner_name),
        ("ซอย", soi_name),
        ("สมาชิก (คน)", member_count),
    ]
    for idx, (label, value) in enumerate(items):
        y = y0 - idx * gap
        c.setFont(label_font, 6.6)
        c.setFillColor(colors.HexColor("#425466"))
        c.drawString(5.0 * mm, y, f"{label}:")
        c.setFont(value_font, 8.2)
        c.setFillColor(colors.black)
        c.drawString(20.0 * mm, y, value)

    c.setFont(sarabun, 5.8)
    c.setFillColor(colors.HexColor("#4b5563"))
    c.drawString(5.0 * mm, 4.6 * mm, f"QR: {qr_data}")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--host-url", required=True)
    parser.add_argument("--input-json", required=True)
    parser.add_argument("--output", required=True)
    args = parser.parse_args()

    register_fonts()

    houses = json.loads(Path(args.input_json).read_text(encoding="utf-8"))
    if not houses:
        raise SystemExit("No house data provided")

    out_path = Path(args.output)
    out_path.parent.mkdir(parents=True, exist_ok=True)

    c = canvas.Canvas(str(out_path), pagesize=landscape((CARD_W, CARD_H)))
    c.setTitle("House Cards")
    for i, house in enumerate(houses):
        draw_card(c, house, args.host_url)
        if i < len(houses) - 1:
            c.showPage()
    c.save()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
