<?php

class data_test {
    public function __construct() {}

    public function get_test_datas_for_user() {
        return [
            [
                'id' => 0,
                'nom' => 'Choquet',
                'prenom' => 'Nicolas',
                'adresse' => '1102 ch de l\'espagnol',
                'adresse2' => 'Les privevères',
                'code_postal' => '06250',
                'ville' => 'Mougins',
                'date_naissence' => '1995-07-21',
                'date_inscription' => date('Y-m-d'),
            ],
            [
                'id' => 1,
                'nom' => 'Choquet',
                'prenom' => 'Yann',
                'adresse' => '1102 ch de l\'espagnol',
                'adresse2' => 'Les privevères',
                'code_postal' => '06250',
                'ville' => 'Mougins',
                'date_naissence' => '1998-04-12',
                'date_inscription' => date('Y-m-d'),
            ],
            [
                'id' => 2,
                'nom' => 'Choquet',
                'prenom' => 'André',
                'adresse' => '1102 ch de l\'espagnol',
                'adresse2' => 'Les privevères',
                'code_postal' => '06250',
                'ville' => 'Mougins',
                'date_naissence' => '1995-07-21',
                'date_inscription' => date('Y-m-d'),
            ],
        ];
    }

    public function get_test_datas_for_slider() {
    	return [
    		[
    			'id' => 0,
				'name' => 'Slider 1',
				'src' => 'http://toto.sliders.com/1',
				'alt' => 'Slider 1',
				'poster' => 1,
				'date_post' => date('Y-m-d'),
			],
			[
				'id' => 1,
				'name' => 'Slider 2',
				'src' => 'http://toto.sliders.com/2',
				'alt' => 'Slider 2',
				'poster' => 0,
				'date_post' => date('Y-m-d'),
			],
			[
				'id' => 2,
				'name' => 'Slider 3',
				'src' => 'http://toto.sliders.com/3',
				'alt' => 'Slider 3',
				'poster' => 2,
				'date_post' => date('Y-m-d'),
			],
		];
	}
}