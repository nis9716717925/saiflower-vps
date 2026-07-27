from fpdf import FPDF

class BlogPDF(FPDF):
    def header(self):
        pass
    def footer(self):
        pass

pdf = BlogPDF('P', 'mm', 'A4')
pdf.set_auto_page_break(auto=True, margin=15)
pdf.add_page()

pdf.set_font('Helvetica', 'B', 18)
pdf.cell(0, 12, 'Sai Flower - Blog Pages', new_x="LMARGIN", new_y="NEXT", align='C')
pdf.set_font('Helvetica', '', 10)
pdf.set_text_color(100, 100, 100)
pdf.cell(0, 6, '80 Blog Posts | saiflower.com', new_x="LMARGIN", new_y="NEXT", align='C')
pdf.ln(8)

blogs = [
    ("How to Choose the Perfect Flower Bouquet for Every Occasion", "how-to-choose-the-perfect-flower-bouquet-for-every-occasion"),
    ("What Flowers Symbolize Love, Friendship, and Gratitude?", "what-flowers-symbolize-love-friendship-and-gratitude"),
    ("Which Flowers Last the Longest After Delivery?", "which-flowers-last-the-longest-after-delivery"),
    ("Fresh Flowers vs Artificial Flowers: Which Is Better?", "fresh-flowers-vs-artificial-flowers-which-is-better"),
    ("How to Keep Flowers Fresh for 7-10 Days", "how-to-keep-flowers-fresh-for-7-10-days"),
    ("What Does Each Flower Color Mean?", "what-does-each-flower-color-mean"),
    ("Best Flowers for Birthdays in India", "best-flowers-for-birthdays-in-india"),
    ("Best Anniversary Flowers and Their Meanings", "best-anniversary-flowers-and-their-meanings"),
    ("Which Flowers Are Best for Proposals?", "which-flowers-are-best-for-proposals"),
    ("Best Flowers to Say Sorry", "best-flowers-to-say-sorry"),
    ("How to Buy Flowers Online Without Getting Scammed", "how-to-buy-flowers-online-without-getting-scammed"),
    ("Things to Check Before Ordering Flowers Online", "things-to-check-before-ordering-flowers-online"),
    ("Same-Day vs Midnight Flower Delivery: Which Should You Choose?", "same-day-vs-midnight-flower-delivery-which-should-you-choose"),
    ("How Flower Delivery Works in Delhi", "how-flower-delivery-works-in-delhi"),
    ("What Makes a Premium Flower Bouquet?", "what-makes-a-premium-flower-bouquet"),
    ("How Florists Keep Flowers Fresh During Delivery", "how-florists-keep-flowers-fresh-during-delivery"),
    ("How Much Should You Spend on a Bouquet?", "how-much-should-you-spend-on-a-bouquet"),
    ("How to Choose Luxury Flower Bouquets", "how-to-choose-luxury-flower-bouquets"),
    ("Fresh Flowers vs Imported Flowers", "fresh-flowers-vs-imported-flowers"),
    ("Why Handmade Bouquets Are Worth It", "why-handmade-bouquets-are-worth-it"),
    ("Best Flower Delivery in Greater Kailash", "best-flower-delivery-in-greater-kailash"),
    ("Flower Delivery in South Delhi: Complete Guide", "flower-delivery-in-south-delhi-complete-guide"),
    ("Best Florists Near Greater Kailash", "best-florists-near-greater-kailash"),
    ("Same-Day Flower Delivery in GK", "same-day-flower-delivery-in-gk"),
    ("Midnight Flower Delivery in South Delhi", "midnight-flower-delivery-in-south-delhi"),
    ("Flower Delivery in Defence Colony", "flower-delivery-in-defence-colony"),
    ("Flower Delivery in Saket", "flower-delivery-in-saket"),
    ("Flower Delivery in Lajpat Nagar", "flower-delivery-in-lajpat-nagar"),
    ("Flower Delivery in Green Park", "flower-delivery-in-green-park"),
    ("Flower Delivery Near Nehru Place", "flower-delivery-near-nehru-place"),
    ("Birthday Flower Ideas", "birthday-flower-ideas"),
    ("Anniversary Bouquet Guide", "anniversary-bouquet-guide"),
    ("Valentine's Day Flower Guide", "valentines-day-flower-guide"),
    ("Mother's Day Flower Guide", "mothers-day-flower-guide"),
    ("Father's Day Flower Guide", "fathers-day-flower-guide"),
    ("Women's Day Flower Guide", "womens-day-flower-guide"),
    ("Raksha Bandhan Flower Gifts", "raksha-bandhan-flower-gifts"),
    ("Diwali Flower Decorations", "diwali-flower-decorations"),
    ("Wedding Flower Guide", "wedding-flower-guide"),
    ("Housewarming Flower Ideas", "housewarming-flower-ideas"),
    ("Meaning of Roses", "meaning-of-roses"),
    ("Meaning of Lilies", "meaning-of-lilies"),
    ("Meaning of Orchids", "meaning-of-orchids"),
    ("Meaning of Carnations", "meaning-of-carnations"),
    ("Meaning of Gerberas", "meaning-of-gerberas"),
    ("Meaning of Tulips", "meaning-of-tulips"),
    ("Most Romantic Flowers", "most-romantic-flowers"),
    ("Flowers That Represent Success", "flowers-that-represent-success"),
    ("Flowers That Symbolize New Beginnings", "flowers-that-symbolize-new-beginnings"),
    ("Flowers That Represent Friendship", "flowers-that-represent-friendship"),
    ("Roses vs Lilies", "roses-vs-lilies"),
    ("Orchids vs Roses", "orchids-vs-roses"),
    ("Fresh vs Dried Flowers", "fresh-vs-dried-flowers"),
    ("Flower Bouquet vs Flower Basket", "flower-bouquet-vs-flower-basket"),
    ("Premium vs Budget Bouquets", "premium-vs-budget-bouquets"),
    ("Imported vs Indian Flowers", "imported-vs-indian-flowers"),
    ("Hand-Tied vs Box Bouquets", "hand-tied-vs-box-bouquets"),
    ("Same-Day vs Scheduled Delivery", "same-day-vs-scheduled-delivery"),
    ("Luxury Florist vs Local Florist", "luxury-florist-vs-local-florist"),
    ("Online Flower Delivery vs Offline Flower Shop", "online-flower-delivery-vs-offline-flower-shop"),
    ("Can Flowers Be Delivered in 2 Hours?", "can-flowers-be-delivered-in-2-hours"),
    ("Can I Schedule Midnight Flower Delivery?", "can-i-schedule-midnight-flower-delivery"),
    ("How Early Should I Order Wedding Flowers?", "how-early-should-i-order-wedding-flowers"),
    ("Are Online Flowers Fresh?", "are-online-flowers-fresh"),
    ("Why Are Flower Prices Different?", "why-are-flower-prices-different"),
    ("Which Bouquet Is Best for a Girlfriend?", "which-bouquet-is-best-for-a-girlfriend"),
    ("Which Flowers Are Best for Parents?", "which-flowers-are-best-for-parents"),
    ("Which Flowers Should You Avoid on Certain Occasions?", "which-flowers-should-you-avoid-on-certain-occasions"),
    ("How Many Roses Should You Gift?", "how-many-roses-should-you-gift"),
    ("What Is the Best Bouquet Under Rs.1000?", "what-is-the-best-bouquet-under-1000"),
    ("Complete Guide to Flower Delivery in Delhi", "complete-guide-to-flower-delivery-in-delhi"),
    ("Flower Care Guide", "flower-care-guide"),
    ("Seasonal Flowers in India", "seasonal-flowers-in-india"),
    ("Complete Bouquet Buying Guide", "complete-bouquet-buying-guide"),
    ("Flower Gift Etiquette", "flower-gift-etiquette"),
    ("Corporate Flower Gifting Guide", "corporate-flower-gifting-guide"),
    ("Wedding Flower Checklist", "wedding-flower-checklist"),
    ("Office Flower Decoration Guide", "office-flower-decoration-guide"),
    ("Event Flower Decoration Guide", "event-flower-decoration-guide"),
    ("Luxury Flower Trends in India", "luxury-flower-trends-in-india"),
]

base_url = "https://saiflower.com/blog/"

for i, (title, slug) in enumerate(blogs, 1):
    url = base_url + slug

    pdf.set_font('Helvetica', 'B', 10)
    pdf.set_text_color(30, 30, 30)
    pdf.cell(8, 6, f"{i}.", new_x="END", new_y="TOP")
    pdf.cell(0, 6, title, new_x="LMARGIN", new_y="NEXT")

    pdf.set_font('Helvetica', '', 8)
    pdf.set_text_color(40, 100, 60)
    pdf.cell(8, 5, '', new_x="END", new_y="TOP")
    pdf.cell(0, 5, url, new_x="LMARGIN", new_y="NEXT", link=url)

    pdf.ln(2)

output_path = r"C:\Users\Nishant Singh\Desktop\Sai_Flower_Blog_Pages.pdf"
pdf.output(output_path)
print(f"PDF saved to: {output_path}")
