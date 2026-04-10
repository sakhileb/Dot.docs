<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();

        // Allow TipTap's full output: headings, lists, tables, code, blockquotes, links, images
        $config->set('HTML.Allowed',
            'p,br,strong,em,u,s,code,pre,blockquote,h1,h2,h3,h4,h5,h6,' .
            'ul,ol,li,table,thead,tbody,tr,th,td,a[href|title|target],img[src|alt|width|height],' .
            'span[class],div[class]'
        );

        // Enforce safe href schemes only
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // Force external links to rel="noopener noreferrer"
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('HTML.TargetNoopener', true);

        // Cache directory
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));

        $this->purifier = new HTMLPurifier($config);
    }

    public function clean(string $html): string
    {
        return $this->purifier->purify($html);
    }

    /**
     * Strip all HTML and return plain text (for AI inputs).
     * Enforces a max character length to prevent prompt injection.
     */
    public function toPlainText(string $html, int $maxLength = 12000): string
    {
        $text = strip_tags(htmlspecialchars_decode($this->clean($html)));
        return mb_substr($text, 0, $maxLength);
    }
}
