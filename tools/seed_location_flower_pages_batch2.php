<?php
/**
 * Seed batch 2: Location-based flower delivery custom pages (dynamic_pages)
 * Run once: https://saiflower.com/tools/seed_location_flower_pages_batch2.php
 */
require_once __DIR__ . '/../config.php';

function loc(string $area, string $local, string $context, string $nearby, string $region = 'Delhi NCR'): array
{
    $slug = 'flower-delivery-in-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($area)), '-');
    return [
        'keyword' => "Flower Delivery in {$area}",
        'slug' => $slug,
        'area' => $area,
        'local' => $local,
        'context' => $context,
        'nearby' => $nearby,
        'region' => $region,
    ];
}

$locations = [
    loc('Andrews Ganj', 'Andrews Ganj', 'Andrews Ganj sits quietly between South Extension and Defence Colony, where understated homes and small celebrations deserve bouquets that feel refined, not rushed.', 'South Extension, Defence Colony, and INA', 'South Delhi'),
    loc('East of Kailash', 'East of Kailash', 'East of Kailash blends busy markets with family homes and temple visits. A reliable florist here should understand block numbers, market lanes, and tight delivery windows.', 'Kailash Colony, Greater Kailash, and Nehru Place', 'South Delhi'),
    loc('Kailash Colony', 'Kailash Colony', 'Kailash Colony is a classic South Delhi address — tree-lined streets, established homes, and gifting moments that call for fresh, well-wrapped flowers.', 'East of Kailash, Greater Kailash, and Lajpat Nagar', 'South Delhi'),
    loc('Amar Colony', 'Amar Colony', 'Amar Colony\'s market energy and residential lanes make it a lively gifting zone. From birthday surprises to festival greetings, flowers remain the warmest gesture.', 'Lajpat Nagar, Dayanand Colony, and Zamrudpur', 'South Delhi'),
    loc('New Friends Colony', 'New Friends Colony', 'NFC\'s wide roads and premium homes expect polished presentation. Residents here often order elegant rose bouquets and premium hand-tied arrangements.', 'Maharani Bagh, Okhla, and Sukhdev Vihar', 'South Delhi'),
    loc('Maharani Bagh', 'Maharani Bagh', 'Maharani Bagh is known for spacious homes and quiet avenues. Anniversary and birthday deliveries here should feel premium from wrapping to arrival timing.', 'New Friends Colony, Ashram, and Nizamuddin', 'South Delhi'),
    loc('Okhla', 'Okhla', 'Okhla spans industrial pockets, modern housing, and busy commercial stretches. Clear landmark details help our riders reach factories, offices, and flats on time.', 'Jasola, Kalkaji, and New Friends Colony', 'South Delhi'),
    loc('Jamia Nagar', 'Jamia Nagar', 'Jamia Nagar is a close-knit neighbourhood where family celebrations and festival visits are part of everyday life. Fresh flowers add warmth to every doorstep surprise.', 'Okhla, Batla House, and Abul Fazal Enclave', 'South Delhi'),
    loc('Jasola', 'Jasola', 'Jasola\'s apartment towers and office blocks need precise tower and gate details. Once confirmed, our team delivers crisp bouquets across the district smoothly.', 'Sarita Vihar, Okhla, and Apollo Hospital area', 'South Delhi'),
    loc('Sarita Vihar', 'Sarita Vihar', 'Sarita Vihar is one of South Delhi\'s largest residential hubs. Same-day flower delivery here works best when flat, pocket, and tower numbers are entered clearly.', 'Jasola, Okhla, and Kalindi Kunj', 'South Delhi'),
    loc('Lajpat Nagar', 'Lajpat Nagar', 'Lajpat Nagar never slows down — market runs, family dinners, and last-minute celebrations happen daily. A dependable bouquet service saves real time here.', 'Amar Colony, Jungpura, and Kotla Mubarakpur', 'South Delhi'),
    loc('Jungpura', 'Jungpura', 'Jungpura\'s mix of old homes and new renovations makes address accuracy important. We route orders carefully so your bouquet reaches the right lane and block.', 'Lajpat Nagar, Bhogal, and Nizamuddin', 'South Delhi'),
    loc('Bhikaji Cama Place', 'Bhikaji Cama Place', 'Bhikaji Cama Place is a commercial address where office birthdays and client thank-yous are common. Desk-friendly bouquets are especially popular.', 'RK Puram, Hyatt Regency area, and Moti Bagh', 'South Delhi'),
    loc('INA', 'INA Market area', 'Near INA Market and the metro hub, gifting moves fast. Whether it is a home near the colony or a quick office surprise, timing and freshness matter.', 'Lodhi Colony, Jor Bagh, and Sarojini Nagar', 'South Delhi'),
    loc('Defence Enclave', 'Defence Enclave', 'Defence Enclave\'s calm residential lanes suit thoughtful, well-presented bouquets. Residents appreciate florists who respect delivery slots and careful wrapping.', 'Andrews Ganj, South Extension, and Kotla Mubarakpur', 'South Delhi'),
    loc('Alaknanda', 'Alaknanda', 'Alaknanda\'s planned blocks and family homes make it a steady gifting neighbourhood. Flowers for pujas, birthdays, and anniversaries are ordered year-round.', 'Kalkaji, GK, and Chittaranjan Park', 'South Delhi'),
    loc('Govindpuri', 'Govindpuri', 'Govindpuri connects busy markets with dense residential pockets. Affordable, fresh bouquets with reliable same-day delivery are always in demand here.', 'Kalkaji, Okhla, and Tughlakabad', 'South Delhi'),
    loc('Greater Kailash', 'Greater Kailash', 'Greater Kailash remains one of Delhi\'s most recognised gifting addresses. From GK 1 boutiques to GK 2 homes, presentation and punctuality define a great delivery.', 'Kailash Colony, Chittaranjan Park, and East of Kailash', 'South Delhi'),
    loc('Connaught Place', 'Connaught Place', 'Connaught Place is Delhi\'s commercial heart. Office celebrations, hotel deliveries, and central Delhi surprises need a florist who understands inner-circle addresses.', 'Barakhamba Road, Janpath, and Mandi House', 'Central Delhi'),
    loc('Karol Bagh', 'Karol Bagh', 'Karol Bagh\'s markets and dense housing mean deliveries must be fast and well coordinated. Flowers here often mark birthdays, shop openings, and family milestones.', 'Paharganj, Rajendra Place, and Patel Nagar', 'Central Delhi'),
    loc('Paharganj', 'Paharganj', 'Paharganj is busy, colourful, and always moving. A fresh bouquet delivered on time cuts through the chaos and turns an ordinary day into something memorable.', 'Karol Bagh, New Delhi Railway Station, and Connaught Place', 'Central Delhi'),
    loc('Patel Nagar', 'Patel Nagar', 'Patel Nagar\'s residential blocks and local markets create steady demand for affordable, fresh flower delivery across West-Central Delhi.', 'Rajendra Place, Kirti Nagar, and Shadipur', 'West Delhi'),
    loc('Rajendra Place', 'Rajendra Place', 'Near metro connectivity and medical hubs, Rajendra Place sees frequent get-well and celebration orders. Gentle, thoughtful bouquets are especially appreciated.', 'Patel Nagar, Karol Bagh, and Pusa Road', 'Central Delhi'),
    loc('Mandi House', 'Mandi House', 'Mandi House sits at the crossroads of culture and offices. Deliveries here often go to theatres, institutions, and nearby residential colonies before evening shows.', 'Connaught Place, ITO, and Lodhi Road', 'Central Delhi'),
    loc('India Gate', 'India Gate area', 'The India Gate stretch is iconic for celebrations and commemorative gestures. Address details near Rajpath, embassies, and nearby colonies help riders arrive precisely.', 'Khan Market, Connaught Place, and Pragati Maidan', 'Central Delhi'),
    loc('Pragati Maidan', 'Pragati Maidan', 'Around Pragati Maidan and exhibition grounds, corporate events and hotel deliveries are common. Structured bouquets suit professional gifting in this zone.', 'ITO, India Gate, and Nizamuddin', 'Central Delhi'),
    loc('ITO', 'ITO', 'ITO\'s newspaper offices, government buildings, and busy crossings need punctual delivery. Desk bouquets and medium arrangements work well for office surprises.', 'Mandi House, Pragati Maidan, and Rajghat', 'Central Delhi'),
    loc('Civil Lines', 'Civil Lines', 'Civil Lines carries old-Delhi elegance with university lanes and quiet homes. Residents here value tasteful arrangements and dependable arrival windows.', 'Kashmere Gate, Model Town, and Delhi University North Campus', 'North Delhi'),
    loc('Daryaganj', 'Daryaganj', 'Daryaganj\'s book-market charm and dense neighbourhoods make flower surprises feel especially personal. Sunday plans or weekday milestones — fresh blooms fit both.', 'ITO, Chandni Chowk, and Ansari Road', 'Central Delhi'),
    loc('Chandni Chowk', 'Chandni Chowk', 'Old Delhi\'s busiest quarter needs a florist who respects narrow lanes and festival rush. Wedding season, Diwali, and family gatherings drive constant demand here.', 'Daryaganj, Red Fort area, and Fatehpuri', 'Old Delhi'),
    loc('Rajouri Garden', 'Rajouri Garden', 'Rajouri Garden is a West Delhi favourite for shopping and celebrations. Birthday bouquets, anniversary roses, and same-day home surprises are ordered throughout the week.', 'Tagore Garden, Subhash Nagar, and Maya Enclave', 'West Delhi'),
    loc('Punjabi Bagh', 'Punjabi Bagh', 'Punjabi Bagh\'s markets and residential pockets blend tradition with modern gifting. Premium wrapping and fresh stems keep customers coming back.', 'Paschim Vihar, Shivaji Park, and Moti Nagar', 'West Delhi'),
    loc('Janakpuri', 'Janakpuri', 'Janakpuri\'s districts and local markets create steady flower delivery demand across West Delhi homes, clinics, and small offices.', 'Uttam Nagar, Vikaspuri, and Tilak Nagar', 'West Delhi'),
    loc('Tilak Nagar', 'Tilak Nagar', 'Tilak Nagar is lively, family-oriented, and celebration-friendly. From festival garlands to rose bouquets, flowers are part of the neighbourhood rhythm.', 'Janakpuri, Subhash Nagar, and Vishnu Garden', 'West Delhi'),
    loc('Uttam Nagar', 'Uttam Nagar', 'Uttam Nagar\'s dense housing and busy streets need accurate pocket and gali details. Our riders confirm landmarks when needed to complete smooth deliveries.', 'Janakpuri, Vikaspuri, and Nawada', 'West Delhi'),
    loc('Paschim Vihar', 'Paschim Vihar', 'Paschim Vihar spans multiple phases and markets. Whether you are gifting near the main road or deeper inside a residential pocket, fresh flowers should arrive crisp.', 'Punjabi Bagh, Peeragarhi, and Jwala Heri', 'West Delhi'),
    loc('Subhash Nagar', 'Subhash Nagar', 'Subhash Nagar\'s metro connectivity and shopping lanes make it easy to plan last-minute surprises — if your florist can deliver same day without delays.', 'Rajouri Garden, Tilak Nagar, and Tagore Garden', 'West Delhi'),
    loc('Kirti Nagar', 'Kirti Nagar', 'Near furniture markets and residential blocks, Kirti Nagar orders range from housewarming bouquets to birthday roses for nearby family homes.', 'Patel Nagar, Moti Nagar, and Naraina', 'West Delhi'),
    loc('Naraina', 'Naraina', 'Naraina\'s industrial and residential mix needs flexible delivery timing. Morning office orders and evening home surprises both happen frequently here.', 'Kirti Nagar, Rajendra Place, and Dhaula Kuan', 'West Delhi'),
    loc('Vikaspuri', 'Vikaspuri', 'Vikaspuri families often order flowers for birthdays, pujas, and anniversary dinners. Fresh stems and neat presentation make every gift feel intentional.', 'Janakpuri, Uttam Nagar, and Hastsal', 'West Delhi'),
    loc('Model Town', 'Model Town', 'Model Town\'s leafy blocks and established homes suit elegant bouquets with clean wrapping. Residents here notice quality in every petal and ribbon.', 'Civil Lines, GTB Nagar, and Kamla Nagar', 'North Delhi'),
    loc('Kamla Nagar', 'Kamla Nagar', 'Kamla Nagar near North Campus buzzes with student celebrations, family visits, and festival gifting. Bright, cheerful bouquets are especially popular.', 'Model Town, Roop Nagar, and Malka Ganj', 'North Delhi'),
    loc('Mukherjee Nagar', 'Mukherjee Nagar', 'Mukherjee Nagar\'s coaching-hub energy means constant birthdays, success parties, and family milestones. Reliable same-day delivery matters during busy exam seasons.', 'GTB Nagar, Model Town, and Adarsh Nagar', 'North Delhi'),
    loc('Ashok Vihar', 'Ashok Vihar', 'Ashok Vihar\'s planned phases and local markets make it a strong North Delhi gifting address. Roses, lilies, and mixed bunches are ordered year-round.', 'Shalimar Bagh, Pitampura, and Wazirpur', 'North Delhi'),
    loc('Shalimar Bagh', 'Shalimar Bagh', 'Shalimar Bagh combines market convenience with residential calm. A well-timed bouquet here can brighten a birthday lunch or anniversary dinner effortlessly.', 'Pitampura, Ashok Vihar, and Haiderpur', 'North Delhi'),
    loc('Pitampura', 'Pitampura', 'Pitampura\'s broad roads and dense housing need clear phase and block details. Once confirmed, our team delivers fresh arrangements across the locality on schedule.', 'Rohini, Shalimar Bagh, and Kohat Enclave', 'North Delhi'),
    loc('Rohini', 'Rohini', 'Rohini\'s sectors stretch wide across North West Delhi. Sector-wise address accuracy helps riders reach the right pocket without delays during peak hours.', 'Pitampura, Burari, and Prashant Vihar', 'North West Delhi'),
    loc('Burari', 'Burari', 'Burari\'s growing residential zones and local markets create rising demand for affordable, fresh flower delivery with same-day options.', 'Rohini, Timarpur, and Wazirabad', 'North Delhi'),
    loc('Preet Vihar', 'Preet Vihar', 'Preet Vihar is a well-known East Delhi address for family celebrations. Birthday bouquets and anniversary flowers are among the most frequent orders here.', 'Karkardooma, Vivek Vihar, and Anand Vihar', 'East Delhi'),
    loc('Laxmi Nagar', 'Laxmi Nagar', 'Laxmi Nagar\'s markets and coaching lanes never rest. Quick, fresh flower delivery is perfect for last-minute surprises after a busy day of classes or work.', 'Preet Vihar, Shakarpur, and Nirman Vihar', 'East Delhi'),
    loc('Mayur Vihar', 'Mayur Vihar', 'Mayur Vihar phases cover a large East Delhi population. Tower, phase, and pocket details help us complete smooth doorstep deliveries every day.', 'IP Extension, Vasundhara Enclave, and Anand Vihar', 'East Delhi'),
    loc('IP Extension', 'IP Extension', 'Indraprastha Extension\'s residential towers and local shops see steady gifting demand. Same-day bouquets work well for birthdays and family get-togethers.', 'Mayur Vihar, Patparganj, and Anand Vihar', 'East Delhi'),
    loc('Anand Vihar', 'Anand Vihar', 'Near the metro and bus terminal, Anand Vihar is a transit-friendly gifting zone. Surprises often go to nearby homes before guests arrive from out of town.', 'Karkardooma, Preet Vihar, and Kaushambi border', 'East Delhi'),
    loc('Shahdara', 'Shahdara', 'Shahdara blends old-market character with dense neighbourhoods. Fresh flowers for festivals, weddings, and home celebrations remain in constant demand.', 'Dilshad Garden, Krishna Nagar, and Welcome', 'East Delhi'),
    loc('Krishna Nagar', 'Krishna Nagar', 'Krishna Nagar\'s lively streets and family homes make flower gifts feel warm and personal. Same-day delivery helps when celebrations are planned on short notice.', 'Preet Vihar, Gandhi Nagar, and Arjun Nagar', 'East Delhi'),
    loc('Dilshad Garden', 'Dilshad Garden', 'Dilshad Garden\'s blocks and markets are ideal for birthday and anniversary surprises. Residents appreciate florists who deliver on time without repeated calls.', 'Shahdara, Jhilmil, and GTB Enclave', 'East Delhi'),
    loc('Sector 18 Noida', 'Sector 18 Noida', 'Sector 18 is Noida\'s shopping and entertainment hub. Mall celebrations, restaurant surprises, and apartment gifting all need fast, fresh flower delivery.', 'Sector 16, Atta Market, and Botanical Garden', 'Noida'),
    loc('Sector 62 Noida', 'Sector 62 Noida', 'Sector 62\'s corporate offices and residential towers make desk bouquets and home surprises equally common. Clear building names speed up every delivery.', 'Sector 60, Fortis Hospital area, and Noida City Centre', 'Noida'),
    loc('Sector 137 Noida', 'Sector 137 Noida', 'Sector 137\'s modern societies along the expressway need precise tower and gate details. Our riders coordinate smoothly across high-rise deliveries.', 'Sector 142, Parthala Chowk, and Noida-Greater Noida link', 'Noida'),
    loc('Noida Extension', 'Noida Extension', 'Noida Extension\'s rapidly growing townships depend on dependable florists who understand new sectors, builder societies, and changing pin codes.', 'Gaur City, Greater Noida West, and Hindon area', 'Noida'),
    loc('Greater Noida', 'Greater Noida', 'Greater Noida\'s wide sectors and institutional zones need planned delivery windows. Fresh bouquets for birthdays, housewarmings, and campus events are popular here.', 'Pari Chowk, Knowledge Park, and Alpha Beta sectors', 'Greater Noida'),
    loc('Pari Chowk', 'Pari Chowk', 'Pari Chowk is Greater Noida\'s central landmark. Celebrations around the fountain square and nearby cafes often start with a thoughtful flower surprise.', 'Greater Noida, Omega sector, and Knowledge Park', 'Greater Noida'),
    loc('DLF Phase 1', 'DLF Phase 1 Gurugram', 'DLF Phase 1\'s premium homes and office lanes expect polished gifting. Elegant rose bouquets and premium wrapping suit this Gurugram address well.', 'DLF Phase 2, Golf Course Road, and Sikanderpur', 'Gurugram'),
    loc('DLF Phase 2', 'DLF Phase 2 Gurugram', 'DLF Phase 2 combines residential towers and commercial pockets. Office birthdays and apartment anniversaries drive steady flower delivery demand.', 'DLF Phase 1, Cyber City, and MG Road', 'Gurugram'),
    loc('Golf Course Road', 'Golf Course Road', 'Golf Course Road\'s upscale towers and business addresses call for premium bouquets with reliable timing. Presentation matters as much as freshness here.', 'DLF Phase 5, Sushant Lok, and Sector 54', 'Gurugram'),
    loc('Sohna Road', 'Sohna Road', 'Sohna Road stretches through growing societies and commercial hubs. Accurate society gate and tower details help complete smooth Gurugram deliveries.', 'South City, Malibu Town, and Badshahpur', 'Gurugram'),
    loc('Cyber City', 'Cyber City Gurugram', 'Cyber City\'s corporate towers see constant office gifting — promotions, farewells, and team celebrations. Compact desk bouquets are especially popular.', 'DLF Phase 2, Udyog Vihar, and Sector 24', 'Gurugram'),
    loc('Sushant Lok', 'Sushant Lok', 'Sushant Lok\'s markets and residential blocks are a Gurugram gifting staple. Fresh flowers for birthdays and anniversaries are ordered throughout the week.', 'Golf Course Road, Sector 27, and MG Road', 'Gurugram'),
    loc('NIT Faridabad', 'NIT Faridabad', 'NIT Faridabad\'s dense residential pockets and local markets need dependable same-day florists. Festival and birthday orders peak across this zone.', 'Sector 15 Faridabad, Old Faridabad, and Ballabgarh road', 'Faridabad'),
    loc('Sector 15 Faridabad', 'Sector 15 Faridabad', 'Sector 15 combines markets, clinics, and family homes. Thoughtful bouquets for celebrations and get-well wishes are ordered regularly here.', 'Sector 21 Faridabad, NIT, and Sector 16', 'Faridabad'),
    loc('Sector 21 Faridabad', 'Sector 21 Faridabad', 'Sector 21\'s residential lanes and shopping stretches make it a practical Faridabad address for same-day rose and mixed-flower deliveries.', 'Sector 15 Faridabad, Sector 37, and Escorts Mujesar', 'Faridabad'),
    loc('Indirapuram', 'Indirapuram', 'Indirapuram\'s high-rise societies and lively markets create constant gifting demand across Ghaziabad\'s most popular residential hub.', 'Vaishali, Ahinsa Khand, and Kaushambi', 'Ghaziabad'),
    loc('Vaishali', 'Vaishali', 'Vaishali\'s sectors and metro connectivity make it easy to send flowers across East Delhi borders. Tower and sector details ensure smooth rider handover.', 'Indirapuram, Kaushambi, and Vasundhara', 'Ghaziabad'),
    loc('Kaushambi', 'Kaushambi', 'Kaushambi sits between Ghaziabad and East Delhi with busy hotels, offices, and apartments. Timed deliveries suit this high-traffic gifting corridor.', 'Vaishali, Anand Vihar, and Indirapuram', 'Ghaziabad'),
    loc('Raj Nagar Extension', 'Raj Nagar Extension', 'Raj Nagar Extension\'s growing townships need florists who understand new societies and expanding sectors. Fresh bouquets brighten housewarmings and birthdays alike.', 'Raj Nagar, Mohan Nagar, and Hindon Vihar', 'Ghaziabad'),
];

