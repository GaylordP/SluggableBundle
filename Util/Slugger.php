<?php

namespace GaylordP\SluggableBundle\Util;

class Slugger
{
    public static function slugify(string $text): string
    {
        $text = trim(strip_tags($text));
        $text = transliterator_transliterate(
            'NFD; [:Nonspacing Mark:] Remove; NFC; Any-Latin; Latin-ASCII; Lower();',
            $text
        );
        $text = preg_replace("/[^a-zA-Z0-9.\/_|+ -]/", '', $text);
        $text = preg_replace("/[.\/_|+ -]+/", '-', $text);
        $text = trim($text, '-');

        return $text;
    }
}
