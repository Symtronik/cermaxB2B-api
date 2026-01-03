<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class TranslationsSeeder extends Seeder
{
    public function run()
    {
        // ↓↓↓ Twoje tłumaczenia ↓↓↓
        $translations = [
            "common" => [
                "help" => "Pomoc"
            ],
            "auth" => [
                "sign_in_title" => "Logowanie",
                "sign_in_to_account" => "Zaloguj się do konta",
                "email" => "E-mail",
                "email_placeholder" => "Twój email",
                "password" => "Hasło",
                "password_placeholder" => "••••••••",
                "remember_me" => "Zapamiętaj mnie",
                "forgot_password" => "Nie pamiętasz hasła?",
                "sign_in" => "Zaloguj się",
                "no_account" => "Nie masz konta?",
                "create_account" => "Utwórz konto",
                "email_required" => "Podaj adres e-mail.",
                "email_invalid" => "Nieprawidłowy adres e-mail.",
                "password_required" => "Podaj hasło.",
                "login_failed" => "Logowanie nie powiodło się",
                "panel_subtitle" => "Panel B2B Cermax"
            ],
            "dashboard" => [
                "logout" => "Wyloguj",
                "home" => "Start",
                "clients" => "Klienci",
                "settings" => "Ustawienia",
                "delivery_adress" => "Adresy wysyłki",
                "orders" => "Zamówienia",
                "products" => "Produkty",
                "marketing" => "Marketing",
                "products_list" => "Lista produktów",
                "product_add" => "Dodaj produkt",
                "category_add" => "Dodaj serie",
                "all_categories" => "Wszystkie kategorie",
                "all_products" => "Wszystkie produkty",
                "filter_by_category_placeholder" => "Filtruj po kategorii...",
                "filters_reset_all" => "Wyczyść filtr",
                "series_search_placeholder" => "Wpisz nazwę serii...",
                "series_search_empty" => "Brak serii pasujących do wyszukiwanej frazy.",
                "products_filters_subtitle" => "Zawężaj listę serii za pomocą filtrów poniżej.",
                "filter_chip_category" => "Kategoria",
                "filter_chip_search" => "Szukaj",
                "filters_label" => "Filtry",
                "filters_active" => "aktywne",
                "filters_none" => "brak aktywnych filtrów"
            ]
        ];

        // ↓↓↓ Zapis do DB ↓↓↓
        foreach ($translations as $namespace => $entries) {
            foreach ($entries as $key => $value) {
                Translation::updateOrCreate(
                    [
                        'namespace' => $namespace,
                        'key' => $key,
                        'lang' => 'pl',
                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}
