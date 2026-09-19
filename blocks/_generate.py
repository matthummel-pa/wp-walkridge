#!/usr/bin/env python3
"""Write theme block.json metadata for the Walkridge inserter collection."""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent

BLOCKS = [
    ("home-hero", "Home Hero", "cover-image", "Full-width home hero with paths, stats, marquee, and background image.", ["hero", "home", "banner"]),
    ("page-intro", "Page Intro", "heading", "Inner-page title band with eyebrow, heading, and intro copy.", ["intro", "header", "title"]),
    ("info-strip", "Info Strip", "info", "Compact NAP strip for phone, address, and tour-day hours.", ["phone", "hours", "address"]),
    ("section-heading", "Section Heading", "editor-textcolor", "Standalone section heading with optional alternate background.", ["heading", "eyebrow"]),
    ("tour-grid", "Tour Grid", "tickets-alt", "WooCommerce tour cards with optional filters and compare table.", ["tours", "shop", "woocommerce"]),
    ("pathway-cards", "Pathway Cards", "table-col-after", "Two large pathway cards: daylight field and after-dark town.", ["tours", "cards"]),
    ("about-split", "About Split", "align-pull-left", "Copy plus photo split, used on Home and Guides.", ["about", "image", "media"]),
    ("book-band", "Book Band", "cart", "Page-level booking call-to-action with trust marks.", ["book", "cta", "checkout"]),
    ("cta-band", "CTA Band", "megaphone", "Simple heading, text, and button band.", ["cta", "button"]),
    ("custom", "Custom (Generator)", "admin-generic", "Renders a generated custom-block definition from Tools → Walkridge Blocks.", ["custom", "generator"]),
    ("contact-desk", "Contact Desk", "email", "Office NAP card plus guest-services contact form.", ["contact", "form"]),
    ("faq-list", "FAQ List", "editor-help", "Question and answer list. One line per item: Question | Answer.", ["faq", "questions"]),
    ("guide-roster", "Guide Roster", "groups", "Licensed-guide cards. One line per guide: Name | Role | Bio.", ["guides", "team"]),
    ("area-facts", "Area Facts", "location", "Parking, meeting point, and directions cards.", ["area", "parking"]),
    ("area-map", "Area Map", "location-alt", "Gettysburg location map with pin, meeting cards, or the Field Map plugin.", ["map", "area", "gettysburg", "location"]),
    ("timeline", "Battle Timeline", "backup", "Three-day battle timeline. One line per day: Day | Place | Note.", ["timeline", "history"]),
    ("card-grid", "Card Grid", "screenoptions", "Expect or feature cards. Pipe-separated rows.", ["cards", "grid"]),
    ("reviews", "Guest Reviews", "star-filled", "Quoted guest reviews. Quote | Name | Tour.", ["reviews", "testimonials"]),
    ("journal-cards", "Journal Cards", "book-alt", "Field-note cards with optional image key in the last column.", ["journal", "blog"]),
    ("town-grid", "Town Grid", "building", "Nearby towns with distance notes.", ["towns", "area"]),
    ("copy-section", "Copy Section", "media-text", "Rich-text narrative section for the area page.", ["copy", "prose"]),
    ("refund-policy", "Refund Policy", "clipboard", "Store refund windows and contact, edited in the sidebar.", ["refund", "policy"]),
]


def main() -> None:
    for slug, title, icon, description, keywords in BLOCKS:
        directory = ROOT / slug
        directory.mkdir(parents=True, exist_ok=True)
        data = {
            "$schema": "https://schemas.wp.org/trunk/block.json",
            "apiVersion": 3,
            "name": f"walkridge/{slug}",
            "title": title,
            "category": "walkridge",
            "icon": icon,
            "description": description,
            "keywords": ["walkridge", "gettysburg", "tours", *keywords],
            "textdomain": "walkridge",
            "supports": {
                "html": False,
                "anchor": True,
                "customClassName": True,
                "align": False,
            },
            "example": {"attributes": {}},
        }
        if slug in {"home-hero", "contact-desk", "refund-policy"}:
            data["supports"]["multiple"] = False
        (directory / "block.json").write_text(json.dumps(data, indent=2) + "\n")
        readme = directory / "README.md"
        if not readme.exists():
            readme.write_text(
                f"# {title}\n\n"
                f"`walkridge/{slug}`\n\n"
                f"{description}\n\n"
                f"See [SOP](../../docs/blocks/{slug}.md) for editor steps and screenshots.\n"
            )
    print(f"wrote {len(BLOCKS)} block folders")


if __name__ == "__main__":
    main()
