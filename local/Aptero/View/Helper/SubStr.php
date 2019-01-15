<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SubStr extends AbstractHelper
{
    public function __invoke($text, $maxLength = 150, $ending = '...', array $allowTags = array('<br><br/>'))
    {
        if (empty($text)) {
            return '';
        }

        if (!empty($allowTags)) {
            $allowTags = implode('', $allowTags);
            $text      = strip_tags($text, $allowTags);
        }

        $text = str_replace(array('&nbsp;', '  '), '', $text);
        if (empty($text)) {
            return '';
        }

        $text = trim($text, '<br>');
        $text = trim($text, ' ');
        $text = trim($text, '<br/>');

        $strlen = mb_strlen($text);

        if ($maxLength > 0 && $maxLength < $strlen) {
            $substr = mb_substr($text, $maxLength, $strlen);

            $pos = mb_strpos($substr, ' ', 0);

            if ($pos !== false) {
                $text = mb_substr($text, 0, ($maxLength + $pos));
            }

            $text .= $ending;
        }

        return $text;
    }
}