function generate_content(array $loc): string
{
    $kw = $loc['keyword'];
    $area = $loc['area'];
    $local = $loc['local'];
    $context = $loc['context'];
    $nearby = $loc['nearby'];
    $region = $loc['region'];

    return <<<HTML
<h2>Fresh {$kw} — Fast, Reliable &amp; Beautiful</h2>
<p>Need trusted <strong>{$kw}</strong> without the last-minute panic? {$context} At <strong>Sai Flower</strong>, we build every order around fresh stems, neat wrapping, and riders who know {$region} pin codes.</p>

<p>Whether it is a birthday morning surprise or a same-day anniversary gift, our <strong>{$kw}</strong> service is designed for people who want quality without chasing multiple florists. You pick the bouquet online, we handle the rest.</p>

<h2>Why Locals Choose Our {$kw} Service</h2>
<p>{$region} moves quickly, and your florist should keep up. A dependable <strong>{$kw}</strong> partner means clear order updates, careful handling in summer heat, and arrangements that still look crisp at the door.</p>

<p>Residents across {$local} and nearby areas like {$nearby} trust us for roses, lilies, mixed seasonal bunches, and premium hand-tied bouquets. Every <strong>{$kw}</strong> order is prepared shortly before dispatch so petals stay firm and colours stay vivid.</p>

<h3>Same-Day Flower Delivery Across {$area}</h3>
<p>Place your order before the daily cut-off and enjoy same-day <strong>{$kw}</strong> across {$local} and surrounding blocks. Add flat, tower, or landmark details at checkout — our team confirms on WhatsApp when needed.</p>

<p>Prefer a scheduled slot? Choose your date during checkout and mention a preferred time in the order notes. We align delivery with office hours, evening home visits, and hospital-friendly timings near {$area}.</p>

<h3>Popular Bouquets for {$area} Addresses</h3>
<ul>
<li><strong>Classic red rose bouquet</strong> — perfect for anniversaries and romantic surprises</li>
<li><strong>Mixed seasonal arrangement</strong> — bright, cheerful, and celebration-ready</li>
<li><strong>Premium hand-tied bouquet</strong> — larger stems with elegant wrapping for special milestones</li>
<li><strong>Compact desk bouquet</strong> — ideal for office deliveries in {$area}</li>
</ul>

<p>Browse the showcase above to match your occasion and budget. Not sure what to pick? Message our florists with the recipient's favourite colours and we will suggest the best <strong>{$kw}</strong> option.</p>

<h2>How to Order {$kw} Online</h2>
<p>Ordering takes minutes. Select a bouquet, enter the {$area} address, and pay securely via UPI, card, or wallet. You can also explore our <a href="/flowers" title="Order flowers online Delhi NCR">flower collection</a>, add a <a href="/cakes" title="Cake delivery Delhi NCR">designer cake</a>, or pair blooms with a <a href="/gifts" title="Gift hampers Delhi NCR">gift hamper</a> for a complete surprise.</p>

<p>Each <strong>{$kw}</strong> request is checked for pin-code coverage before dispatch. Protective packaging helps bouquets travel well through traffic and weather — because a beautiful gift should arrive looking as thoughtful as you intended.</p>

<p>We also handle corporate gifting, hospital visits, and festival orders across {$region}, with handwritten note cards available on request for a warmer, more personal finishing touch.</p>

<h3>Make Your Next Celebration in {$area} Unforgettable</h3>
<p>Flowers say what messages sometimes cannot. This season, send a fresh <strong>{$kw}</strong> that feels personal, polished, and on time. Order early for preferred slots, or rely on same-day delivery when plans change at the last minute.</p>

<p>From birthdays and anniversaries to thank-you gestures and festival greetings — Sai Flower is here to help you brighten every home, office, and doorstep across {$local}. Shop now and let us deliver the smile.</p>
HTML;
}

