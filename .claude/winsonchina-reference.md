# winsonchina.com — reference study

Source: https://winsonchina.com/ (fetched 2026-09-28). This is the existing
company site the Winson Laravel project is modeled after. Use it as the
reference for structure and content, not as something to reproduce byte-for-byte.

## Company / main idea

**Guangzhou Winson Information Technology Co., Ltd** — founded 2011,
manufactures barcode reading equipment and intelligent terminals. Factory in
Yue'an Industrial Park, Guangzhou; offices in Shenzhen and Beijing. The site's
job is B2B lead generation: showcase the product catalog and get visitors to
reach out for a quote — there is no on-site pricing or checkout anywhere.

## Product lines (top-level catalog categories)

- Handheld Barcode Scanner
  - Wired Handheld Barcode Scanner
  - Wireless Handheld Barcode Scanner
- Industrial Barcode Scanner
- Desktop Barcode Scanner
- Barcode Scan Engine (CCD/CMOS modules)
- Fix-Mounted Scanner
- Embedded Scanner
- Data Collector PDA (with RFID)
- Smart Terminal (price checkers, etc.)
- Epidemic Prevention Products

Products are further filterable by sensor technology: **CCD**, **CMOS**, **Laser**.

## Site structure / nav map

```
Home
Products
  └─ (11 category collections above)
Services
  ├─ Buying Guide
  ├─ Product Warranty
  ├─ OEM Design
  └─ FAQ
Applications
  ├─ Temperature Test & Personnel Management
  ├─ Supermarket Cashier
  ├─ Express Scan
  ├─ Medical Scan
  ├─ Industrial Scan
  ├─ Intelligent Device & Scan
  └─ WMS Scan Solution
News
  ├─ Company News
  ├─ Industry News
  └─ Product Guide
About Us
  ├─ Company Profile
  ├─ Quality Control
  ├─ Exhibition Information
  ├─ Certifications
  └─ Partner
Contact Us
Cases (footer link, project case studies)
```

## Homepage content areas

1. Hero banner (features a flagship product, e.g. 8-inch price checker)
2. Product category highlights/grid
3. Company overview blurb ("design, research and development, manufacture and sales")
4. Project case studies (expressway tolls, supermarkets, pharmacies, industrial
   assembly lines)
5. "Get a Quote" call-to-action

## Product listing page pattern

- Grid of product cards; each card = image + product title + "View More" link
  only. **No price, no model number, no "add to cart" shown on the grid.**
- Left sidebar: hierarchical category + sensor-technology filters
- Bottom pagination (numbered pages + "Go to page" jump box)
- Product detail (from "View More") is presumably where specs live and where
  the inquiry path starts — not fetched in this pass.

## Contact info pattern (to mirror the "contact for price" model)

- Phone (office landline + mobile)
- Email
- WhatsApp / WeChat / QQ / Skype (all same or near-same handle/number)
- Physical address
- On-page contact form (with a captcha/verification code)

For the winsonchina.com original: office 020-82510810 / 020-29835849, mobile
+86-18011810087, email Jane_gu@winsonchina.com, QQ 1511028466, address Third
Floor Block B, Yue'an Industrial Park, NO.57 HuangCun Road, Tianhe District,
Guangzhou. (Reference only — do not assume these are the values for *this*
project; confirm real contact details with the user before shipping them.)

## Takeaways for the Laravel rebuild

- Core pages needed: Home, Products (category grid + listing + detail),
  About, Contact. Services/Applications/News are secondary — nice-to-have,
  not required for an MVP.
- Product model needs: category (+ optional sensor-type taxonomy), title,
  image(s), description/specs — deliberately **no price field** driving the
  UI, since the business model is "contact us for pricing."
- Every product detail page should end in a clear CTA: contact form and/or
  WhatsApp/email link, optionally an external e-commerce link if one exists
  for this project.
- Keep it simple: this is a catalog/brochure site, not a store — no cart,
  accounts, or checkout, matching [[claude|CLAUDE.md]]'s stated scope.
