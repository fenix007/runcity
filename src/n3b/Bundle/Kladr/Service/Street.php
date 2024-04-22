<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 23.04.17
 * Time: 15:15
 */

namespace n3b\Bundle\Kladr\Service;


class Street
{
    const SOCR =[
        '',
        'ул',
        'просек',
        'проезд',
        'пр-кт',
        'б-р',
        'ш',
        'пер',
        'д',
        'аллея',
        'кв-л',
        'туп',
        'наб',
        'пл',
        'линия',
        'нп',
        'мкр',
        'просека',
        'городок',
        'платф',
        'тер',
        'км',
        'ст',
        'парк',
        'снт',
        'дор',
        'п',
        'проселок'
    ];
    
    const SOCR_REPLACES = [
        'б-р' => [
            'бульвар',
            'бульв'
        ],
        'наб' => [
            'набережная'
        ],
        'ш' => [
            'шоссе'
        ],
        'пр-кт' => [
            'проспект',
            'просп'
        ],
        'ул' => [
            'улица'
        ],
        'пл' => [
            'площадь'
        ],
        'пер' => [
            'переулок'
        ],
        'туп' => [
            'тупик'
        ]
    ];

    const REPLACEMENTS = [
        'Большой' => 'Б.',
        'Большая' => 'Б.',
        'Малый' => 'М.',
        'Малая' => 'М.',
        'Нижний' => 'Нижн.',
        'Нижняя' => 'Нижн.',
        'Верхний' => 'Верхн.',
        'Верхняя' => 'Верхн.',
    ];
    
    public static function parseStreetName($streetName)
    {
        $streetInfo = [
            'title' => '',
            'socr' => 'ул'
        ];
        
        $streetName = str_replace(['.', '.'], '', $streetName);
        foreach (self::SOCR_REPLACES as $to => $from) {
            $streetName = str_replace($from, $to, $streetName);
        }
        
        $streetAr = explode(' ', $streetName);
        foreach ($streetAr as $streetPart) {
            if (in_array($streetPart, self::SOCR)) {
                $streetInfo['socr'] = $streetPart;
            } else {
                $streetInfo['title'] .= $streetPart . ' ';
            }
        }

        $title = trim($streetInfo['title']);

        foreach (self::REPLACEMENTS as $from => $to) {
            if (strpos($title, $from) !== false) {
                $title = trim(str_replace($title, $from, '')) . ' ' . $to;
            }
        }

        $streetInfo['title'] = $title;
        
        return $streetInfo;
    }
}