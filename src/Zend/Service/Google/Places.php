<?php

class Zend_Service_Google_Places
{
    private $_url = 'https://maps.googleapis.com/maps/api/place/search/json';

    private $_options = array(
        'key' => 'AIzaSyCPujSL3HhCoYjtkNZtokhlu3KnXL-9yiU',
        'location' => '51.6700563,39.2513476',
        'radius' => '50000',
        //'types' => 'bar',
        'sensor' => 'false'
    );

    public function request()
    {
        $url = $this->_url  . '?' . http_build_query($this->_options);
        $resp = file_get_contents($url);
        $resp = json_decode($resp);
        print_r($resp);
    }

    public function request2()
    {
        $options = array(
            'key' => 'AIzaSyCPujSL3HhCoYjtkNZtokhlu3KnXL-9yiU',
            'reference' => 'CoQBfAAAAFlqDML6FyFKzlvOIc6le0a2_6zHYqZ4IHr9jjbanwbNCLYPyDKBIKO8RjDB1F-3kp45636rB0oi7-VUgzxmswMdEzxXPTPiZox_3pJ0B2gK7D1chlZ6mYdeutZt3n7JEM1be4JNneqmJrZoS10HW0xDzXZ_upmRGWtqHb31jSbrEhAceTdWEbJJGnIkXnE1S9hEGhR2LAuR1k5RSkaUUiy_b44-_5xDdQ',
            'sensor' => 'false',
            'language' => 'ru'
        );
        $url = 'https://maps.googleapis.com/maps/api/place/details/json'  . '?' . http_build_query($options);

        $resp = file_get_contents($url);
        $resp = json_decode($resp);
        print_r($resp);
    }

    // http://maps.google.com/maps/place?cid=4248644972632693132&ie=UTF8&view=feature&mcsrc=photo&output=json&num=21&start=0&callback=photosCallback0

    // http://maps.yandex.ru/actions/get-search/?lang=ru-RU&text=%D1%80%D0%B5%D1%81%D1%82%D0%BE%D1%80%D0%B0%D0%BD%D1%8B%2C+%D0%BA%D0%B0%D1%84%D0%B5&where=&ll=39.203436%2C51.671042&spn=0.123253%2C0.039813&z=14&results=20&skip=100&source=pager
    // http://maps.yandex.ru/sprav/1081122316/

}