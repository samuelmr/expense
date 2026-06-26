<?php

require_once('CoicopCat.php');
require_once('CoicopSub.php');

class Coicop {

  public $cats;

  public function __construct() {
    $this->cats = array();

    $c01 = new CoicopCat('01', '#FF0000', 'Elintarvikkeet ja alkoholittomat juomat','Food and non-alcoholic beverages');
    $c01->addSub('01.1', 'Elintarvikkeet', 'Food');
    $c01->addSub('01.2', 'Alkoholittomat juomat', 'Non-alcoholic beverages');
    $this->cats[] = &$c01;

    $c02 = new CoicopCat('02', '#FF6600', 'Alkoholijuomat ja tupakka','Alcoholic beverages and tobacco');
    $c02->addSub('02.1', 'Alkoholijuomat', 'Alcoholic beverages');
    $c02->addSub('02.3', 'Tupakka', 'Tobacco');
    # $c02->addSub('02.3', 'Huumausaineet', 'Narcotics');
    $this->cats[] = &$c02;

    $c03 = new CoicopCat('03', '#FF9933', 'Vaatetus ja jalkineet', 'Clothing and footwear');
    # $c03->addSub('03.1', 'Vaatetus ja vaatetuskankaat', 'Clothing');
    $c03->addSub('03.1', 'Vaatetus', 'Clothing');
    $c03->addSub('03.2', 'Jalkineet', 'Footwear');
    $this->cats[] = &$c03;

    $c04 = new CoicopCat('04', '#FFCC33', 'Asuminen, vesi, sähkö, kaasu ja muut polttoaineet', 'Housing, water, electricity, gas and other fuels');
    $c04->addSub('04.1', 'Todelliset asumisvuokrat', 'AActual rental payments made for housing');
    $c04->addSub('04.2', 'Laskennalliset asumisvuokrat', 'Imputed rentals of owner-occupiers for main residence');
    $c04->addSub('04.3', 'Asuntojen huolto, korjaus ja turvallisuus', 'Maintenance, repair and security of the dwelling');
    $c04->addSub('04.4', 'Vesihuolto ja muut asumiseen liittyvät palvelut', 'Water supply and miscellaneous services relating to the dwelling');
    $c04->addSub('04.5', 'Sähkö, kaasu ja muut polttoaineet', 'Electricity, gas and other fuels');
    $c04->addSub('04.6', 'Omistusasuminen', 'Owner-occupied housing');
    $this->cats[] = &$c04;

    $c05 = new CoicopCat('05', '#33CC33', 'Kalusteet, kotitalouskoneet ja tavanomainen kodinhoito', 'Furnishings, household equipment and routine household maintenance');
    $c05->addSub('05.1', 'Huonekalut, kalusteet ja irtomatot', 'Furniture, furnishings and loose carpets');
    $c05->addSub('05.2', 'Kodintekstiilit', 'Household textiles');
    $c05->addSub('05.3', 'Kodinkoneet', 'Household appliances');
    $c05->addSub('05.4', 'Lasitavarat, astiat ja taloustavarat', 'Glassware, tableware and household utensils');
    $c05->addSub('05.5', 'Kodin ja puutarhan työkalut ja laitteet', 'Tools and equipment for house and garden');
    $c05->addSub('05.6', 'Kodinhoitotarvikkeet ja -palvelut', 'Goods and services for routine household maintenance');
    $this->cats[] = &$c05;

    $c06 = new CoicopCat('06', '#009900', 'Terveys', 'Health');
    # $c06->addSub('06.1', 'Lääkevalmisteet, hoitolaitteet ja -tarvikkeet', 'Medical products, appliances and equipment');
    $c06->addSub('06.1', 'Lääkkeet ja terveystuotteet', 'Medicines and health products');
    $c06->addSub('06.2', 'Avohoitopalvelut', 'Outpatient care services');
    $c06->addSub('06.3', 'Sairaalapalvelut', 'Inpatient care services');
    $c06->addSub('06.4', 'Muut terveyspalvelut', 'Other health services');
    $this->cats[] = &$c06;

    $c07 = new CoicopCat('07', '#003333', 'Liikenne', 'Transport');
    $c07->addSub('07.1', 'Ajoneuvojen hankinta', 'Purchase of vehicles');
    $c07->addSub('07.2', 'Yksityisajoneuvojen käyttö', 'Operation of personal transport equipment');
    $c07->addSub('07.3', 'Matkustajien kuljetuspalvelut', 'Passenger transport services');
    $c07->addSub('07.4', 'Tavaroiden kuljetuspalvelut', 'Transport services of goods');
    $this->cats[] = &$c07;

    $c08 = new CoicopCat('08', '#0033CC', 'Informaatio ja viestintä', 'Information and communication');
    $c08->addSub('08.1', 'Informaatio- ja viestintätekniset laitteet', 'Information and communication equipment');
    $c08->addSub('08.2', 'Ohjelmistot, pl. pelit', 'Software, excluding games');
    $c08->addSub('08.3', 'Informaatio- ja viestintäpalvelut', 'Information and communication services');
    $this->cats[] = &$c08;

    $c09 = new CoicopCat('09', '#6666FF', 'Kulttuuri ja vapaa-aika', 'Recreation and culture');
    $c09->addSub('09.1', 'Vapaa-aikaan liittyvät kestokulutustavarat', 'Recreational durables');
    $c09->addSub('09.2', 'Muut vapaa-ajan tuotteet', 'Other recreational goods');
    $c09->addSub('09.3', 'Puutarhatarvikkeet ja lemmikkieläimet', 'Garden products and pets');
    $c09->addSub('09.4', 'Vapaa-ajanpalvelut', 'Recreational services');
    $c09->addSub('09.5', 'Kulttuuriesineet', 'Cultural goods');
    $c09->addSub('09.6', 'Kulttuuripalvelut', 'Cultural services');
    $c09->addSub('09.7', 'Sanomalehdet, kirjat ja paperitavarat', 'Newspapers, books and stationery');
    $c09->addSub('09.8', 'Valmismatkat', 'Package holidays');
    $this->cats[] = &$c09;

    $c10 = new CoicopCat('10', '#6600FF', 'Koulutus', 'Education services');
    $c10->addSub('10.1', 'Esiasteen ja alemman perusasteen koulutus', 'Early childhood and primary education');
    $c10->addSub('10.2', 'Ylemmän perusasteen ja keskiasteen koulutus', 'Secondary education');
    $c10->addSub('10.3', 'Keskiasteen jälkeinen koulutus, joka ei ole korkea-asteen koulutusta', 'Post-secondary non-tertiary education');
    $c10->addSub('10.4', 'Korkea-asteen koulutus', 'Tertiary education');
    $c10->addSub('10.5', 'Tasoltaan määrittelemätön koulutus', 'Education not defined by level');
    $this->cats[] = &$c10;

    $c11 = new CoicopCat('11', '#9900CC', 'Ravintola- ja majoituspalvelut', 'Restaurants and accommodation services');
    $c11->addSub('11.1', 'Ravitsemispalvelut', 'Catering services');
    $c11->addSub('11.2', 'Majoituspalvelut', 'Accommodation services');
    $this->cats[] = &$c11;

    $c12 = new CoicopCat('12', '#FF00FF', 'Vakuutus- ja rahoituspalvelut', 'Insurance and financial services');
    $c12->addSub('12.1', 'Vakuutukset', 'Insurance');
    $c12->addSub('12.2', 'Rahoituspalvelut', 'Financial services');
    $this->cats[] = &$c12;

    $c13 = new CoicopCat('13', '#FF00FF', 'Henkilökohtainen hygienia, sosiaaliturva ja muut tavarat ja palvelut', 'Personal care, social protection and miscellaneous goods and services');
    $c13->addSub('13.1', 'Henkilökohtainen hygienia', 'Personal care');
    $c13->addSub('13.2', 'Henkilökohtaiset tavarat', 'Personal effects n.e.c.');
    $c13->addSub('13.3', 'Sosiaaliturva', 'Social protection');
    $c13->addSub('13.9', 'Muut palvelut', 'Other services n.e.c.');
    $this->cats[] = &$c13;

    $c99 = new CoicopCat('99', '#FF00FF', 'Kulutusmenojen ulkopuoliset erät', 'Expenditure not broken down into other goods and services');
    $c99->addSub('99.1', 'Kulutusmenojen ulkopuoliset erät', 'Expenditure not broken down into other goods and services');
    $this->cats[] = &$c99;

  }

  function getCats() {
    return $this->cats;
  }

  function getCat(&$id) {
    $num = sprintf("%d", $id) - 1;
    if ($num > 13) {
      $num = 13;
    }
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
      # tigger_error("No cat $id found!", E_USER_ERROR);
      echo "No cat $id found!";
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