function generate_faqs(array $loc): array
{
    $kw = $loc['keyword'];
    $area = $loc['area'];
    $local = $loc['local'];
    $nearby = $loc['nearby'];

    return [
        [
            'question' => "Do you offer same-day {$kw}?",
            'answer' => "Yes. Place your order before the daily cut-off for same-day {$kw} across {$local} and nearby areas such as {$nearby}. Add your pin code at checkout or WhatsApp our team to confirm express availability.",
        ],
        [
            'question' => "Which areas near {$area} do you cover for flower delivery?",
            'answer' => "We deliver across {$local} and surrounding neighbourhoods including {$nearby}. Enter the full address with tower, flat, or landmark details for the fastest {$area} delivery.",
        ],
        [
            'question' => "What types of flowers can I order for {$area} delivery?",
            'answer' => "You can order roses, lilies, carnations, orchids, and mixed seasonal bouquets for {$area} delivery. Premium hand-tied arrangements and compact desk bunches are also available for offices and homes.",
        ],
        [
            'question' => "How do I order {$kw} online?",
            'answer' => "Visit this page, choose a bouquet from the product showcase, enter the {$area} delivery address, select your date, and complete secure checkout. You can add a personal message in the order notes.",
        ],
        [
            'question' => "Can I send flowers to an office in {$area}?",
            'answer' => "Absolutely. Many customers send desk-friendly bouquets to offices in {$area} and {$local}. Mention the building name, floor, and reception instructions in the delivery notes for smooth handover.",
        ],
        [
            'question' => "What is the price range for {$kw}?",
            'answer' => "Prices vary by bouquet size, flower type, and add-ons. Budget-friendly bunches and premium arrangements are both available. Browse the products above or contact Sai Flower on WhatsApp for a quick recommendation.",
        ],
        [
            'question' => "Can I combine flowers with a cake for {$area} delivery?",
            'answer' => "Yes. Pair your bouquet with a chocolate, butterscotch, or designer cake from our cakes section. Same-day combo delivery is available in many {$area} pin codes when ordered before the cut-off.",
        ],
    ];
}

