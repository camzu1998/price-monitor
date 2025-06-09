<?php

namespace App\Enums;

enum SourceName: string
{
    case AMAZON = 'amazon';
    case ALLEGRO = 'allegro';
    case CENEO = 'ceneo';
    case EURO_RTV_AGD = 'euro_rtv_agd';
    case MEDIA_MARKT = 'media_markt';
    case X_KOM = 'x_kom';
    case MORELE = 'morele';
    case KOMPUTRONIK = 'komputronik';
    case NEONET = 'neonet';
    case EMPIK = 'empik';
    case ZALANDO = 'zalando';
    case OLX = 'olx';
    case SHOPEE = 'shopee';
    case EBAY = 'ebay';
    case ALIEXPRESS = 'aliexpress';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match($this) {
            self::AMAZON => 'Amazon',
            self::ALLEGRO => 'Allegro',
            self::CENEO => 'Ceneo',
            self::EURO_RTV_AGD => 'Euro RTV AGD',
            self::MEDIA_MARKT => 'Media Markt',
            self::X_KOM => 'x-kom',
            self::MORELE => 'Morele',
            self::KOMPUTRONIK => 'Komputronik',
            self::NEONET => 'Neonet',
            self::EMPIK => 'Empik',
            self::ZALANDO => 'Zalando',
            self::OLX => 'OLX',
            self::SHOPEE => 'Shopee',
            self::EBAY => 'eBay',
            self::ALIEXPRESS => 'AliExpress',
            self::CUSTOM => 'Custom Source',
        };
    }

    public function getDomain(): string
    {
        return match($this) {
            self::AMAZON => 'amazon.com',
            self::ALLEGRO => 'allegro.pl',
            self::CENEO => 'ceneo.pl',
            self::EURO_RTV_AGD => 'euro.com.pl',
            self::MEDIA_MARKT => 'mediamarkt.pl',
            self::X_KOM => 'x-kom.pl',
            self::MORELE => 'morele.net',
            self::KOMPUTRONIK => 'komputronik.pl',
            self::NEONET => 'neonet.pl',
            self::EMPIK => 'empik.com',
            self::ZALANDO => 'zalando.pl',
            self::OLX => 'olx.pl',
            self::SHOPEE => 'shopee.pl',
            self::EBAY => 'ebay.com',
            self::ALIEXPRESS => 'aliexpress.com',
            self::CUSTOM => '',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::AMAZON => '#FF9900',
            self::ALLEGRO => '#FF5A00',
            self::CENEO => '#007C42',
            self::EURO_RTV_AGD => '#E31837',
            self::MEDIA_MARKT => '#E31837',
            self::X_KOM => '#FF6600',
            self::MORELE => '#0066CC',
            self::KOMPUTRONIK => '#00599C',
            self::NEONET => '#00B04F',
            self::EMPIK => '#F39200',
            self::ZALANDO => '#FF6900',
            self::OLX => '#002F34',
            self::SHOPEE => '#EE4D2D',
            self::EBAY => '#0064D2',
            self::ALIEXPRESS => '#FF6A00',
            self::CUSTOM => '#6B7280',
        };
    }

    public function getDefaultSelectors(): array
    {
        return match($this) {
            self::AMAZON => [
                'price' => '.a-price-whole, .a-offscreen',
                'availability' => '#availability span',
            ],
            self::ALLEGRO => [
                'price' => '[data-testid="price-value"]',
                'availability' => '[data-testid="availability"]',
            ],
            self::CENEO => [
                'price' => '.price-value',
                'availability' => '.product-availability',
            ],
            default => [
                'price' => '.price',
                'availability' => '.availability',
            ]
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getPolishSources(): array
    {
        return [
            self::ALLEGRO,
            self::CENEO,
            self::EURO_RTV_AGD,
            self::MEDIA_MARKT,
            self::X_KOM,
            self::MORELE,
            self::KOMPUTRONIK,
            self::NEONET,
            self::EMPIK,
            self::ZALANDO,
            self::OLX,
        ];
    }

    public static function getInternationalSources(): array
    {
        return [
            self::AMAZON,
            self::SHOPEE,
            self::EBAY,
            self::ALIEXPRESS,
        ];
    }
}
