<?php

namespace App\Services;

class AlgeriaShippingService
{
    /**
     * Grille tarifaire par défaut (estimations basées sur Yalidine / ZR Express en DA).
     */
    private const ZONE_FEES = [
        'alger' => ['home' => 500, 'desk' => 350],
        'centre' => ['home' => 600, 'desk' => 400],
        'nord' => ['home' => 750, 'desk' => 450],
        'est_ouest' => ['home' => 800, 'desk' => 500],
        'hauts_plateaux' => ['home' => 850, 'desk' => 550],
        'sahara_nord' => ['home' => 1050, 'desk' => 700],
        'grand_sud' => ['home' => 1350, 'desk' => 900],
    ];

    /**
     * Répertoire officiel des 58 Wilayas et leurs communes principales.
     */
    private const WILAYAS = [
        1 => [
            'code' => '01',
            'name' => '01 - Adrar',
            'zone' => 'grand_sud',
            'communes' => ['Adrar', 'Tamest', 'Charouine', 'Reggane', 'In Zghmir', 'Tit', 'Ksar Kaddour', 'Tsabit', 'Timokten', 'Zaouiet Kounta', 'Aoulef', 'Fenoughil', 'Tamentit'],
        ],
        2 => [
            'code' => '02',
            'name' => '02 - Chlef',
            'zone' => 'centre',
            'communes' => ['Chlef', 'Ténès', 'El Karimia', 'Oued Fodda', 'Ouled Fares', 'Boukadir', 'Taougrit', 'Beni Haoua', 'Abou El Hassan', 'El Marsa', 'Chettia', 'Sidi Akkacha', 'Zeboudja', 'Oued Sly'],
        ],
        3 => [
            'code' => '03',
            'name' => '03 - Laghouat',
            'zone' => 'hauts_plateaux',
            'communes' => ['Laghouat', 'Ksar El Hirane', 'Bennasser Benchohra', 'Sidi Makhlouf', 'Hassi Delaa', 'Hassi R’Mel', 'Aïn Madhi', 'Tadjmout', 'Kheneg', 'Gueltat Sidi Saad', 'Brida', 'Aflou'],
        ],
        4 => [
            'code' => '04',
            'name' => '04 - Oum El Bouaghi',
            'zone' => 'est_ouest',
            'communes' => ['Oum El Bouaghi', 'Aïn Beïda', 'Aïn M’lila', 'Aïn Fakroun', 'Sigus', 'El Amiria', 'Meshouche', 'Aïn Babouche', 'Ksar Sbahi', 'Fkirina', 'Souk Naamane'],
        ],
        5 => [
            'code' => '05',
            'name' => '05 - Batna',
            'zone' => 'est_ouest',
            'communes' => ['Batna', 'Barika', 'Aïn Touta', 'Merouana', 'Arris', 'Tazoult', 'N’Gaous', 'Chemora', 'Seriana', 'Menaa', 'Ras El Aioun', 'T’Kout', 'Aïn Djasser'],
        ],
        6 => [
            'code' => '06',
            'name' => '06 - Béjaïa',
            'zone' => 'nord',
            'communes' => ['Béjaïa', 'Amizour', 'Akbou', 'Seddouk', 'Tichy', 'Aokas', 'Sidi Aïch', 'El Kseur', 'Kherrata', 'Adekar', 'Souk El Ténine', 'Tazmalt', 'Ighzer Amokrane'],
        ],
        7 => [
            'code' => '07',
            'name' => '07 - Biskra',
            'zone' => 'sahara_nord',
            'communes' => ['Biskra', 'Sidi Okba', 'Tolga', 'Chetma', 'Zeribet El Oued', 'El Kantara', 'M’Chouneche', 'Ouled Djellal', 'Lichana', 'Foughala', 'El Outaya'],
        ],
        8 => [
            'code' => '08',
            'name' => '08 - Béchar',
            'zone' => 'sahara_nord',
            'communes' => ['Béchar', 'Kenadsa', 'Abadla', 'Taghit', 'Béni Abbès', 'Tabelbala', 'Kerzaz', 'Erg Ferradj', 'Mécheria', 'Lahmar', 'Boukais'],
        ],
        9 => [
            'code' => '09',
            'name' => '09 - Blida',
            'zone' => 'centre',
            'communes' => ['Blida', 'Boufarik', 'Ouled Yaïch', 'Beni Mered', 'Chebli', 'Bougara', 'Mouzaïa', 'El Affroun', 'Chiffa', 'Meftah', 'Larbaâ', 'Bouinan', 'Soumaa', 'Oued Djer'],
        ],
        10 => [
            'code' => '10',
            'name' => '10 - Bouira',
            'zone' => 'centre',
            'communes' => ['Bouira', 'Lakhdaria', 'Aïn Bessem', 'Sour El Ghozlane', 'M’Chedallah', 'Kadiria', 'Bechloul', 'El Hachimia', 'Haizer', 'Taghzout', 'Aomar'],
        ],
        11 => [
            'code' => '11',
            'name' => '11 - Tamanrasset',
            'zone' => 'grand_sud',
            'communes' => ['Tamanrasset', 'Abalessa', 'In Amguel', 'Tazrouk', 'Idles'],
        ],
        12 => [
            'code' => '12',
            'name' => '12 - Tébessa',
            'zone' => 'est_ouest',
            'communes' => ['Tébessa', 'Bir El Ater', 'Cheria', 'El Aouinet', 'El Kouif', 'Morsott', 'Ouenza', 'Negrine', 'Bekkaria', 'Boukhadra'],
        ],
        13 => [
            'code' => '13',
            'name' => '13 - Tlemcen',
            'zone' => 'est_ouest',
            'communes' => ['Tlemcen', 'Mansourah', 'Chetouane', 'Maghnia', 'Ghazaouet', 'Remchi', 'Sebdou', 'Nedroma', 'Beni Saf', 'Hennaya', 'Ouled Mimoun', 'Bensekrane'],
        ],
        14 => [
            'code' => '14',
            'name' => '14 - Tiaret',
            'zone' => 'hauts_plateaux',
            'communes' => ['Tiaret', 'Sougueur', 'Frenda', 'Ksar Chellala', 'Mahdia', 'Rahouia', 'Mechraa Safa', 'Dahmouni', 'Aïn Deheb', 'Oued Lili'],
        ],
        15 => [
            'code' => '15',
            'name' => '15 - Tizi Ouzou',
            'zone' => 'nord',
            'communes' => ['Tizi Ouzou', 'Azazga', 'Draâ Ben Khedda', 'Tigzirt', 'Aïn El Hammam', 'Larbaâ Nath Irathen', 'Boghni', 'Ouadhia', 'Azeffoun', 'Bouzeguène', 'Makouda', 'Ouaguenoun'],
        ],
        16 => [
            'code' => '16',
            'name' => '16 - Alger',
            'zone' => 'alger',
            'communes' => [
                'Alger Centre', 'Sidi M’Hamed', 'El Madania', 'Belouizdad', 'Bab El Oued', 'Bologhine', 'Casbah', 'Oued Koriche',
                'Bir Mourad Raïs', 'El Biar', 'Bouzareah', 'Hydra', 'Ben Aknoun', 'El Mouradia', 'Kouba', 'Birmandreis',
                'Birtouta', 'Ouled Chebel', 'Tessala El Merdja', 'Zéralda', 'Staoueli', 'Souidania', 'Chéraga', 'Ouled Fayet',
                'Aïn Benian', 'Draria', 'Douera', 'Baba Hassen', 'Khraicia', 'Saoula', 'Hussein Dey', 'Bourouba', 'El Magharia',
                'Mohammadia', 'Dar El Beïda', 'Bab Ezzouar', 'Bordj El Kiffan', 'El Harrach', 'Baraki', 'Les Eucalyptus',
                'Rouïba', 'Reghaïa', 'Aïn Taya', 'Bordj El Bahri', 'El Marsa', 'Harraoua', 'Beni Messous', 'Rahmania', 'Mahelma', 'Dely Ibrahim',
            ],
        ],
        17 => [
            'code' => '17',
            'name' => '17 - Djelfa',
            'zone' => 'hauts_plateaux',
            'communes' => ['Djelfa', 'Aïn Oussara', 'Messaad', 'Hassi Bahbah', 'Charef', 'Dar Chioukh', 'El Idrissia', 'Birine', 'Sidi Ladjel', 'Faid El Botma'],
        ],
        18 => [
            'code' => '18',
            'name' => '18 - Jijel',
            'zone' => 'nord',
            'communes' => ['Jijel', 'Taher', 'El Milia', 'El Aouana', 'Ziama Mansouriah', 'Chekfa', 'Texenna', 'Sidi Abdelaziz', 'Settara', 'El Ancer'],
        ],
        19 => [
            'code' => '19',
            'name' => '19 - Sétif',
            'zone' => 'est_ouest',
            'communes' => ['Sétif', 'El Eulma', 'Aïn Oulmene', 'Aïn Azel', 'Bougaa', 'Aïn Arnat', 'Beni Aziz', 'Amoucha', 'Salah Bey', 'Guellal', 'Djemila', 'Bazer Sakhra'],
        ],
        20 => [
            'code' => '20',
            'name' => '20 - Saïda',
            'zone' => 'hauts_plateaux',
            'communes' => ['Saïda', 'Youb', 'Aïn El Hadjar', 'Ouled Brahim', 'El Hassasna', 'Sidi Boubekeur'],
        ],
        21 => [
            'code' => '21',
            'name' => '21 - Skikda',
            'zone' => 'nord',
            'communes' => ['Skikda', 'El Hadaiek', 'Azzaba', 'Collo', 'Tamalous', 'Ramdane Djamel', 'Harrouch', 'Sidi Mezghiche', 'Ben Azzouz', 'Zitouna'],
        ],
        22 => [
            'code' => '22',
            'name' => '22 - Sidi Bel Abbès',
            'zone' => 'est_ouest',
            'communes' => ['Sidi Bel Abbès', 'Telagh', 'Sfisef', 'Ben Badis', 'Ras El Ma', 'Tessala', 'Mostefa Ben Brahim', 'Sidi Ali Benyoub', 'Merine'],
        ],
        23 => [
            'code' => '23',
            'name' => '23 - Annaba',
            'zone' => 'nord',
            'communes' => ['Annaba', 'El Bouni', 'El Hadjar', 'Sidi Amar', 'Berrahal', 'Seraïdi', 'Ain El Berda', 'Cheurfa'],
        ],
        24 => [
            'code' => '24',
            'name' => '24 - Guelma',
            'zone' => 'est_ouest',
            'communes' => ['Guelma', 'Oued Zenati', 'Bouchegouf', 'Héliopolis', 'Guelaat Bou Sbaa', 'Hammam Debagh', 'Belkheir', 'Aïn Makhlouf'],
        ],
        25 => [
            'code' => '25',
            'name' => '25 - Constantine',
            'zone' => 'nord',
            'communes' => ['Constantine', 'El Khroub', 'Aïn Smara', 'Hamma Bouziane', 'Didouche Mourad', 'Zighoud Youcef', 'Ouled Rahmoun', 'Ain Abid', 'Ibn Ziad'],
        ],
        26 => [
            'code' => '26',
            'name' => '26 - Médéa',
            'zone' => 'centre',
            'communes' => ['Médéa', 'Berrouaghia', 'Ksar El Boukhari', 'Beni Slimane', 'Tablat', 'Ouzera', 'Aïn Boucif', 'Chahbounia', 'El Omaria', 'Seghouane'],
        ],
        27 => [
            'code' => '27',
            'name' => '27 - Mostaganem',
            'zone' => 'nord',
            'communes' => ['Mostaganem', 'Aïn Nouïssy', 'Hassi Mameche', 'Mesra', 'Bouguirat', 'Sidi Ali', 'Achaacha', 'Sidi Lakhdar', 'Aïn Tedeles', 'Khadra'],
        ],
        28 => [
            'code' => '28',
            'name' => '28 - M’Sila',
            'zone' => 'hauts_plateaux',
            'communes' => ['M’Sila', 'Bou Saâda', 'Magra', 'Sidi Aïssa', 'Aïn El Hadjel', 'Hammam Dhalaa', 'Ouled Derradj', 'Ben Srour', 'Chellal'],
        ],
        29 => [
            'code' => '29',
            'name' => '29 - Mascara',
            'zone' => 'est_ouest',
            'communes' => ['Mascara', 'Sig', 'Mohammadia', 'Tighennif', 'Ghriss', 'Oued El Abtal', 'Bouhanifia', 'Zahana', 'Aïn Fares'],
        ],
        30 => [
            'code' => '30',
            'name' => '30 - Ouargla',
            'zone' => 'sahara_nord',
            'communes' => ['Ouargla', 'Hassi Messaoud', 'Rouissat', 'N’Goussa', 'Sidi Khouiled', 'Ain Beida', 'Taibet'],
        ],
        31 => [
            'code' => '31',
            'name' => '31 - Oran',
            'zone' => 'nord',
            'communes' => [
                'Oran', 'Bir El Djir', 'Es Senia', 'Aïn El Turk', 'Arzew', 'Bethioua', 'Boutlélis',
                'Gdyel', 'Mers El Kébir', 'Bousfer', 'El Ançor', 'Misserghin', 'Oued Tlelat', 'Sidi Chami', 'Hassi Bounif', 'Hassi Ben Okba',
            ],
        ],
        32 => [
            'code' => '32',
            'name' => '32 - El Bayadh',
            'zone' => 'hauts_plateaux',
            'communes' => ['El Bayadh', 'Rogassa', 'Brezina', 'Bougtob', 'El Abiodh Sidi Cheikh', 'Labiodh Sidi Cheikh', 'Cheguig'],
        ],
        33 => [
            'code' => '33',
            'name' => '33 - Illizi',
            'zone' => 'grand_sud',
            'communes' => ['Illizi', 'In Amenas', 'Debdeb', 'Bordj Omar Driss'],
        ],
        34 => [
            'code' => '34',
            'name' => '34 - Bordj Bou Arreridj',
            'zone' => 'est_ouest',
            'communes' => ['Bordj Bou Arreridj', 'Ras El Oued', 'Mansoura', 'Medjana', 'El Achir', 'Bir Kasdali', 'Bordj Zemoura', 'Khelil'],
        ],
        35 => [
            'code' => '35',
            'name' => '35 - Boumerdès',
            'zone' => 'centre',
            'communes' => [
                'Boumerdès', 'Zemmouri', 'Thenia', 'Dellys', 'Naciria', 'Isser', 'Bordj Menaïel', 'Baghlia',
                'Khemis El Khechna', 'Ouled Moussa', 'Hammedi', 'Keddache', 'Corso', 'Tidjelabine', 'Si Mustapha',
            ],
        ],
        36 => [
            'code' => '36',
            'name' => '36 - El Tarf',
            'zone' => 'nord',
            'communes' => ['El Tarf', 'El Kala', 'Ben M’Hidi', 'Besbes', 'Drean', 'Bouhadjar', 'Chebaita Mokhtar'],
        ],
        37 => [
            'code' => '37',
            'name' => '37 - Tindouf',
            'zone' => 'grand_sud',
            'communes' => ['Tindouf', 'Oum El Assel'],
        ],
        38 => [
            'code' => '38',
            'name' => '38 - Tissemsilt',
            'zone' => 'hauts_plateaux',
            'communes' => ['Tissemsilt', 'Theniet El Had', 'Lardjem', 'Bordj Bou Naama', 'Khemisti', 'Ammari'],
        ],
        39 => [
            'code' => '39',
            'name' => '39 - El Oued',
            'zone' => 'sahara_nord',
            'communes' => ['El Oued', 'Robbah', 'Guemar', 'Debila', 'Bayadha', 'Hassi Khalifa', 'Magrane', 'Reguiba'],
        ],
        40 => [
            'code' => '40',
            'name' => '40 - Khenchela',
            'zone' => 'est_ouest',
            'communes' => ['Khenchela', 'Kais', 'Chechar', 'Bouhmama', 'El Hamma', 'Ouled Rechache'],
        ],
        41 => [
            'code' => '41',
            'name' => '41 - Souk Ahras',
            'zone' => 'est_ouest',
            'communes' => ['Souk Ahras', 'Sedrata', 'M’Daourouch', 'Taoura', 'Merahna', 'Heddada', 'Ouled Driss'],
        ],
        42 => [
            'code' => '42',
            'name' => '42 - Tipaza',
            'zone' => 'centre',
            'communes' => [
                'Tipaza', 'Cherchell', 'Kolea', 'Bou Ismaïl', 'Hadjout', 'Fouka', 'Douaouda', 'Gouraya',
                'Damous', 'Ahmer El Aïn', 'Sidi Amar', 'Attatba', 'Chaiba',
            ],
        ],
        43 => [
            'code' => '43',
            'name' => '43 - Mila',
            'zone' => 'est_ouest',
            'communes' => ['Mila', 'Chelghoum Laïd', 'Tadjenanet', 'Grarem Gouga', 'Ferdjioua', 'Rouached', 'Oued Endja', 'Teleghma'],
        ],
        44 => [
            'code' => '44',
            'name' => '44 - Aïn Defla',
            'zone' => 'centre',
            'communes' => ['Aïn Defla', 'Khemis Miliana', 'Miliana', 'El Attaf', 'Djelida', 'Boumedfaa', 'Djendel', 'Hammam Righa'],
        ],
        45 => [
            'code' => '45',
            'name' => '45 - Naâma',
            'zone' => 'hauts_plateaux',
            'communes' => ['Naâma', 'Mécheria', 'Aïn Sefra', 'Tiout', 'Sfissifa', 'Moghrar', 'Asla'],
        ],
        46 => [
            'code' => '46',
            'name' => '46 - Aïn Témouchent',
            'zone' => 'nord',
            'communes' => ['Aïn Témouchent', 'Beni Saf', 'Hammam Bouhadjar', 'El Malah', 'El Amria', 'Ain Kihal', 'Oulhaca El Gheraba'],
        ],
        47 => [
            'code' => '47',
            'name' => '47 - Ghardaïa',
            'zone' => 'sahara_nord',
            'communes' => ['Ghardaïa', 'Metlili', 'El Guerrara', 'Dhayet Bendhahoua', 'Berriane', 'Zelfana', 'Bounoura', 'El Atteuf'],
        ],
        48 => [
            'code' => '48',
            'name' => '48 - Relizane',
            'zone' => 'est_ouest',
            'communes' => ['Relizane', 'Oued Rhiou', 'Mazouna', 'Yellel', 'Zemmora', 'Djidiouia', 'El Matmar', 'Ammi Moussa'],
        ],
        49 => [
            'code' => '49',
            'name' => '49 - Timimoun',
            'zone' => 'grand_sud',
            'communes' => ['Timimoun', 'Aougrout', 'Deldoul', 'Tinerkouk', 'Ksar Kaddour', 'Ouled Said'],
        ],
        50 => [
            'code' => '50',
            'name' => '50 - Bordj Badji Mokhtar',
            'zone' => 'grand_sud',
            'communes' => ['Bordj Badji Mokhtar', 'Timiaouine'],
        ],
        51 => [
            'code' => '51',
            'name' => '51 - Ouled Djellal',
            'zone' => 'sahara_nord',
            'communes' => ['Ouled Djellal', 'Sidi Khaled', 'Ras El Miaad', 'Besbes', 'Ech Chaiba', 'Doucen'],
        ],
        52 => [
            'code' => '52',
            'name' => '52 - Béni Abbès',
            'zone' => 'grand_sud',
            'communes' => ['Béni Abbès', 'Kerzaz', 'Tabelbala', 'El Ouata', 'Igli', 'Timoudi'],
        ],
        53 => [
            'code' => '53',
            'name' => '53 - In Salah',
            'zone' => 'grand_sud',
            'communes' => ['In Salah', 'In Ghar', 'Foggaret Ezzaouia'],
        ],
        54 => [
            'code' => '54',
            'name' => '54 - In Guezzam',
            'zone' => 'grand_sud',
            'communes' => ['In Guezzam', 'Tin Zaouatine'],
        ],
        55 => [
            'code' => '55',
            'name' => '55 - Touggourt',
            'zone' => 'sahara_nord',
            'communes' => ['Touggourt', 'Nezla', 'Tebesbest', 'Zaouia El Abidia', 'Megarine', 'Taibet', 'El Hadjira'],
        ],
        56 => [
            'code' => '56',
            'name' => '56 - Djanet',
            'zone' => 'grand_sud',
            'communes' => ['Djanet', 'Bordj El Haouas'],
        ],
        57 => [
            'code' => '57',
            'name' => '57 - El M’Ghair',
            'zone' => 'sahara_nord',
            'communes' => ['El M’Ghair', 'Djamaa', 'Oum Touyour', 'Sidi Amrane', 'Still', 'Tendla'],
        ],
        58 => [
            'code' => '58',
            'name' => '58 - El Meniaa',
            'zone' => 'sahara_nord',
            'communes' => ['El Meniaa', 'Hassi Gara', 'Hassi Fehal'],
        ],
    ];