function generate_meta_title(array $loc): string
{
    $area = $loc['area'];
    $title = $area . ' Flower Delivery | Sai Flower';
    if (strlen($title) > 60) {
        $title = $area . ' Flowers | Sai Flower';
    }
    if (strlen($title) > 60) {
        $title = mb_substr($area, 0, 42) . ' Flowers | Sai Flower';
    }
    return $title;
}

function generate_meta_description(array $loc): string
{
    $region = $loc['region'];
    $desc = "Order fresh {$loc['keyword']} with same-day service in {$region}. Roses, bouquets & gifts from Sai Flower. Shop online today.";
    if (strlen($desc) > 160) {
        $desc = "Fresh {$loc['keyword']} with same-day service in {$region}. Roses & bouquets from Sai Flower. Order online today.";
    }
    return $desc;
}

function generate_meta_keywords(array $loc): string
{
    $slugArea = strtolower($loc['area']);
    return implode(', ', [
        strtolower($loc['keyword']),
        "same day flowers {$slugArea}",
        "online florist {$slugArea}",
        'rose bouquet delivery delhi ncr',
        "birthday flowers {$slugArea}",
        'fresh flower delivery near me',
        'sai flower delivery',
    ]);
}

function count_keyword_stats(string $html, string $keyword): array
{
    $text = html_entity_decode(strip_tags($html));
    $text = preg_replace('/\s+/', ' ', trim($text));
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $count = preg_match_all('/' . preg_quote($keyword, '/') . '/i', $text);
    $total = count($words);
    return [
        'words' => $total,
        'keyword_count' => $count,
        'density' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
    ];
}

