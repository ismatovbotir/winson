<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Whitelist HTML sanitizer for article bodies written in the admin rich-text
 * editor. Everything not listed is dropped: unknown tags are unwrapped (their
 * text kept), dangerous ones (script, style, iframe…) removed with content,
 * and every attribute except the ones below is stripped. Links must pass
 * SafeUrl; images must come from our own uploads or static images.
 */
class RichText
{
    /** tag => allowed attributes */
    private const ALLOWED = [
        'p' => [], 'br' => [], 'h2' => [], 'h3' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'hr' => [],
        'a' => ['href'], 'img' => ['src', 'alt'],
    ];

    /** Removed together with everything inside them. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
        'textarea', 'select', 'svg', 'math', 'template', 'noscript', 'link', 'meta', 'head', 'title'];

    /** Where inline images live (see Admin\ArticleImageController). */
    public const UPLOAD_DIR = 'articles/inline';

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        // Quill's empty paragraphs.
        $out = preg_replace('~<p>(\s|<br>)*</p>~u', '', $out);

        return trim($out);
    }

    /** Plain-text version (for excerpts, reading time, search). */
    public static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * Paths (relative to the public disk) of uploaded images referenced by
     * this HTML, e.g. "articles/inline/abc.jpg".
     *
     * @return array<int, string>
     */
    public static function uploadedImages(?string $html): array
    {
        preg_match_all('~<img[^>]+src="/storage/('.preg_quote(self::UPLOAD_DIR, '~').'/[A-Za-z0-9._-]+)"~u', (string) $html, $m);

        return array_values(array_unique($m[1]));
    }

    private static function walk(DOMNode $node): void
    {
        // Iterate over a copy — we mutate the child list.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }
            if (! $child instanceof DOMElement) {
                continue; // text nodes are escaped by saveHTML
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Unwrap: keep children, drop the element itself.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                if (! in_array(strtolower($attr->name), self::ALLOWED[$tag], true)) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if (! SafeUrl::isSafe($href)) {
                    // Unsafe link → plain text.
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }
                if (preg_match('~^https?://~i', $href)) {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }

            if ($tag === 'img') {
                $src = self::ownImagePath($child->getAttribute('src'));
                $src === null ? $node->removeChild($child) : $child->setAttribute('src', $src);
            }
        }
    }

    /**
     * Site-relative path of an allowed image (our uploads under
     * /storage/articles/inline or static /images files), or null.
     */
    private static function ownImagePath(string $src): ?string
    {
        $src = preg_replace('~^'.preg_quote(rtrim(config('app.url'), '/'), '~').'~', '', trim($src));

        return preg_match('~^/(storage/'.preg_quote(self::UPLOAD_DIR, '~').'|images)/[A-Za-z0-9._/-]+\.(jpe?g|png|webp|svg)$~i', $src) && ! str_contains($src, '..')
            ? $src
            : null;
    }
}
