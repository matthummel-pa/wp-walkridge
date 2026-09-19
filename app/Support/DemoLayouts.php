<?php

namespace App\Support;

/**
 * Full concept-page Gutenberg layouts restored from the Hallowed Ground demo.
 */
class DemoLayouts
{
    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function forSlug(string $slug, array $intro = []): string
    {
        return match ($slug) {
            'home', 'front-page' => self::home(),
            'tours' => self::tours($intro),
            'guides' => self::guides($intro),
            'area' => self::area($intro),
            'contact' => self::contact($intro),
            'refund-policy' => self::refund($intro),
            default => self::genericIntro($intro),
        };
    }

    public static function home(): string
    {
        return implode("\n\n", [
            self::comment('walkridge/home-hero', [
                'title' => 'Walk the ground where <em>history turned.</em>',
                'text' => 'Small-group walking, bus, and evening lantern tours of the battlefield — led by licensed guides who bring the three days of July 1863 to life, right where it happened.',
                'secondaryLabel' => 'See All Tours',
                'stats' => "14 | Years Guiding\n4.9★ | Average Rating\n15 | Max Group Size\n5 | Tour Experiences",
                'marquee' => 'Cemetery Ridge | McPherson Ridge | Little Round Top | Devil’s Den | High Water Mark | Lincoln Square | David Wills House | Baltimore Street | Seminary Ridge',
                'leftPathTitle' => 'Walk the field',
                'leftPathText' => 'Daylight tours on Cemetery Ridge, Little Round Top, and the High Water Mark.',
                'rightPathTitle' => 'Walk after dark',
                'rightPathText' => 'Lantern-lit downtown accounts. Real names, letters, and streets — not jump scares.',
            ]),
            '<!-- wp:walkridge/info-strip /-->',
            self::comment('walkridge/about-split', [
                'eyebrow' => 'About Us',
                'heading' => 'Guided by licensed historians, not a script.',
                'text' => '<p>Walkridge Battlefield Tours was founded by a former park ranger and a fourth-generation Adams County family who wanted visitors to experience the field the way locals understand it — grounded in primary sources and walked at a pace that lets the landscape do the talking.</p><p>Every walking and bus tour is led by a guide certified through the same licensing program the national park uses.</p>',
                'primaryLabel' => 'Meet Your Guides',
                'secondaryLabel' => 'About the Area',
                'caption' => 'A farmstead along the Day Two tour route',
            ]),
            self::comment('walkridge/pathway-cards', [
                'eyebrow' => 'Two Ways In',
                'heading' => 'The field by day. The town after dark.',
                'text' => 'Choose the landscape of July 1863, or the civilian streets where the letters were written. Same licensed historians. Different light.',
                'leftEyebrow' => 'Historical',
                'leftTitle' => 'Battlefield walking, bus, hike & private sunrise',
                'leftText' => 'Cemetery Ridge, McPherson Ridge, Little Round Top, Devil’s Den, the High Water Mark.',
                'rightEyebrow' => 'After Dark',
                'rightTitle' => 'Ghosts of Gettysburg Lantern Walk',
                'rightText' => 'Downtown. Lincoln Square. The David Wills House. Candlelit storytelling from the record.',
            ]),
            self::comment('walkridge/timeline', [
                'eyebrow' => 'July 1–3, 1863',
                'heading' => 'Three days, walked in order.',
                'text' => 'Our daytime tours stitch the battle to the ground — not a greatest-hits montage.',
                'items' => "Day One | McPherson Ridge | The opening fight west of town. How the first day pulled the armies onto the ridges that still hold the line.\nDay Two | Little Round Top | The southern flank, the rocks of Devil’s Den, and the high ground Union troops fought to keep.\nDay Three | High Water Mark | Cemetery Ridge and Pickett’s Charge — the assault you can still pace from the copse of trees.",
            ]),
            '<!-- wp:walkridge/tour-grid {"limit":3,"showFilters":true,"showCompare":false,"eyebrow":"Choose Your Tour","heading":"Five ways to walk the field.","text":"From an easy narrated bus loop to a lantern-lit evening walk through town, each tour is capped at a small group size so you can actually hear your guide — and ask questions."} /-->',
            self::comment('walkridge/card-grid', [
                'variant' => 'expect',
                'eyebrow' => 'Before You Go',
                'heading' => 'What to expect on tour.',
                'text' => 'A little preparation goes a long way on the battlefield. Here’s what every guest should know before meeting us.',
                'items' => "Dress for the field | Closed-toe walking shoes, sun protection, and a water bottle. Walking tours cover uneven, sometimes rocky terrain.\nRain or shine | Tours run in light rain and most weather. We’ll call or email you directly if conditions require a reschedule.\nSmall groups | Most tours cap at 12–24 guests. You’ll always be close enough to hear your guide without a headset.\nArrive 15 minutes early | Check in at the sample ticket office, or the posted evening meeting point, before departure time.",
            ]),
            self::comment('walkridge/card-grid', [
                'variant' => 'feature',
                'alt' => true,
                'eyebrow' => 'Also On Offer',
                'heading' => 'Groups, gifts, and an easier way in.',
                'text' => 'Features most tour operators forget until the phone rings.',
                'items' => "Gift certificates | Give a licensed-guide tour instead of a souvenir. Guest services issues certificates mapped to a simple product. | WooCommerce | /contact/\nPrivate groups | The Sunrise Private Battlefield Experience is capped at six. Larger families, schools, and corporate groups: call the sample desk. | Schools & reunions | /contact/\nADA bus loop | The deluxe bus tour is seated and ADA accessible. Walking tours use uneven ground and are not wheelchair accessible. | Access | /contact/",
            ]),
            '<!-- wp:walkridge/book-band {"heading":"Reserve your spot in three minutes.","text":"Pick a tour, choose a date, and check out securely. You’ll get a confirmation with your ticket number by email."} /-->',
            self::reviewsBlock(),
            self::comment('walkridge/journal-cards', [
                'eyebrow' => 'Field Notes',
                'heading' => 'Read before you walk.',
                'text' => 'Concept journal cards — parking, licensing, and why the lantern walk is not a haunted house.',
                'items' => "The Area | Where to stand on Cemetery Ridge | Parking, meeting points, and the ridges our walking tours actually cross. | /area/ | cannon\nGuides | What a licensed battlefield guide is | The same exam used at the national military park. | /guides/ | wentz\nAfter dark | Why the lantern walk is not a haunted house | Letters, diaries, and downtown streets. Sample meet at the town square flagpole. | /tours/#after-dark | downtown",
            ]),
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function tours(array $intro): string
    {
        $defaults = PageFields::defaultsForSlug('tours');

        return implode("\n\n", [
            self::comment('walkridge/page-intro', self::introAttrs($intro, $defaults)),
            '<!-- wp:walkridge/info-strip /-->',
            '<!-- wp:walkridge/tour-grid {"eyebrow":"Choose Your Tour","heading":"Five ways to walk the field.","text":"Every tour is led by an Association-licensed guide and capped at a small group size. Prices are per person; children are ages 6–12 and seniors are 65+."} /-->',
            self::comment('walkridge/card-grid', [
                'variant' => 'expect',
                'eyebrow' => 'Before You Go',
                'heading' => 'What to expect on tour.',
                'text' => 'A little preparation goes a long way on the battlefield.',
                'items' => "Dress for the field | Closed-toe walking shoes, sun protection, and a water bottle. Walking tours cover uneven, sometimes rocky terrain.\nRain or shine | Tours run in light rain and most weather. We’ll call or email you directly if conditions require a reschedule.\nSmall groups | Most tours cap at 12–24 guests. You’ll always be close enough to hear your guide without a headset.\nArrive 15 minutes early | Check in at the sample ticket office, or the posted evening meeting point, before departure time.",
            ]),
            '<!-- wp:walkridge/book-band {"heading":"Ready to pick your date?"} /-->',
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function guides(array $intro): string
    {
        $defaults = PageFields::defaultsForSlug('guides');

        return implode("\n\n", [
            self::comment('walkridge/page-intro', self::introAttrs($intro, $defaults)),
            '<!-- wp:walkridge/info-strip /-->',
            self::comment('walkridge/guide-roster', [
                'eyebrow' => 'The Team',
                'heading' => 'Historians who live the ground they walk.',
                'items' => "Eleanor Voss | Walking & hike specialist | Leads the highlights walk and the ridge hike. Day Two ground, Cemetery Ridge artillery, and the ridges you can actually walk.\nMaya Trent | After-dark & civilian-history specialist | Leads the lantern walk through downtown — the square, the Wills House streets, and the civilian record after dark.\nJames Whitaker | Bus & private-sunrise specialist | Leads the ADA-accessible coach loop and dawn routes capped at six.\nClara Brennan | Families & school groups | Mixed-age walking tours across all three days, at a pace that works for first-time visitors.",
            ]),
            self::comment('walkridge/about-split', [
                'eyebrow' => 'Licensed Guides',
                'heading' => 'The same exam the park uses.',
                'text' => '<p>Roles above are sample specialties for this concept — not named people or live credentials. Every walking and bus departure is still written as if a licensed battlefield guide is on the ground with you.</p>',
                'primaryLabel' => 'Book a Tour',
                'secondaryLabel' => 'See Tours',
                'flip' => true,
            ]),
            self::reviewsBlock(),
            '<!-- wp:walkridge/book-band {"heading":"Tour with a licensed guide."} /-->',
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function area(array $intro): string
    {
        $defaults = PageFields::defaultsForSlug('area');

        return implode("\n\n", [
            self::comment('walkridge/page-intro', self::introAttrs($intro, $defaults)),
            '<!-- wp:walkridge/info-strip /-->',
            self::comment('walkridge/copy-section', [
                'eyebrow' => 'The Battlefield',
                'heading' => 'Where the Battle of Gettysburg was fought',
                'text' => '<p>The Battle of Gettysburg was fought July 1–3, 1863, across the ridges, fields, and hills that surround the borough of Gettysburg, Pennsylvania. Today that ground is preserved as Gettysburg National Military Park, with more than 1,300 monuments spread across roughly 6,000 acres. Our tours are designed to make sense of that landscape — not just the maps, but the actual rises and swales where the fighting turned.</p>',
            ]),
            self::comment('walkridge/card-grid', [
                'variant' => 'expect',
                'eyebrow' => 'On the Ground',
                'heading' => 'Ridges, rocks, and downtown streets.',
                'text' => 'The same licensed historians walk daylight ridges and civilian streets after dark.',
                'items' => "Seminary & Cemetery Ridge | Two long parallel ridges shaped the battle. The Highlights Walking Tour crosses Cemetery Ridge and McPherson Ridge to connect all three days into a single, walkable story.\nLittle Round Top & Devil’s Den | At the southern end of the field, the high ground of July 2 and the boulders below it. The ridge hike climbs this rugged terrain with time among the rocks.\nDowntown & the Wills House | Lincoln Square is the civic heart of town. The evening lantern walk winds these streets after dark, pairing wartime accounts with the buildings where they happened.",
            ]),
            self::comment('walkridge/area-facts', [
                'eyebrow' => 'Find Us',
                'heading' => 'Meeting points, parking, and office hours',
                'parking' => 'This concept uses a sample ticket office at 100 Sample Street, Gettysburg, PA 17325 — not a live storefront. Use downtown public lots and metered street parking near Lincoln Square.',
                'meeting' => 'Walking and bus tours in this demo start from the sample office. Evening lantern walks use a sample downtown meet at the Lincoln Square flagpole — look for your guide holding a lit lantern.',
                'directions' => 'From the north or south, take US-15 to the Gettysburg exits, then follow Baltimore Street toward downtown. From the east or west, US-30 leads into Lincoln Square. Most guests arriving from Harrisburg, York, Hanover, or the Maryland line reach downtown in well under an hour.',
            ]),
            self::comment('walkridge/town-grid', [
                'eyebrow' => 'Nearby Towns We Serve',
                'heading' => 'Adams County & beyond',
                'text' => 'Approximate distances to the sample 100 Sample Street meeting point.',
                'items' => "Biglerville | ~9 mi N · US-15 & PA-34\nLittlestown | ~10 mi SE · PA-97\nNew Oxford | ~9 mi E · US-30\nMcSherrystown | ~13 mi E\nFairfield | ~8 mi W · PA-116\nCashtown | ~8 mi NW · US-30\nHanover | ~15 mi SE · PA-116",
            ]),
            self::comment('walkridge/cta-band', [
                'eyebrow' => 'Plan the visit',
                'heading' => 'Ready to walk the field?',
                'text' => 'Pick a tour and a date. Sample checkout only.',
                'buttonLabel' => 'Browse Tours',
            ]),
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function contact(array $intro): string
    {
        $defaults = PageFields::defaultsForSlug('contact');

        return implode("\n\n", [
            self::comment('walkridge/page-intro', self::introAttrs($intro, $defaults)),
            '<!-- wp:walkridge/info-strip /-->',
            self::comment('walkridge/card-grid', [
                'variant' => 'feature',
                'eyebrow' => 'Plan Ahead',
                'heading' => 'Gifts, groups, and an easier way in.',
                'items' => "Gift certificates | Ask guest services to issue a certificate toward any of the five tours. In Sage this becomes a WooCommerce simple product. | Give the walk | #\nPrivate groups | The Sunrise Private Battlefield Experience is built for up to six guests. Families, reunions, schools, and corporate groups: use the form. | Groups & schools | #",
            ]),
            '<!-- wp:walkridge/contact-desk /-->',
            self::comment('walkridge/faq-list', [
                'eyebrow' => 'Good to Know',
                'heading' => 'Frequently asked questions.',
                'items' => self::faqItems(),
            ]),
            '<!-- wp:walkridge/book-band {"heading":"Ready to book?"} /-->',
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function refund(array $intro): string
    {
        $defaults = PageFields::defaultsForSlug('refund-policy');

        return implode("\n\n", [
            self::comment('walkridge/page-intro', self::introAttrs($intro, $defaults) ?: [
                'eyebrow' => 'Store Policy',
                'heading' => 'Refund Policy',
            ]),
            self::comment('walkridge/refund-policy', [
                'effectiveDate' => 'September 2, 2026',
                'storeName' => Identity::brandName(),
                'storeUrl' => home_url('/'),
                'contactEmail' => Identity::email(),
                'refundWindowDays' => 30,
                'resolutionDays' => 7,
                'duplicateDays' => 7,
                'responseDays' => 2,
                'paymentDaysMin' => 5,
                'paymentDaysMax' => 10,
            ]),
        ]);
    }

    public static function faqItems(): string
    {
        return implode("\n", [
            'Where do tours meet? | Walking and bus tours in this concept meet at a sample ticket office at 100 Sample Street, Gettysburg, PA 17325 — not a live storefront. The lantern walk uses a sample downtown meet at the Lincoln Square flagpole.',
            'Are tours accessible for guests with mobility limitations? | The deluxe bus tour is ADA-accessible and requires no walking beyond short stops. Walking tours involve uneven, sometimes sloped terrain and are not wheelchair accessible.',
            'What happens in bad weather? | Tours operate rain or shine, including light rain and cold. In severe weather we contact you by phone or email at least 2 hours before departure to reschedule or refund.',
            'What is your cancellation and refund policy? | Cancel or reschedule up to 24 hours before your tour for a full refund. Cancellations inside 24 hours are eligible for a credit toward a future tour. No-shows are non-refundable.',
            'Where should I park? | Use downtown public lots and metered street parking near Lincoln Square. This concept does not list a live loading zone or a company garage.',
            'Do you offer private and group tours? | Yes. The sunrise private experience is built for up to six guests, and we arrange private bus and walking tours for families, reunions, schools, and corporate groups.',
        ]);
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     * @param  array{eyebrow: string, heading: string, intro: string}  $defaults
     * @return array<string, string>
     */
    private static function introAttrs(array $intro, array $defaults): array
    {
        return array_filter([
            'eyebrow' => $intro['eyebrow'] ?? $defaults['eyebrow'],
            'heading' => $intro['heading'] ?? $defaults['heading'],
            'intro' => $intro['intro'] ?? $defaults['intro'],
        ], static fn ($v) => is_string($v) && $v !== '');
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    private static function genericIntro(array $intro): string
    {
        if (($intro['heading'] ?? '') === '' && ($intro['intro'] ?? '') === '') {
            return '';
        }

        return self::comment('walkridge/page-intro', $intro)."\n\n<!-- wp:walkridge/info-strip /-->";
    }

    private static function reviewsBlock(): string
    {
        return self::comment('walkridge/reviews', [
            'eyebrow' => 'Guest Reviews',
            'heading' => 'What guests say after the walk.',
            'text' => 'Sample reviews shown for this concept design.',
            'items' => "Our guide made three days of history feel like one afternoon. My kids still talk about Little Round Top. | Karen D. | Ridge hike · Hanover, PA\nThe lantern walk was genuinely moving, not gimmicky. Every stop tied back to a real name and letter. | Ray P. | Lantern walk · New Oxford, PA\nBooked the bus tour for my in-laws who can't walk far. Comfortable, well-paced, and still packed with detail. | Sandra M. | Bus tour · Littlestown, PA",
        ]);
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    public static function comment(string $name, array $attrs): string
    {
        $clean = array_filter(
            $attrs,
            static fn ($v) => $v !== '' && $v !== null
        );
        if ($clean === []) {
            return "<!-- wp:{$name} /-->";
        }

        $json = wp_json_encode($clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return "<!-- wp:{$name} {$json} /-->";
    }
}
