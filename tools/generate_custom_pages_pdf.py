#!/usr/bin/env python3
"""Generate PDF listing all custom pages created in this chat with URLs."""
from fpdf import FPDF

BASE = "https://saiflower.com"
OUT = r"C:\Users\Nishant Singh\Desktop\saiflower\SaiFlower_Custom_Pages_URLs.pdf"

pages = [
    (1, "Flower Delivery", "flower-delivery"),
    (2, "Online Flower Delivery", "online-flower-delivery"),
    (3, "Same Day Flower Delivery", "same-day-flower-delivery"),
    (4, "Midnight Flower Delivery", "midnight-flower-delivery"),
    (5, "Express Flower Delivery", "express-flower-delivery"),
    (6, "Best Florist Near Me", "best-florist-near-me"),
    (7, "Online Florist", "online-florist"),
    (8, "Fresh Flower Delivery", "fresh-flower-delivery"),
    (9, "Flower Shop Online", "flower-shop-online"),
    (10, "Bouquet Delivery", "bouquet-delivery"),
    (11, "Rose Bouquet Delivery", "rose-bouquet-delivery"),
    (12, "Premium Flower Bouquet", "premium-flower-bouquet"),
    (13, "Luxury Flower Bouquet", "luxury-flower-bouquet"),
    (14, "Anniversary Flowers", "anniversary-flowers"),
    (15, "Birthday Flower Delivery", "birthday-flower-delivery"),
    (16, "Romantic Flower Bouquet", "romantic-flower-bouquet"),
    (17, "Wedding Flower Bouquet", "wedding-flower-bouquet"),
    (18, "Congratulations Flowers", "congratulations-flowers"),
    (19, "Get Well Soon Flowers", "get-well-soon-flowers"),
    (20, "Sympathy Flowers", "sympathy-flowers"),
    (21, "Thank You Flowers", "thank-you-flowers"),
    (22, "Love Flowers", "love-flowers"),
    (23, "Red Rose Bouquet", "red-rose-bouquet"),
    (24, "Mixed Flower Bouquet", "mixed-flower-bouquet"),
    (25, "Orchid Bouquet", "orchid-bouquet"),
    (26, "Lily Bouquet", "lily-bouquet"),
    (27, "Carnation Bouquet", "carnation-bouquet"),
    (28, "Gerbera Bouquet", "gerbera-bouquet"),
    (29, "Sunflower Bouquet", "sunflower-bouquet"),
    (30, "Exotic Flower Bouquet", "exotic-flower-bouquet"),
    (31, "Fresh Roses Online", "fresh-roses-online"),
    (32, "Buy Flowers Online", "buy-flowers-online"),
    (33, "Order Flowers Online", "order-flowers-online"),
    (34, "Send Flowers Online", "send-flowers-online"),
    (35, "Send Bouquet Online", "send-bouquet-online"),
    (36, "Flower Delivery Delhi", "flower-delivery-delhi"),
    (37, "Florist in Delhi", "florist-in-delhi"),
    (38, "Delhi Flower Shop", "delhi-flower-shop"),
    (39, "Best Flower Shop Delhi", "best-flower-shop-delhi"),
    (40, "Flower Bouquet Delhi", "flower-bouquet-delhi"),
    (41, "Premium Florist Delhi", "premium-florist-delhi"),
    (42, "Affordable Flower Delivery", "affordable-flower-delivery"),
    (43, "Flower Delivery with Cake", "flower-delivery-with-cake"),
    (44, "Cake and Flower Delivery", "cake-and-flower-delivery"),
    (45, "Flower and Chocolate Delivery", "flower-and-chocolate-delivery"),
    (46, "Bouquet with Teddy", "bouquet-with-teddy"),
    (47, "Gift Combo Delivery", "gift-combo-delivery"),
    (48, "Same Day Bouquet Delivery", "same-day-bouquet-delivery"),
    (49, "Midnight Bouquet Delivery", "midnight-bouquet-delivery"),
    (50, "Surprise Flower Delivery", "surprise-flower-delivery"),
    (51, "Luxury Rose Bouquet", "luxury-rose-bouquet"),
    (52, "Premium Rose Box", "premium-rose-box"),
    (53, "Floral Gift Delivery", "floral-gift-delivery"),
    (54, "Hand Tied Bouquet", "hand-tied-bouquet"),
    (55, "Personalized Flower Bouquet", "personalized-flower-bouquet"),
    (56, "Valentine's Day Flowers", "valentines-day-flowers"),
    (57, "Mother's Day Flowers", "mothers-day-flowers"),
    (58, "Women's Day Flowers", "womens-day-flowers"),
    (59, "Raksha Bandhan Flowers", "raksha-bandhan-flowers"),
    (60, "Festival Flower Delivery", "festival-flower-delivery"),
    (61, "Flower Delivery Greater Kailash", "flower-delivery-greater-kailash"),
    (62, "Flower Delivery GK", "flower-delivery-gk"),
    (63, "Florist in Greater Kailash", "florist-in-greater-kailash"),
    (64, "Florist in GK", "florist-in-gk"),
    (65, "Online Flower Delivery Greater Kailash", "online-flower-delivery-greater-kailash"),
    (66, "Same Day Flower Delivery Greater Kailash", "same-day-flower-delivery-greater-kailash"),
    (67, "Midnight Flower Delivery Greater Kailash", "midnight-flower-delivery-greater-kailash"),
    (68, "Flower Shop Greater Kailash", "flower-shop-greater-kailash"),
    (69, "Bouquet Delivery Greater Kailash", "bouquet-delivery-greater-kailash"),
    (70, "Best Florist in Greater Kailash", "best-florist-in-greater-kailash"),
]