    /**
     * Retourne la liste complète de toutes les Wilayas (avec nom et communes).
     */
    public function all(): array
    {
        return self::WILAYAS;
    }

    /**
     * Liste des noms des wilayas formatés.
     */
    public function getWilayaNames(): array
    {
        return array_map(fn ($item) => $item['name'], self::WILAYAS);
    }

    /**
     * Recherche d'une wilaya par son nom ou code.
     */
    public function findWilaya(?string $query): ?array
    {
        if (! $query) {
            return null;
        }

        $queryClean = mb_strtolower(trim($query));

        foreach (self::WILAYAS as $id => $data) {
            if (
                mb_strtolower($data['name']) === $queryClean
                || mb_strtolower($data['code']) === $queryClean
                || (string) $id === $queryClean
                || str_contains(mb_strtolower($data['name']), $queryClean)
            ) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Communes d'une wilaya donnée.
     */
    public function getCommunesForWilaya(?string $wilaya): array
    {
        $found = $this->findWilaya($wilaya);

        return $found ? $found['communes'] : [];
    }

    /**
     * Calcul des frais d'expédition (home ou desk).
     */
    public function calculateShippingFee(?string $wilaya, string $deliveryType = 'home'): int
    {
        $found = $this->findWilaya($wilaya);
        $zone = $found['zone'] ?? 'nord';
        $fees = self::ZONE_FEES[$zone] ?? self::ZONE_FEES['nord'];

        return $deliveryType === 'desk' ? $fees['desk'] : $fees['home'];
    }
}
