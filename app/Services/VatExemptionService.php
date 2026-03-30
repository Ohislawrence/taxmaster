<?php

namespace App\Services;

/**
 * VAT Exemption Service
 *
 * Manages VAT exemptions per Nigerian VAT Act and Finance Acts 2019/2020
 * Source: FIRS VAT exempt goods and services
 * Reference: https://portal.citn.org/administration-of-value-added-tax-in-nigeria-goods-and-services-exempt/blog/
 */
class VatExemptionService
{
    /**
     * Get all VAT exempt categories for goods
     * Per Section 3 of VAT Act and Finance Acts 2019/2020
     */
    public static function getExemptGoodsCategories(): array
    {
        return [
            'medical_pharmaceutical' => 'All medical and pharmaceutical products',
            'basic_food_items' => 'Basic food items (unprocessed staples)',
            'books_educational_materials' => 'Books and educational materials',
            'baby_products' => 'Baby products',
            'agricultural_inputs' => 'Fertilizer, agricultural/veterinary medicine, farming machinery & equipment',
            'exported_goods' => 'All exported goods',
            'epz_equipment' => 'Plant and machinery for Export Processing Zone',
            'gas_equipment' => 'Equipment for gas utilization in downstream petroleum',
            'agricultural_equipment' => 'Tractors, ploughs, agricultural implements',
            'sanitary_products' => 'Locally manufactured sanitary towels, pads, tampons',
            'commercial_aircraft' => 'Commercial aircraft, engines, and spare parts',
        ];
    }

    /**
     * Get all VAT exempt categories for services
     * Per Section 3 of VAT Act and Finance Acts 2019/2020
     */
    public static function getExemptServicesCategories(): array
    {
        return [
            'medical_services' => 'Medical services',
            'microfinance_services' => 'Services by Microfinance Banks, People\'s Banks, Mortgage Institutions',
            'educational_performances' => 'Plays and performances by educational institutions',
            'exported_services' => 'All exported services',
            'tuition' => 'Tuition (nursery, primary, secondary, tertiary education)',
            'airline_tickets' => 'Airline tickets issued by Nigerian commercial airlines',
            'agricultural_rental' => 'Hire/rental/lease of agricultural equipment',
        ];
    }

    /**
     * Get all VAT exempt categories (goods + services combined)
     */
    public static function getAllExemptCategories(): array
    {
        return array_merge(
            self::getExemptGoodsCategories(),
            self::getExemptServicesCategories()
        );
    }

    /**
     * Get basic food items definition
     * Per Finance Act 2019 clarifications
     */
    public static function getBasicFoodItems(): array
    {
        return [
            'honey' => 'Honey (additive)',
            'bread' => 'Bread',
            'cereals' => 'Cereals',
            'cooking_oils' => 'Cooking oils',
            'culinary_herbs' => 'Culinary herbs',
            'fish' => 'Fish (fresh, frozen, or processed)',
            'flour_starch' => 'Flour and starch',
            'fruits' => 'Fruits (fresh or dried)',
            'meat_poultry' => 'Live or raw meat and poultry',
            'milk' => 'Milk',
            'nuts' => 'Nuts',
            'pulses' => 'Pulses (beans, lentils, peas)',
            'roots' => 'Roots (yam, cassava, potatoes)',
            'salt' => 'Salt',
            'vegetables' => 'Vegetables',
            'water' => 'Water (natural water and table water)',
        ];
    }

    /**
     * Check if a business qualifies for VAT exemption
     * Businesses with turnover < ₦25M are exempt per Finance Act 2020
     */
    public static function qualifiesForTurnoverExemption(float $annualRevenue): bool
    {
        return $annualRevenue < 25000000; // ₦25 million threshold
    }

    /**
     * Validate VAT exempt category
     */
    public static function isValidExemptCategory(string $category): bool
    {
        return array_key_exists($category, self::getAllExemptCategories());
    }

    /**
     * Get category display name
     */
    public static function getCategoryDisplayName(string $category): string
    {
        $allCategories = self::getAllExemptCategories();
        return $allCategories[$category] ?? $category;
    }

    /**
     * Get exemption guidance text
     */
    public static function getExemptionGuidance(): string
    {
        return 'Per Nigerian VAT Act and Finance Acts 2019/2020, certain goods and services are exempt from VAT. '
             . 'Businesses with annual turnover below ₦25 million are also exempt from VAT registration. '
             . 'Exempt items attract neither input nor output VAT.';
    }

    /**
     * Check if transaction should be VAT exempt based on description/category
     * This is a helper for auto-detection
     */
    public static function detectExemptCategory(string $description, ?string $category = null): ?string
    {
        $description = strtolower($description);

        // Medical/pharmaceutical
        if (str_contains($description, 'medical') || str_contains($description, 'hospital')
            || str_contains($description, 'pharmacy') || str_contains($description, 'drug')) {
            return 'medical_pharmaceutical';
        }

        // Food items
        if (str_contains($description, 'food') || str_contains($description, 'bread')
            || str_contains($description, 'rice') || str_contains($description, 'milk')) {
            return 'basic_food_items';
        }

        // Educational
        if (str_contains($description, 'school') || str_contains($description, 'tuition')
            || str_contains($description, 'education') || str_contains($description, 'books')) {
            return str_contains($description, 'tuition') ? 'tuition' : 'books_educational_materials';
        }

        // Exports
        if (str_contains($description, 'export')) {
            return 'exported_goods';
        }

        return null;
    }
}
