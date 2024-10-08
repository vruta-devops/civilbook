<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Models\SubCategory;

class SubCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        $checkSubCategory= SubCategory::first();

        if (empty($checkSubCategory))
        {
            $data = [
                [
                'category_id' => 9,
                'created_at' => '2023-09-04 13:08:30',
                'deleted_at' => NULL,
                'description' => 'Explore step-by-step guides and tips for correctly installing and setting up air conditioning units in various environments, ensuring optimal cooling performance.',
                'id' => 1,
                'is_featured' => 1,
                'name' => 'AC Installation and Setup',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/ac_coolcare/ac_installation_and_setup.png'),
                'updated_at' => '2023-09-04 13:16:02',
            ],
            [
                'category_id' => 9,
                'created_at' => '2023-09-04 13:09:08',
                'deleted_at' => NULL,
                'description' => 'Learn essential techniques for regular maintenance, cleaning, and upkeep of air conditioning systems to improve efficiency, extend lifespan, and maintain air quality.',
                'id' => 2,
                'is_featured' => 0,
                'name' => 'AC Maintenance and Cleaning',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/ac_coolcare/ac_maintenance_and_cleaning.png'),
                'updated_at' => '2023-09-04 13:09:08',
            ],
            [
                'category_id' => 21,
                'created_at' => '2023-09-04 13:12:12',
                'deleted_at' => NULL,
                'description' => 'Keep your vehicle in optimal condition. 🚛🚙',
                'id' => 3,
                'is_featured' => 1,
                'name' => 'Maintenance and Servicing',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/automotive_care/maintenance_and_servicing.png'),
                'updated_at' => '2023-09-04 13:16:02',
            ],
            [
                'category_id' => 21,
                'created_at' => '2023-09-04 13:13:52',
                'deleted_at' => NULL,
                'description' => 'Swift solutions for vehicle issues. 🚒',
                'id' => 4,
                'is_featured' => 0,
                'name' => 'Repairs and Diagnostics',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/automotive_care/repairs_and_diagnostics.png'),
                'updated_at' => '2023-09-04 13:13:52',
            ],
            [
                'category_id' => 26,
                'created_at' => '2023-09-04 13:15:27',
                'deleted_at' => NULL,
                'description' => 'Learn the art of crafting functional and aesthetically pleasing furniture pieces through woodworking techniques . 🪚 🔨',
                'id' => 5,
                'is_featured' => 1,
                'name' => 'Furniture Carpentry',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/carpenter/furniture_carpentry.png'),
                'updated_at' => '2023-09-04 13:16:06',
            ],
            [
                'category_id' => 26,
                'created_at' => '2023-09-04 13:15:57',
                'deleted_at' => NULL,
                'description' => 'Dive into the construction side of carpentry, focusing on building frameworks, roofs, and structural elements for various projects. 🔨',
                'id' => 6,
                'is_featured' => 0,
                'name' => 'Structural Carpentry',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/carpenter/structural_carpentry.png'),
                'updated_at' => '2023-09-04 13:15:57',
            ],
            [
                'category_id' => 24,
                'created_at' => '2023-09-04 13:17:55',
                'deleted_at' => NULL,
                'description' => 'Comprehensive cleaning solutions for both residential spaces and professional workplaces, ensuring cleanliness and comfort.  🧽🧹🧻',
                'id' => 7,
                'is_featured' => 1,
                'name' => 'House & Office Cleaning',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cleaning/house_office_cleaning.png'),
                'updated_at' => '2023-10-06 11:41:29',
            ],
            [
                'category_id' => 24,
                'created_at' => '2023-09-04 13:20:37',
                'deleted_at' => NULL,
                'description' => 'Experience thorough cleaning and revitalization with our comprehensive Carpet, Curtain, Ceiling & Upholstery Cleaning services. 🧽🪣🧼',
                'id' => 8,
                'is_featured' => 1,
                'name' => 'Interior Surface Refresh',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cleaning/interior_surface_refresh.png'),
                'updated_at' => '2023-09-04 13:20:42',
            ],
            [
                'category_id' => 22,
                'created_at' => '2023-09-04 13:24:24',
                'deleted_at' => NULL,
                'description' => 'Experience the warmth of home-cooked meals with our Home-style Cuisine Services, offering daily meals and family-style dinners crafted with care. 🍲🫕',
                'id' => 9,
                'is_featured' => 0,
                'name' => 'Home Style Cuisine Services',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cooking/home_style_cuisine_services.png'),
                'updated_at' => '2023-09-04 13:24:24',
            ],
            [
                'category_id' => 22,
                'created_at' => '2023-09-04 13:25:04',
                'deleted_at' => NULL,
                'description' => 'Experience a global gastronomic journey with our International Culinary Specialists, bringing the world\'s flavors to your plate. 🥙🥗🫔🍕🥘',
                'id' => 10,
                'is_featured' => 0,
                'name' => 'International Culinary Specialists',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cooking/international_culinary_specialists.png'),
                'updated_at' => '2023-09-04 13:25:04',
            ],
            [
                'category_id' => 25,
                'created_at' => '2023-09-06 13:32:07',
                'deleted_at' => NULL,
                'description' => 'Expert wiring solutions for safety and functionality. 🪛🛠️',
                'id' => 11,
                'is_featured' => 1,
                'name' => 'Wiring and Installation',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/electrician/wiring_and_installation.png'),
                'updated_at' => '2023-09-06 13:39:44',
            ],
            [
                'category_id' => 25,
                'created_at' => '2023-09-06 13:32:57',
                'deleted_at' => NULL,
                'description' => 'Illuminate your space with our lighting solutions. 🔩🔧',
                'id' => 12,
                'is_featured' => 0,
                'name' => 'Lighting Fixtures',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/electrician/lighting_fixtures.png'),
                'updated_at' => '2023-09-06 13:32:57',
            ],
            [
                'category_id' => 20,
                'created_at' => '2023-09-06 13:35:23',
                'deleted_at' => NULL,
                'description' => 'Transform your outdoor space with creative landscape designs. 🌲🪴🥀🌻🌴🌾',
                'id' => 13,
                'is_featured' => 1,
                'name' => 'Garden Maintenance',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/gardener/garden_maintenance.png'),
                'updated_at' => '2023-09-11 10:46:11',
            ],
            [
                'category_id' => 20,
                'created_at' => '2023-09-06 13:36:15',
                'deleted_at' => NULL,
                'description' => 'Maintain a lush and healthy lawn with expert care. ⛏️🪴🥀',
                'id' => 14,
                'is_featured' => 0,
                'name' => 'Lawn Care',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/gardener/lawn_care.png'),
                'updated_at' => '2023-09-08 08:10:19',
            ],
            [
                'category_id' => 18,
                'created_at' => '2023-09-06 13:38:23',
                'deleted_at' => NULL,
                'description' => 'Transform your indoor spaces with expert interior painting. 🖌️',
                'id' => 15,
                'is_featured' => 1,
                'name' => 'Interior Painting',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/painter/interior_painting.png'),
                'updated_at' => '2023-09-06 13:39:41',
            ],
            [
                'category_id' => 18,
                'created_at' => '2023-09-06 13:39:34',
                'deleted_at' => NULL,
                'description' => 'Renew your property\'s exterior with professional painting. 🖌️',
                'id' => 16,
                'is_featured' => 0,
                'name' => 'Exterior Painting',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/painter/exterior_painting.png'),
                'updated_at' => '2023-09-06 13:39:34',
            ],
            [
                'category_id' => 17,
                'created_at' => '2023-09-06 13:41:22',
                'deleted_at' => NULL,
                'description' => 'Traditional Vedic rituals for auspicious occasions. 🔥',
                'id' => 17,
                'is_featured' => 0,
                'name' => 'Vedic Rituals',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/pandit/vedic_rituals.png'),
                'updated_at' => '2023-09-06 13:41:22',
            ],
            [
                'category_id' => 17,
                'created_at' => '2023-09-06 13:42:25',
                'deleted_at' => NULL,
                'description' => 'Insights from experienced astrologers to guide your life\'s journey. 🔯',
                'id' => 18,
                'is_featured' => 0,
                'name' => 'Astrology Consultation',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/pandit/astrology_consultation.png'),
                'updated_at' => '2023-09-06 13:43:04',
            ],
            [
                'category_id' => 16,
                'created_at' => '2023-09-08 05:45:04',
                'deleted_at' => NULL,
                'description' => 'Embrace environmentally friendly solutions with our biological pest control services. We harness nature\'s predators to combat pests and maintain a balanced ecosystem.🧴',
                'id' => 19,
                'is_featured' => 0,
                'name' => 'Biological Control',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/pest_control/biological_pest_control.png'),
                'updated_at' => '2023-09-08 05:45:04',
            ],
            [
                'category_id' => 16,
                'created_at' => '2023-09-08 05:46:04',
                'deleted_at' => NULL,
                'description' => 'Our mechanical pest control solutions provide effective and environmentally friendly methods to keep unwanted pests at bay. We focus on non-chemical techniques to ensure your space remains pest-free.🚿',
                'id' => 20,
                'is_featured' => 0,
                'name' => 'Mechanical Pest Control',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/pest_control/mechanical_pest_control.png'),
                'updated_at' => '2023-09-08 05:46:04',
            ],
            [
                'category_id' => 15,
                'created_at' => '2023-09-08 05:51:45',
                'deleted_at' => NULL,
                'description' => 'Capture your essence in stunning portraits. 📸',
                'id' => 21,
                'is_featured' => 1,
                'name' => 'Portrait Photography',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/photography/portrait_photography.png'),
                'updated_at' => '2023-09-08 05:52:56',
            ],
            [
                'category_id' => 15,
                'created_at' => '2023-09-08 05:52:06',
                'deleted_at' => NULL,
                'description' => 'Capture the moments that matter most during your events.📷',
                'id' => 22,
                'is_featured' => 0,
                'name' => 'Event Photography',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/photography/event_photography.png'),
                'updated_at' => '2023-09-08 05:52:06',
            ],
            [
                'category_id' => 23,
                'created_at' => '2023-09-08 05:59:54',
                'deleted_at' => NULL,
                'description' => 'Swift solutions for plumbing issues. 🔧🪛🛠️',
                'id' => 23,
                'is_featured' => 1,
                'name' => 'Plumbing Repairs',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/plumber/plumbing_repairs.png'),
                'updated_at' => '2023-09-08 06:11:39',
            ],
            [
                'category_id' => 23,
                'created_at' => '2023-09-08 06:00:39',
                'deleted_at' => NULL,
                'description' => 'Expert installation and upgrades for plumbing systems. 🪛🛠️',
                'id' => 24,
                'is_featured' => 0,
                'name' => 'Installation and Upgrades',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/plumber/installation_and_upgrades.png'),
                'updated_at' => '2023-09-08 06:00:39',
            ],
            [
                'category_id' => 14,
                'created_at' => '2023-09-08 06:09:51',
                'deleted_at' => NULL,
                'description' => 'Transform your look with expert hair styling. ✂️💇🏻',
                'id' => 25,
                'is_featured' => 1,
                'name' => 'Hair Styling',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/salon/hair_styling.png'),
                'updated_at' => '2023-09-08 06:11:40',
            ],
            [
                'category_id' => 14,
                'created_at' => '2023-09-08 06:10:22',
                'deleted_at' => NULL,
                'description' => 'Rejuvenate your skin with tailored treatments. 🧖‍♀️',
                'id' => 26,
                'is_featured' => 0,
                'name' => 'Skin Care and Facials',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/salon/skin_care_and_facials.png'),
                'updated_at' => '2023-09-08 06:10:22',
            ],
            [
                'category_id' => 13,
                'created_at' => '2023-09-08 06:14:35',
                'deleted_at' => NULL,
                'description' => 'Comprehensive sanitization for a hygienic home environment. 🧴',
                'id' => 27,
                'is_featured' => 1,
                'name' => 'Residential Sanitization',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/sanitization/residential_sanitization.png'),
                'updated_at' => '2023-09-08 06:15:42',
            ],
            [
                'category_id' => 13,
                'created_at' => '2023-09-08 06:15:35',
                'deleted_at' => NULL,
                'description' => 'Professional sanitization for a hygienic workplace. 🚿',
                'id' => 28,
                'is_featured' => 0,
                'name' => 'Commercial Sanitization',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/sanitization/commercial_sanitization.png'),
                'updated_at' => '2023-09-08 06:15:35',
            ],
            [
                'category_id' => 12,
                'created_at' => '2023-09-08 06:24:18',
                'deleted_at' => NULL,
                'description' => 'Ensure personal safety with professional security solutions. 👮🏻',
                'id' => 29,
                'is_featured' => 0,
                'name' => 'Personal Protection',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/security_guard/personal_protection.png'),
                'updated_at' => '2023-09-08 06:24:18',
            ],
            [
                'category_id' => 12,
                'created_at' => '2023-09-08 06:25:09',
                'deleted_at' => NULL,
                'description' => 'Safeguard properties with effective security measures. 👮🏻‍♀️',
                'id' => 30,
                'is_featured' => 0,
                'name' => 'Property Security',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/security_guard/property_security.png'),
                'updated_at' => '2023-09-08 06:25:09',
            ],
            [
                'category_id' => 11,
                'created_at' => '2023-09-08 07:55:09',
                'deleted_at' => NULL,
                'description' => 'Transform your home with advanced automation. 📱',
                'id' => 31,
                'is_featured' => 1,
                'name' => 'Home Automation',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/smart_home/home_automation.png'),
                'updated_at' => '2023-09-08 08:24:33',
            ],
            [
                'category_id' => 11,
                'created_at' => '2023-09-08 07:57:27',
                'deleted_at' => NULL,
                'description' => 'Elevate your home entertainment experience. 🎥',
                'id' => 32,
                'is_featured' => 0,
                'name' => 'Entertainment and Media',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/smart_home/entertainment_and_media.png'),
                'updated_at' => '2023-09-08 07:57:27',
            ],
            [
                'category_id' => 10,
                'created_at' => '2023-09-08 07:59:02',
                'deleted_at' => NULL,
                'description' => 'Perfect fits with expert alterations. 👗👕👖',
                'id' => 33,
                'is_featured' => 0,
                'name' => 'Clothing Alterations',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/tailor/clothing_alterations.png'),
                'updated_at' => '2023-09-08 07:59:02',
            ],
            [
                'category_id' => 10,
                'created_at' => '2023-09-08 08:02:37',
                'deleted_at' => NULL,
                'description' => 'Unique designs tailored just for you. 👘👚🥻',
                'id' => 34,
                'is_featured' => 0,
                'name' => 'Custom Clothing',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/tailor/custom_clothing.png'),
                'updated_at' => '2023-09-08 08:02:37',
            ],
            [
                'category_id' => 13,
                'created_at' => '2023-09-08 08:03:37',
                'deleted_at' => NULL,
                'description' => 'Tailored sanitization solutions for specific needs. 🧴',
                'id' => 35,
                'is_featured' => 0,
                'name' => 'Specialized Sanitization',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/sanitization/specialized_sanitization.png'),
                'updated_at' => '2023-09-08 08:03:37',
            ],
            [
                'category_id' => 18,
                'created_at' => '2023-09-08 08:06:32',
                'deleted_at' => NULL,
                'description' => 'Elevate spaces with unique and artistic finishes. 🎨🖌️',
                'id' => 36,
                'is_featured' => 0,
                'name' => 'Specialty Finishes',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/painter/specialty_finishes.png'),
                'updated_at' => '2023-09-08 08:06:32',
            ],
            [
                'category_id' => 19,
                'created_at' => '2023-09-08 08:07:35',
                'deleted_at' => NULL,
                'description' => 'Tailored care for unique items.  👘👚🥻',
                'id' => 37,
                'is_featured' => 0,
                'name' => 'Special Garment Care',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/laundry/special_garment_care.png'),
                'updated_at' => '2023-09-08 08:07:35',
            ],
            [
                'category_id' => 19,
                'created_at' => '2023-09-08 08:08:41',
                'deleted_at' => NULL,
                'description' => 'Restore elegance and quality to your wardrobe.  👗👔👖',
                'id' => 38,
                'is_featured' => 0,
                'name' => 'Dry Cleaning',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/laundry/dry_cleaning.png'),
                'updated_at' => '2023-09-08 08:08:41',
            ],
            [
                'category_id' => 21,
                'created_at' => '2023-09-08 08:11:19',
                'deleted_at' => NULL,
                'description' => 'Enhance your vehicle\'s aesthetics. 🚗',
                'id' => 40,
                'is_featured' => 0,
                'name' => 'Detailing and Appearance',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/automotive_care/detailing_and_appearance.png'),
                'updated_at' => '2023-09-08 08:11:19',
            ],
            [
                'category_id' => 10,
                'created_at' => '2023-09-08 08:12:10',
                'deleted_at' => NULL,
                'description' => 'Extend the life of your favorite garments. 👗👔👖',
                'id' => 41,
                'is_featured' => 1,
                'name' => 'Repairs and Restorations',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/tailor/repairs_and_restorations.png'),
                'updated_at' => '2023-09-08 08:19:39',
            ],
            [
                'category_id' => 11,
                'created_at' => '2023-09-08 08:14:50',
                'deleted_at' => NULL,
                'description' => 'Enhance your home\'s security with cutting-edge technology. 📹',
                'id' => 42,
                'is_featured' => 0,
                'name' => 'Security and Surveillance',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/smart_home/security_and_surveillance.png'),
                'updated_at' => '2023-09-08 08:14:50',
            ],
            [
                'category_id' => 14,
                'created_at' => '2023-09-08 08:15:18',
                'deleted_at' => NULL,
                'description' => 'Enhance your nails with professional care. 💅🏼',
                'id' => 43,
                'is_featured' => 1,
                'name' => 'Nail Care',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/salon/nail_care.png'),
                'updated_at' => '2023-09-08 08:19:40',
            ],
            [
                'category_id' => 23,
                'created_at' => '2023-09-08 08:16:19',
                'deleted_at' => NULL,
                'description' => 'Effective solutions for drainage problems. 🛠️🪠',
                'id' => 44,
                'is_featured' => 0,
                'name' => 'Drainage Solutions',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/plumber/drainage_solutions.png'),
                'updated_at' => '2023-09-08 08:16:19',
            ],
            [
                'category_id' => 22,
                'created_at' => '2023-09-08 08:17:07',
                'deleted_at' => NULL,
                'description' => 'Explore the artistry of flavors and textures with our Baking and Pastry creations, where sweet delights come to life. 🎂🧁',
                'id' => 45,
                'is_featured' => 1,
                'name' => 'Baking and Pastry',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cooking/baking_and_pastry.png'),
                'updated_at' => '2023-09-08 08:19:41',
            ],
            [
                'category_id' => 25,
                'created_at' => '2023-09-08 08:18:27',
                'deleted_at' => NULL,
                'description' => 'Swift diagnosis and resolution of electrical issues. 🪛🛠️',
                'id' => 46,
                'is_featured' => 1,
                'name' => 'Electrical Troubleshooting',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/electrician/electrical_troubleshooting.png'),
                'updated_at' => '2023-09-08 08:19:44',
            ],
            [
                'category_id' => 24,
                'created_at' => '2023-09-08 08:19:27',
                'deleted_at' => NULL,
                'description' => 'Revitalize your space with our thorough deep cleaning service. Our expert cleaners target hidden dirt and grime, leaving your surroundings refreshed and rejuvenated. 🧽🧹🧻 🪣🧼',
                'id' => 47,
                'is_featured' => 0,
                'name' => 'Deep Cleaning',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/cleaning/deep_cleaning.png'),
                'updated_at' => '2023-09-08 08:19:27',
            ],
            [
                'category_id' => 26,
                'created_at' => '2023-09-08 08:21:16',
                'deleted_at' => NULL,
                'description' => 'Explore the intricate details of carpentry, including molding, trim work, and fine finishes that add the perfect touch to any woodworking project. 🪚 🔨',
                'id' => 48,
                'is_featured' => 0,
                'name' => 'Finish Carpentry',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/carpenter/finish_carpentry.png'),
                'updated_at' => '2023-09-08 08:21:16',
            ],
            [
                'category_id' => 9,
                'created_at' => '2023-09-08 08:22:02',
                'deleted_at' => NULL,
                'description' => 'Discover troubleshooting methods and solutions to common AC issues, along with guidance on minor repairs, helping you save costs and keep your air conditioner in top working condition. 🪛🛠️',
                'id' => 49,
                'is_featured' => 1,
                'name' => 'AC Troubleshooting and Repairs',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/ac_coolcare/ac_troubleshooting_and_repairs.png'),
                'updated_at' => '2023-09-08 08:24:06',
            ],
            [
                'category_id' => 19,
                'created_at' => '2023-09-09 07:11:40',
                'deleted_at' => NULL,
                'description' => 'Convenience and freshness in every load. 👖👕👗',
                'id' => 50,
                'is_featured' => 0,
                'name' => 'Wash and Fold',
                'status' => 1,
                'subcategory_image' => public_path('/images/subcategory-images/laundry/wash_and_fold.png'),
                'updated_at' => '2023-09-09 07:11:40',
            ],
        ];

        foreach ($data as $key => $val) {
            $featureImage = $val['subcategory_image'] ?? null;
            $subCategoryData = Arr::except($val, ['subcategory_image']);
            $sub_category = SubCategory::create($subCategoryData);
            if (isset($featureImage)) {
                $this->attachFeatureImage($sub_category, $featureImage);
            }

        }
    }
    }
    private function attachFeatureImage($model, $publicPath)
    {

        $file = new \Illuminate\Http\File($publicPath);

        $media = $model->addMedia($file)->preservingOriginal()->toMediaCollection('subcategory_image');

        return $media;

    }
}