$layout_type = 'product_showcase';
$page_tag = 'sameday';
$status = 1;
$short_description = '';

$created = 0;
$skipped = 0;
$errors = [];

$check = $conn->prepare('SELECT id FROM dynamic_pages WHERE slug = ? LIMIT 1');
$insert = $conn->prepare(
    'INSERT INTO dynamic_pages (
        title, short_description, slug, content, meta_title, meta_description,
        meta_keywords, status, layout_type, page_tag, faqs
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

foreach ($locations as $loc) {
    $slug = $loc['slug'];
    $title = $loc['keyword'];
    $content = generate_content($loc);
    $meta_title = generate_meta_title($loc);
    $meta_description = generate_meta_description($loc);
    $meta_keywords = generate_meta_keywords($loc);
    $faqs_json = json_encode(generate_faqs($loc), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stats = count_keyword_stats($content, $title);

    $check->bind_param('s', $slug);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "SKIP  /{$slug} — already exists\n";
        $skipped++;
        continue;
    }

    $insert->bind_param(
        'sssssssisss',
        $title,
        $short_description,
        $slug,
        $content,
        $meta_title,
        $meta_description,
        $meta_keywords,
        $status,
        $layout_type,
        $page_tag,
        $faqs_json
    );

    if ($insert->execute()) {
        echo "OK    /{$slug} — {$stats['words']} words, {$stats['keyword_count']} keywords, {$stats['density']}% density\n";
        echo "      meta title: " . strlen($meta_title) . " chars | meta desc: " . strlen($meta_description) . " chars\n";
        $created++;
    } else {
        $msg = $conn->error;
        echo "FAIL  /{$slug} — {$msg}\n";
        $errors[] = "{$slug}: {$msg}";
    }
}

echo "\nDone. Created: {$created}, Skipped: {$skipped}, Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    exit(1);
}
