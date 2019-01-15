<?php
namespace Aptero\String;

class Search
{
    static public function prepareQuery($query) {
        $query = mb_strtolower($query);

        $result = [$query];

        $replace = [
            'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з',
            '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л',
            'l' => 'д', ';' => 'ж', '\'' => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т', 'm' => 'ь',
            ',' => 'б', '.' => 'ю'];

        $result[] = str_replace(array_keys($replace), $replace, $query);
        $result[] = str_replace($replace, array_keys($replace), $query);

        $result[] = Translit::ruToEn($query);
        $result[] = Translit::enToRu($query);

        $tmp = Translit::ruToEn($result[1]);
        if($tmp) {
            $result[] = $tmp;
        }

        return $result;
    }
}