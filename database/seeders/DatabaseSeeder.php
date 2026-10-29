<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@shopsphere.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+1-555-0100',
            ]
        );

        // 2. Vendors & Stores
        $vendorsData = [
            [
                'name' => 'Marcus Vance',
                'email' => 'vendor1@shopsphere.test',
                'store_name' => 'Apex Tech Gear',
                'slug' => 'apex-tech-gear',
                'description' => 'High-performance computing accessories and ergonomic peripherals.',
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'vendor2@shopsphere.test',
                'store_name' => 'Nordic Living',
                'slug' => 'nordic-living',
                'description' => 'Minimalist Scandinavian home decor, ceramics, and sustainable textiles.',
            ],
            [
                'name' => 'Tariq Al-Mansoor',
                'email' => 'vendor3@shopsphere.test',
                'store_name' => 'Urban Apparel',
                'slug' => 'urban-apparel',
                'description' => 'Contemporary streetwear, outerwear, and durable travel gear.',
            ],
            [
                'name' => 'Maya Lin',
                'email' => 'vendor4@shopsphere.test',
                'store_name' => 'Green Earth Organics',
                'slug' => 'green-earth-organics',
                'description' => 'Organic wellness supplies, natural skincare, and eco-friendly home goods.',
            ],
        ];

        $stores = [];
        foreach ($vendorsData as $v) {
            $vendorUser = User::firstOrCreate(
                ['email' => $v['email']],
                [
                    'name' => $v['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_VENDOR,
                    'phone' => '+1-555-010' . rand(1, 9),
                ]
            );

            $stores[] = Store::firstOrCreate(
                ['slug' => $v['slug']],
                [
                    'user_id' => $vendorUser->id,
                    'name' => $v['store_name'],
                    'description' => $v['description'],
                    'contact_email' => $v['email'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Customers
        $customer = User::firstOrCreate(
            ['email' => 'customer@shopsphere.test'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'phone' => '+1-555-0199',
            ]
        );

        // 4. Categories
        $categoriesData = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Gadgets, audio, computing gear, and hardware.'],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'description' => 'Furniture, kitchenware, modern lighting, and cozy living.'],
            ['name' => 'Apparel & Fashion', 'slug' => 'apparel-fashion', 'description' => 'Everyday wear, jackets, shoes, and streetwear.'],
            ['name' => 'Wellness & Beauty', 'slug' => 'wellness-beauty', 'description' => 'Natural skincare, botanical oils, and self-care essentials.'],
            ['name' => 'Outdoor & Sports', 'slug' => 'outdoor-sports', 'description' => 'Camping, hiking backpacks, athletic shoes, and hydration gear.'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[] = Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        // 5. Baseline Curated Products
        $curatedProducts = [
            // Apex Tech Gear (Electronics)
            [
                'store_idx' => 0, 'cat_idx' => 0,
                'name' => 'Mechanical Wireless Keyboard Pro',
                'slug' => 'mechanical-wireless-keyboard-pro',
                'sku' => 'APX-KB-001',
                'price' => 129.99,
                'stock' => 45,
                'description' => 'Custom hot-swappable switches with double-shot PBT keycaps and ultra-low latency 2.4GHz wireless connectivity.',
            ],
            [
                'store_idx' => 0, 'cat_idx' => 0,
                'name' => 'Ergonomic Precision Mouse',
                'slug' => 'ergonomic-precision-mouse',
                'sku' => 'APX-MS-002',
                'price' => 79.50,
                'stock' => 60,
                'description' => 'Sculpted form factor designed for all-day comfort with a 26,000 DPI optical sensor and magnetic scroll wheel.',
            ],
            [
                'store_idx' => 0, 'cat_idx' => 0,
                'name' => 'Active Noise Canceling Headphones',
                'slug' => 'active-noise-canceling-headphones',
                'sku' => 'APX-AU-003',
                'price' => 199.00,
                'stock' => 30,
                'description' => 'Hybrid ANC with 40mm beryllium drivers, 38-hour battery endurance, and multipoint Bluetooth pairing.',
            ],
            [
                'store_idx' => 0, 'cat_idx' => 0,
                'name' => 'Ultra-Wide Curved Monitor 34-Inch',
                'slug' => 'ultra-wide-curved-monitor-34-inch',
                'sku' => 'APX-DP-004',
                'price' => 489.00,
                'stock' => 14,
                'description' => 'WQHD 144Hz 1800R curved display with 99% sRGB color gamut and USB-C 90W power delivery.',
            ],

            // Nordic Living (Home & Living)
            [
                'store_idx' => 1, 'cat_idx' => 1,
                'name' => 'Minimalist Oak Desk Lamp',
                'slug' => 'minimalist-oak-desk-lamp',
                'sku' => 'NOR-LP-101',
                'price' => 64.00,
                'stock' => 25,
                'description' => 'Sustainably sourced white oak with dimmable warm LED bar and touch-sensitive brass control.',
            ],
            [
                'store_idx' => 1, 'cat_idx' => 1,
                'name' => 'Handcrafted Ceramic Pour-Over Set',
                'slug' => 'handcrafted-ceramic-pour-over-set',
                'sku' => 'NOR-CK-102',
                'price' => 45.00,
                'stock' => 40,
                'description' => 'Stone-glazed matte finish ceramic dripper with 600ml matching carafe for slow-brew coffee rituals.',
            ],
            [
                'store_idx' => 1, 'cat_idx' => 1,
                'name' => 'Linen Cushion Cover Duo',
                'slug' => 'linen-cushion-cover-duo',
                'sku' => 'NOR-TX-103',
                'price' => 38.50,
                'stock' => 50,
                'description' => 'Stonewashed 100% natural European flax linen covers with concealed zip closures.',
            ],

            // Urban Apparel (Fashion)
            [
                'store_idx' => 2, 'cat_idx' => 2,
                'name' => 'Waterproof All-Weather Commuter Shell',
                'slug' => 'waterproof-all-weather-commuter-shell',
                'sku' => 'URB-JK-201',
                'price' => 155.00,
                'stock' => 20,
                'description' => 'Three-layer breathable recycled membrane featuring taped seams, waterproof YKK zips, and reflective trims.',
            ],
            [
                'store_idx' => 2, 'cat_idx' => 2,
                'name' => 'Heavyweight Cotton Boxy Tee',
                'slug' => 'heavyweight-cotton-boxy-tee',
                'sku' => 'URB-TS-202',
                'price' => 34.00,
                'stock' => 80,
                'description' => '280 GSM combed organic cotton with relaxed drop-shoulder cut and reinforced collar stitching.',
            ],
            [
                'store_idx' => 2, 'cat_idx' => 2,
                'name' => 'Technical Cargo Pants',
                'slug' => 'technical-cargo-pants',
                'sku' => 'URB-PT-203',
                'price' => 89.00,
                'stock' => 35,
                'description' => 'Stretch ripstop fabric with articulate knee darts, FIDLOCK belt buckle, and deep utility pockets.',
            ],

            // Green Earth Organics (Wellness)
            [
                'store_idx' => 3, 'cat_idx' => 3,
                'name' => 'Organic Cold-Pressed Rosehip Serum',
                'slug' => 'organic-cold-pressed-rosehip-serum',
                'sku' => 'GEO-SK-301',
                'price' => 28.00,
                'stock' => 70,
                'description' => 'Rich in essential fatty acids and antioxidants to support skin regeneration and deep barrier hydration.',
            ],
            [
                'store_idx' => 3, 'cat_idx' => 3,
                'name' => 'Botanical Aromatherapy Diffuser',
                'slug' => 'botanical-aromatherapy-diffuser',
                'sku' => 'GEO-DF-302',
                'price' => 52.00,
                'stock' => 22,
                'description' => 'Ultrasonic cool-mist diffuser with handcrafted porcelain stone shell and subtle ambient night glow.',
            ],

            // Apex / Urban (Outdoor)
            [
                'store_idx' => 2, 'cat_idx' => 4,
                'name' => 'Modular Roll-Top Travel Pack 28L',
                'slug' => 'modular-roll-top-travel-pack-28l',
                'sku' => 'URB-BP-401',
                'price' => 140.00,
                'stock' => 18,
                'description' => 'Weatherproof Cordura 500D nylon backpack with dedicated 16-inch laptop compartment and luggage pass-through.',
            ],
            [
                'store_idx' => 3, 'cat_idx' => 4,
                'name' => 'Double-Walled Insulated Flask 1L',
                'slug' => 'double-walled-insulated-flask-1l',
                'sku' => 'GEO-FL-402',
                'price' => 32.00,
                'stock' => 90,
                'description' => 'Kitchen-grade 18/8 stainless steel bottle keeping beverages iced for 24 hours or steaming hot for 12 hours.',
            ],
        ];

        foreach ($curatedProducts as $p) {
            Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'store_id' => $stores[$p['store_idx']]->id,
                    'category_id' => $categories[$p['cat_idx']]->id,
                    'name' => $p['name'],
                    'sku' => $p['sku'],
                    'description' => $p['description'],
                    'price' => $p['price'],
                    'stock' => $p['stock'],
                    'is_active' => true,
                ]
            );
        }

        // 6. Sample Outgoing Webhook Endpoint
        WebhookEndpoint::firstOrCreate(
            ['url' => 'https://httpbin.org/post'],
            [
                'secret' => 'whsec_test_signature_secret_9988',
                'event' => 'order.created',
                'is_active' => true,
            ]
        );

        // 7. Seed sample orders
        $firstProduct = Product::where('slug', 'mechanical-wireless-keyboard-pro')->first();
        $secondProduct = Product::where('slug', 'minimalist-oak-desk-lamp')->first();

        if ($firstProduct && $secondProduct) {
            $order = Order::firstOrCreate(
                ['order_number' => 'ORD-20261029-1001'],
                [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'shipping_address' => '742 Evergreen Terrace',
                    'shipping_city' => 'Springfield',
                    'shipping_postal_code' => '97477',
                    'shipping_phone' => '+1-555-0199',
                    'total_amount' => round($firstProduct->price + ($secondProduct->price * 2), 2),
                    'status' => Order::STATUS_PROCESSING,
                    'payment_method' => 'card',
                    'payment_status' => Order::PAYMENT_PAID,
                    'notes' => 'Please leave package by the front door.',
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $firstProduct->id],
                [
                    'store_id' => $firstProduct->store_id,
                    'product_name' => $firstProduct->name,
                    'price' => $firstProduct->price,
                    'quantity' => 1,
                    'subtotal' => $firstProduct->price,
                    'status' => 'processing',
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $secondProduct->id],
                [
                    'store_id' => $secondProduct->store_id,
                    'product_name' => $secondProduct->name,
                    'price' => $secondProduct->price,
                    'quantity' => 2,
                    'subtotal' => round($secondProduct->price * 2, 2),
                    'status' => 'pending',
                ]
            );
        }
    }
}
