<?php

require_once('CoicopCat.php');
require_once('CoicopSub.php');

class Coicop {


  function __construct() {
    $this->cats = array();

    $c01 = new CoicopCat('01', '#FF0000',
                       'Elintarvikkeet ja alkoholittomat juomat',
		       'Food and non-alcoholic beverages');
    $c01->addSub('01.1', 'Elintarvikkeet', 'Food');
    $c01->addHelp('01.1.1', 'Viljatuotteet ja leipä', '');
    $c01->addHelp('01.1.2', 'Liha', '');
    $c01->addHelp('01.1.3', 'Kala', '');
    $c01->addHelp('01.1.4', 'Maitotuotteet, juusto ja kananmunat', '');
    $c01->addHelp('01.1.5', 'Öjlyt ja rasvat', '');
    $c01->addHelp('01.1.6', '', '');
    $c01->addHelp('01.1.7', '', '');
    $c01->addHelp('01.1.8', '', '');
    $c01->addHelp('01.1.9', '', '');
    $c01->addSub('01.2', 'Alkoholittomat juomat', 'Non-alcoholic beverages');
    $this->cats[] = &$c01;

    $c02 = new CoicopCat('02', '#FF6600', 
                          # 'Alkoholijuomat, tupakka ja huumausaineet',
                          'Alkoholijuomat ja tupakka',
  		          'Alcoholic beverages, tobacco and narcotics');
    $c02->addSub('02.1', 'Alkoholijuomat', 'Alcoholic beverages');
    $c02->addSub('02.2', 'Tupakka', 'Tobacco');
    # $c02->addSub('02.3', 'Huumausaineet', 'Narcotics');
    $this->cats[] = &$c02;

    $c03 = new CoicopCat('03', '#FF9933', 
                       'Vaatetus ja jalkineet',
		       'Clothing and footwear');
    # $c03->addSub('03.1', 'Vaatetus ja vaatetuskankaat', 'Clothing');
    $c03->addSub('03.1', 'Vaatetus', 'Clothing');
    $c03->addHelp('03.1.1', 'Vaatteiden valmistusmateriaalit', 'Clothing 1');
    $c03->addHelp('03.1.2', 'Vaatteet', 'Clothing 2');
    $c03->addHelp('03.1.3', 'Asusteet ja pukineet', 'Clothing 3');
    $c03->addHelp('03.1.4', 'Vaatteiden pesu, korjaus ja vuokraus', 'Clothing 4');
    $c03->addSub('03.2', 'Jalkineet', 'Footwear');
    $c03->addHelp('03.2.1', 'Kengät ja muut jalkineet', 'Footwear 1');
    $c03->addHelp('03.2.2', 'Jalkineiden korjaus ja vuokraus', 'Footwear 2');
    $this->cats[] = &$c03;

    $c04 = new CoicopCat('04', '#FFCC33',
                       # 'Asuminen ja energia',
                       'Asuminen, vesi ja sähkö',
		       'Housing, water, electricity, gas and other fuels');
    # $c04->addSub('04.1', 'Todelliset asumisvuokrat', 'Actual rentals for housing');
    $c04->addSub('04.1', 'Asuntojen vuokrat', 'Actual rentals for housing');
    # $c04->addSub('04.2', 'Laskennalliset asumisvuokrat', 'Imputed rentals for housing');
    $c04->addSub('04.2', 'Omistusasuminen', 'Imputed rentals for housing');
    $c04->addSub('04.3', 'Asunnon huolto ja korjaus', 'Maintenance and repair of the dwelling');
    # $c04->addSub('04.4', 'Muut asumiseen liittyvät palvelut', 'Water supply and miscellaneous services relating to the dwelling');
    $c04->addSub('04.4', 'Vesi ja muut asumiseen liittyvät palvelut', 'Water supply and miscellaneous services relating to the dwelling');
    $c04->addSub('04.5', 'Sähkö, kaasu ja muut polttoaineet', 'Electricity, gas and other fuels');
    $this->cats[] = &$c04;

    $c05 = new CoicopCat('05', '#33CC33', 
                       # 'Kodin kalusteet, koneet, tavarat ja palvelut',
                       'Kalusteet, kotitalouskoneet ja yleinen kodinhoito',
		       'Furnishings, household equipment and routine household maintenance');
    # $c05->addSub('05.1', 'Huonekalut, matot ja sisustus', 'Furniture and furnishings, carpets and other floor coverings');
    $c05->addSub('05.1', 'Huonekalut ja kalusteet, matot ja muut lattianpäällysteet', 'Furniture and furnishings, carpets and other floor coverings');
    $c05->addSub('05.2', 'Kodintekstiilit', 'Household textiles');
    $c05->addSub('05.3', 'Kodinkoneet', 'Household appliances');
    # $c05->addSub('05.4', 'Lasitavarat, astiat ja taloustavarat', 'Glassware, tableware and household utensils');
    $c05->addSub('05.4', 'Lasitavarat, astiat ja kotitaloustarvikkeet', 'Glassware, tableware and household utensils');
    $c05->addSub('05.5', 'Kodin ja puutarhan työkalut ja laitteet', 'Tools and equipment for house and garden');
    # $c05->addSub('05.6', 'Kodinhoitotarvikkeet ja -palvelut', 'Goods and services for routine household maintenance');
    $c05->addSub('05.6', 'Taloudenhoitoon liittyvät tavarat ja palvelut', 'Goods and services for routine household maintenance');
    $this->cats[] = &$c05;

    $c06 = new CoicopCat('06', '#009900', 
                       'Terveys',
		       'Health');
    # $c06->addSub('06.1', 'Lääkevalmisteet, hoitolaitteet ja -tarvikkeet', 'Medical products, appliances and equipment');
    $c06->addSub('06.1', 'Lääkevalmisteet, hoitolaitteet ja -välineet', 'Medical products, appliances and equipment');
    $c06->addSub('06.2', 'Avohoitopalvelut', 'Outpatient services');
    $c06->addSub('06.3', 'Sairaalapalvelut', 'Hospital services');
    $this->cats[] = &$c06;

    $c07 = new CoicopCat('07', '#003333',
                       'Liikenne',
		       'Transport');
    $c07->addSub('07.1', 'Ajoneuvojen hankinta', 'Purchase of vehicles');
    $c07->addSub('07.2', 'Yksityisajoneuvojen käyttö', 'Operation of personal transport equipment');
    # $c07->addSub('07.3', 'Liikennepalvelut', 'Transport services');
    $c07->addSub('07.3', 'Julkinen liikenne', 'Transport services');
    $this->cats[] = &$c07;

    $c08 = new CoicopCat('08', '#0033CC',
                       # 'Tietoliikenne',
                       'Viestintä',
		       'Communication');
    $c08->addSub('08.1', 'Postipalvelut', 'Postal services');
    $c08->addSub('08.2', 'Puhelin- ja telekopiolaitteet', 'Telephone and telefax equipment');
    $c08->addSub('08.3', 'Puhelin- ja telekopiopalvelut', 'Telephone and telefax services');
    $this->cats[] = &$c08;

    $c09 = new CoicopCat('09', '#6666FF',
                       'Kulttuuri ja vapaa-aika',
		       'Recreation and culture');
    # $c09->addSub('09.1', 'Audiovisuaaliset laitteet ja tietokoneet', 'Audio-visual, photographic and information processing equipment');
    $c09->addSub('09.1', 'Audiovisuaaliset sekä valokuvaus- ja tietojenkäsittelylaitteet', 'Audio-visual, photographic and information processing equipment');
    $c09->addHelp('09.1.1', 'Äänen ja kuvan vastaanottoon, tallentamiseen ja toistoon käytettävät laitteet', 'Audio-visual, photographic and information processing equipment 1');
    $c09->addHelp('09.1.2', 'Valokuvaus- ja elokuvalaitteet sekä optiset laitteet', 'Audio-visual, photographic and information processing equipment 2');
    $c09->addHelp('09.1.3', 'Tietojenkäsittelylaitteet', 'Audio-visual, photographic and information processing equipment 3');
    $c09->addHelp('09.1.4', 'Tallennusvälineet', 'Audio-visual, photographic and information processing equipment 4');
    $c09->addHelp('09.1.5', 'Audiovisuaalisten laitteiden sekä valokuvaus- ja tietojenkäsittelylaitteiden korjaus', 'Audio-visual, photographic and information processing equipment 5');
    # $c09->addSub('09.2', 'Muut suuret vapaa-ajan välineet', 'Other major durables for recreation and culture');
    $c09->addSub('09.2', 'Muut kulttuuriin ja vapaa-aikaan liittyvät kestokulutustavarat', 'Other major durables for recreation and culture');
    # $c09->addSub('09.3', 'Muut virkistys- ja harrastusvälineet, puutarhanhoito sekä lemmikkieläimet', 'Other recreational items and equipment, gardens and pets');
    $c09->addSub('09.3', 'Muut vapaa-aikaan liittyvät tarvikkeet ja laitteet, puutarhat ja lemmikkieläimet', 'Other recreational items and equipment, gardens and pets');
    # $c09->addSub('09.4', 'Kulttuuri- ja virkistyspalvelut', 'Recreational and cultural services');
    $c09->addSub('09.4', 'Kulttuuri- ja vapaa-ajan palvelut', 'Recreational and cultural services');
    $c09->addSub('09.5', 'Sanomalehdet, kirjat ja paperitavarat', 'Newspapers, books and stationery');
    $c09->addSub('09.6', 'Valmismatkat', 'Package holidays');
    $this->cats[] = &$c09;

    $c10 = new CoicopCat('10', '#6600FF',
                       'Koulutus',
		       'Education');
    $c10->addSub('10.1', 'Esiasteen ja alemman perusasteen koulutus', 'Pre-primary and primary education');
    $c10->addSub('10.2', 'Ylemmän perusasteen ja keskiasteen koulutus', 'Secondary education');
    $c10->addSub('10.3', 'Keskiasteen jälkeinen koulutus, joka ei ole korkea-asteen koulutusta', 'Post-secondary non-tertiary education');
    # $c10->addSub('10.4', 'Korkea-asteen koulutus', 'Tertiary education');
    $c10->addSub('10.4', 'Korkea-asteen koulutus ja tutkijakoulutusaste', 'Tertiary education');
    $c10->addSub('10.5', 'Tasoltaan määrittelemätön koulutus', 'Education not definable by level');
    $this->cats[] = &$c10;

    $c11 = new CoicopCat('11', '#9900CC',
                       # 'Ravintolat, kahvilat ja hotellit',
                       'Ravintolat ja hotellit',
		       'Restaurants and hotels');
    # $c11->addSub('11.1', 'Ravitsemispalvelut', 'Catering services');
    $c11->addSub('11.1', 'Ateriapalvelut', 'Catering services');
    $c11->addSub('11.2', 'Majoituspalvelut', 'Accommodation services');
    $this->cats[] = &$c11;

    $c12 = new CoicopCat('12', '#FF00FF',
                       'Muut tavarat ja palvelut',
		       'Miscellaneous goods and services');
    # $c12->addSub('12.1', 'Henkilökohtainen hygienia ja kauneudenhoito', 'Personal care');
    $c12->addSub('12.1', 'Henkilökohtainen hygienia', 'Personal care');
    # $c12->addSub('12.2', 'Prostituutio', 'Prostitution');
    # $c12->addSub('12.3', 'Henkilökohtaiset tavarat', 'Personal effects n.e.c.');
    $c12->addSub('12.3', 'Henkilökohtaiset esineet, muualla luokittelemattomat', 'Personal effects n.e.c.');
    # $c12->addSub('12.4', 'Sosiaalipalvelut', 'Social protection');
    $c12->addSub('12.4', 'Sosiaalinen suojelu', 'Social protection');
    $c12->addSub('12.5', 'Vakuutukset', 'Insurance');
    # $c12->addSub('12.6', 'Rahoituspalvelut', 'Financial services n.e.c.');
    $c12->addSub('12.6', 'Rahoituspalvelut, muualla luokittelemattomat', 'Financial services n.e.c.');
    # $c12->addSub('12.7', 'Muut palvelut', 'Other services n.e.c.');
    $c12->addSub('12.7', 'Muut palvelut, muualla luokittelemattomat', 'Other services n.e.c.');
    # $c12->addSub('12.8', '---', '---');
    $c12->addSub('12.9', 'Kulutusmenojen ulkopuoliset erät', 'Expenditure not broken down into other goods and services');
    $this->cats[] = &$c12;

  }

  function getCats() {
    return $this->cats;
  }

  function getCat(&$id) {
    $num = sprintf("%d", $id) - 1;
    return $this->cats[$num];
    # return $this->cats[$id-1];
  }

  function getCatName(&$id, $lang='en') {
    $cat = $this->getCat($id);
    $name = (($lang == 'fi') ? $cat->nameFi : $cat->nameEn);
    return $name;
  }

  function getSub($id) {
    $cat = $this->getCat($id);
    if (!$cat) {
      tigger_error("No cat $id found!", E_USER_ERROR);
      return false;
    }
    if ($sub = $cat->getSub($id)) {
      return $sub;
    }
    return $cat;
  }

  function findCat($type) {
    list($id, $rest) = explode('.', $type);
    $cat = $this->getCat($id);
    if (!$rest) {
      return $cat;
    }
    if ($cat) {
      return $cat->getSub($type);
    }
    return false;
  }

  function getSubName(&$id, $lang='en') {
    $sub = $this->getSub($id);
    $name = (($lang == 'fi') ? $sub->nameFi : $sub->nameEn);
    return $name;
  }
}

?>