batches = {
    1: "Batch 1 - Core Delivery & Florist",
    11: "Batch 2 - Occasions & Emotions",
    21: "Batch 3 - Bouquet Types",
    31: "Batch 4 - Online & Delhi",
    41: "Batch 5 - Combos & Express",
    51: "Batch 6 - Luxury & Festivals",
    61: "Batch 7 - Greater Kailash / GK",
}


class PDF(FPDF):
    def header(self):
        self.set_font("Helvetica", "B", 10)
        self.set_text_color(47, 111, 78)
        self.cell(
            0,
            8,
            "Sai Flower - Custom Pages Created in This Chat",
            align="C",
            new_x="LMARGIN",
            new_y="NEXT",
        )
        self.set_draw_color(212, 175, 55)
        self.set_line_width(0.4)
        self.line(10, self.get_y(), 200, self.get_y())
        self.ln(4)

    def footer(self):
        self.set_y(-15)
        self.set_font("Helvetica", "I", 8)
        self.set_text_color(120, 120, 120)
        self.cell(
            0,
            10,
            f"Page {self.page_no()}/{{nb}}  |  Total: 70 custom pages  |  Layout: product_showcase  |  Tag: sameday",
            align="C",
        )


pdf = PDF()
pdf.alias_nb_pages()
pdf.set_auto_page_break(auto=True, margin=18)
pdf.add_page()

pdf.set_font("Helvetica", "B", 18)
pdf.set_text_color(47, 111, 78)
pdf.cell(0, 12, "Custom Pages URL List", new_x="LMARGIN", new_y="NEXT")

pdf.set_font("Helvetica", "", 11)
pdf.set_text_color(60, 60, 60)
pdf.multi_cell(
    0,
    6,
    "All 70 keyword custom pages created in this chat, with live URLs. "
    "Pages are stored in dynamic_pages and editable from Admin > Custom Pages. "
    "Seed scripts: tools/seed_keyword_pages_batch1.php through batch7.php.",
)
pdf.ln(3)
pdf.set_font("Helvetica", "", 10)
pdf.cell(
    0,
    6,
    "Index page: https://saiflower.com/custom-pages",
    new_x="LMARGIN",
    new_y="NEXT",
    link="https://saiflower.com/custom-pages",
)
pdf.ln(4)

col_w = [12, 78, 90]


def draw_table_header():
    pdf.set_fill_color(47, 111, 78)
    pdf.set_text_color(255, 255, 255)
    pdf.set_font("Helvetica", "B", 9)
    pdf.cell(col_w[0], 8, "#", border=1, fill=True, align="C")
    pdf.cell(col_w[1], 8, "Page Title (Primary Keyword)", border=1, fill=True)
    pdf.cell(col_w[2], 8, "URL", border=1, fill=True, new_x="LMARGIN", new_y="NEXT")
    pdf.set_text_color(40, 40, 40)


draw_table_header()

for num, title, slug in pages:
    if num in batches:
        if pdf.get_y() > 250:
            pdf.add_page()
            draw_table_header()
        pdf.set_fill_color(232, 245, 233)
        pdf.set_font("Helvetica", "B", 9)
        pdf.set_text_color(47, 111, 78)
        pdf.cell(sum(col_w), 8, batches[num], border=1, fill=True, new_x="LMARGIN", new_y="NEXT")
        pdf.set_text_color(40, 40, 40)

    if pdf.get_y() > 270:
        pdf.add_page()
        draw_table_header()

    url = f"{BASE}/{slug}"
    fill = num % 2 == 0
    if fill:
        pdf.set_fill_color(248, 250, 252)
    pdf.set_font("Helvetica", "", 8)
    pdf.cell(col_w[0], 7, str(num), border=1, fill=fill, align="C")
    # Truncate long titles slightly for table fit
    display_title = title if len(title) <= 42 else title[:39] + "..."
    pdf.cell(col_w[1], 7, display_title, border=1, fill=fill)
    pdf.set_text_color(30, 100, 180)
    display_url = url if len(url) <= 52 else url[:49] + "..."
    pdf.cell(col_w[2], 7, display_url, border=1, fill=fill, link=url, new_x="LMARGIN", new_y="NEXT")
    pdf.set_text_color(40, 40, 40)

pdf.ln(8)
pdf.set_font("Helvetica", "B", 11)
pdf.set_text_color(47, 111, 78)
pdf.cell(0, 8, "How to publish", new_x="LMARGIN", new_y="NEXT")
pdf.set_font("Helvetica", "", 9)
pdf.set_text_color(60, 60, 60)
pdf.multi_cell(
    0,
    5,
    "Run each seed file once on the server (browser or CLI):\n"
    "https://saiflower.com/tools/seed_keyword_pages_batch1.php\n"
    "... through batch7.php\n"
    "Existing slugs are skipped automatically. After seeding, pages appear at the URLs above and on /custom-pages.",
)

pdf.output(OUT)
print(OUT)
print(f"Total pages listed: {len(pages)}")
