<?php



namespace App\Room103Bundle\Utils;

class News
{
    static public function slugify($text)
    {
        $text = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $text);
        $text = preg_replace('/[-\s]+/', '-', $text);
        $text = trim($text, '-');

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }
}