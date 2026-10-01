<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Public contact details, edited in /admin → Settings → Contacts and stored
 * in the `settings` table. Builds the ready-to-use links (mailto:, tel:,
 * wa.me, t.me) so views never assemble them by hand.
 */
class Contacts
{
    public const KEYS = [
        'contact_email',
        'contact_phone',
        'contact_whatsapp',
        'contact_telegram',
        'contact_address_uz',
        'contact_address_ru',
    ];

    /** @return array<string, string|null> */
    public static function all(): array
    {
        return once(function () {
            $s = Setting::getMany(self::KEYS);
            $locale = app()->getLocale() === 'ru' ? 'ru' : 'uz';
            $digits = fn (?string $v) => $v ? preg_replace('/\D+/', '', $v) : null;

            return [
                'email' => $s['contact_email'],
                'phone' => $s['contact_phone'],
                'whatsapp' => $s['contact_whatsapp'],
                'telegram' => $s['contact_telegram'],
                'address' => $s["contact_address_{$locale}"] ?: null,
                'email_url' => $s['contact_email'] ? 'mailto:'.$s['contact_email'] : null,
                'phone_url' => $s['contact_phone'] ? 'tel:+'.$digits($s['contact_phone']) : null,
                'whatsapp_url' => $s['contact_whatsapp'] ? 'https://wa.me/'.$digits($s['contact_whatsapp']) : null,
                'telegram_url' => $s['contact_telegram'] ? 'https://t.me/'.$s['contact_telegram'] : null,
            ];
        });
    }

    /**
     * Main "request a price" link: email (with the subject pre-filled), else
     * Telegram, else WhatsApp (with the message pre-filled), else the footer.
     */
    public static function primaryUrl(?string $subject = null): string
    {
        $c = self::all();

        if ($c['email_url']) {
            return $c['email_url'].($subject ? '?subject='.rawurlencode($subject) : '');
        }
        if ($c['telegram_url']) {
            return $c['telegram_url'];
        }
        if ($c['whatsapp_url']) {
            return $c['whatsapp_url'].($subject ? '?text='.rawurlencode($subject) : '');
        }

        return url('/').'#contact';
    }
}
