<?php

namespace xsme\HiboxApi;

use SoapClient;

class Hibox
{
    /**
     * Trzymanie konfiguracji SOAP.
     *
     * @var SoapClient
     */
    private $soap;

    /**
     * Zbudowane cale zapytanie do SOAP.
     *
     * @var array
     */
    private $query;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Konstruktor, inicjacja klienta SOAP.
     *
     * @throws \SoapFault
     */
    public function __construct($location, $username, $password)
    {
        $this->location = $location;
        $this->username = $username;
        $this->password = $password;

        $this->soap = new SoapClient(null, array(
            'location' => $this->location,
            'uri' => 'namespace',
            'exceptions' => true,
            'trace' => 1,
            'stream_context' => stream_context_create(array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            )),
            'connection_timeout' => 10,
        ));
    }

    /**
     * Ustawianie parametrów do zapytania.
     *
     * @param string       $method metoda z API Hibox np. 'addDevice', 'getISPSettings'
     * @param string       $endPoint domyślnie do wiekszosci jest 'isp', od urzadzen jest 'devices'
     * @param string|array $params zmienna (nazwa uzytkownika) lub tablica z danymi do zapytania
     * @return object
     */
    private function setParams($method, $endPoint = 'isp', $params = array())
    {
        $this->query = array(
            'method' => 'CallHibox',
            'params' => array(
                'endpoint' => $endPoint,
                'method' => $method,
                'requestParams' => $params,
            ),
            'auth' => array(
                'login' => $this->username,
                'pass' => md5(md5($this->password)),
            ),
        );
        return $this;
    }

    /**
     * Wykonanie zapytania do SOAP.
     *
     * @return array
     */
    private function execCall()
    {
        return (array)$this->soap->call($this->query);
    }

    /**
     * Pobieranie ustawień ISP z systemu Hibox.
     *
     * @return array
     */
    public function getSystemSettings()
    {
        return $this->setParams('getISPSettings')->execCall();
    }

    /**
     * Pobieranie wszystkich metod płatności.
     *
     * @return array
     */
    public function getSystemPaymentMethods()
    {
        return $this->setParams('getPaymentMethods')->execCall();
    }

    /**
     * Pobieranie danych o uzytwkoniku według jego identyfikatora.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @return array
     */
    public function getCustomerByName($userName)
    {
        return $this->setParams('getSubscriberByName', 'isp', $userName)->execCall();
    }

    /**
     * Pobieranie wszystkich uzytkowników naleacych do ISP.
     *
     * @return array
     */
    public function getCustomerAll()
    {
        return $this->setParams('getSubscribersByISP')->execCall();
    }

    /**
     * Pobieranie listy wszystkich subskrypcji dla uzytkownika.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param string $startDate data np. '2020-09-30T23:59:59.999', trzeba podac mikrosekundy format('Y-m-d\TH:i:s.u')
     * @param string $endDate data np. '2020-09-30T23:59:59.999', trzeba podac mikrosekundy format('Y-m-d\TH:i:s.u')
     * @param string $currency waluta jako string np. 'PLN'
     * @return array
     */
    public function getCustomerPurchases($userName, $startDate, $endDate, $currency = 'PLN')
    {
        return $this->setParams('listPurchasesForSubscriber','isp', array(
            'userName' => $userName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currencyA3' => $currency,
        ))->execCall();
    }

    /**
     * Pobieranie listy aktywnych subskrypcji dla uztkownika.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param string $currency waluta jako string np. 'PLN'
     * @return array
     */
    public function getCustomerActivePurchases($userName = '', $currency = 'PLN')
    {
        return $this->setParams('listPurchasesForSubscriber', 'isp', array(
            'userName' => $userName,
            'currencyA3' => $currency,
        ))->execCall();
    }

    /**
     * Pobieranie dodatkowych informacji o uzytkowniku.
     * PIN do zakupow, PIN do kontroli rodzicielskiej.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     *
     * @return array
     */
    public function getCustomerRights($userName)
    {
        return $this->setParams('getSubscriberRights', 'isp', $userName)->execCall();
    }

    /**
     * Dodawanie nowego uzytkownika do systemu.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param string $firstName imie uzytkownika np. 'Jan'
     * @param string $lastName nazwisko uzytkownika np. 'Kowalski'
     * @param string $email adres email np. 'jan@kowalski.pl'
     * @param string $password haslo uzytkownika np. '123qwe456'
     * @param string $language jezyk uzytkownika eng|pol, domyslnie system ustawia 'eng'
     * @return array
     */
    public function postCustomerCreate($userName, $firstName, $lastName, $email, $password, $language = 'pol')
    {
        return $this->setParams('addSubscriber', 'isp', array(
            'userName' => $userName,
            'firstName' => $firstName,
            'lastName' => $lastName ,
            'email' => $email,
            'password' => $password,
            'languageA3Code' => $language,
        ))->execCall();
    }

    /**
     * Usuwanie uzytkownika z systemu Hibox.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @return array
     */
    public function postCustomerDelete($userName)
    {
        return $this->setParams('removeSubscriber', 'isp', $userName)->execCall();
    }

    /**
     * Zmiana kodu PIN uzytkownika do weryfikowania zakupów.
     * To nie jest kod PIN do kontroli rodzicielskiej.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param int    $pin pin jako cyfry np. '1234'
     * @return array
     */
    public function postCustomerUpdatePinCode($userName, $pin)
    {
        return $this->setParams('updateSubscriberPinCode', 'isp', array(
            'userName' => $userName,
            'pinCode' => $pin,
        ))->execCall();
    }

    /**
     * Zmiana kodu PIN do kontroli rodzicielskiej.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param int    $code pin w formie cyfr np. '1234'
     * @return array
     */
    public function postCustomerUpdateLockCode($userName, $code)
    {
        return $this->setParams('updateSubscriberLockCode', 'isp', array(
            'userName' => $userName,
            'lockCode' =>$code,
        ))->execCall();
    }

    /**
     * Zmiana hasła do autoryzacji uzytkownika na STB w systemie.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param string $password haslo do konta uzywtkonika np. '12345678'
     * @return array
     */
    public function postCustomerUpdatePassword($userName, $password)
    {
        return $this->setParams('updatePassword', 'isp', array(
            'userName' => $userName,
            'password' => $password,
        ))->execCall();
    }

    /**
     * Pobieranie wszystkich subskrypcji.
     *
     * @return array
     */
    public function getSubscriptionAll()
    {
        return $this->setParams('getAllSubscriptions')->execCall();
    }

    /**
     * Pobieranie listy wszystkich subskrypcji zakupionych/aktywowanych pomiedzy datami.
     *
     * @param string $startDate data np. '2020-09-30T23:59:59.999', trzeba podac mikrosekundy format('Y-m-d\TH:i:s.u')
     * @param string $endDate data np. '2020-09-30T23:59:59.999', trzeba podac mikrosekundy format('Y-m-d\TH:i:s.u')
     * @param string $currency waluta jako string np. 'PLN'
     * @return array
     */
    public function getSubscriptionPurchasedBetween($startDate, $endDate, $currency = 'PLN')
    {
        return $this->setParams('listAllServicesPurchasedBetween', 'isp', array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currencyA3' => $currency
        ))->execCall();
    }

    /**
     * Anulowanie subskrypcji uzytkownika na koniec okresu rozliczeniowego.
     * Aby pobrać $serviceId, trzeba najpierw wywołać listę aktywnych usług na uzytwkoniku,
     * nastepnie podać id usługi np. 2307058, data zakończenai jest na ostatni dzień miesiąca.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param int    $serviceId identyfikator subskrypcji '0000000'
     * @param string $endDate data np. '2020-09-30T23:59:59.999', trzeba podac mikrosekundy format('Y-m-d\TH:i:s.u')
     * @return array
     */
    public function postSubscriptionCancel($userName, $serviceId, $endDate)
    {
        return $this->setParams('cancelSubscription', 'isp', array(
            'userName' => $userName,
            'serviceId' => $serviceId,
            'endTime' => $endDate,
        ))->execCall();
    }

    /**
     * Anulowanie subskrypcji uzytkownika natychmiastowo.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param int    $serviceId identyfikator subskrypcji '0000000'
     * @param int    $forceEndDate w formacie Timestamp np. '1597240379'
     * @return array
     */
    public function postSubscriptionCancelForced($userName, $serviceId, $forceEndDate)
    {
        return $this->setParams('cancelSubscriptionForced', 'isp', array(
            'userName' => $userName,
            'serviceId' => $serviceId,
            'forceEndDate' => $forceEndDate,
        ))->execCall();
    }

    /**
     * Tworzenie nowej subskrypcji dla uzytkownika.
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param string $packageName nazwa subskrypcji
     * @param string $currency waluta subskrypcji np. 'PLN'
     * @param array  $paymentMethod metoda platnosci podana jako pelna tablica, pobrać z 'getPaymentMethods'
     * @return array
     */
    public function postSubscriptionCreate($userName, $packageName, $currency = 'PLN', $paymentMethod = array())
    {
        return $this->setParams('createSubscription', 'isp', array(
            'userName' => $userName,
            'externalId' => $packageName,
            'currencyA3' => $currency,
            'paymentMethod' => $paymentMethod,
        ))->execCall();
    }

    /**
     * Pobiera listę urzadzeń (stb) przypisanch do uzytkownika (klienta).
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @return array
     */
    public function getDevicesAssignedToClient($userName)
    {
        return $this->setParams('getDevicesAssignedToClient', 'devices', array(
            'userName' => $userName,
        ))->execCall();
    }

    /**
     * Dodawanie nowego urządzenia i przypisanie go do uzytkownika.
     *
     * @param string $type najczesciej producent np. 'TVIP'
     * @param string $model model set top boxa np. 'TVIP s605'
     * @param string $profile profil urzadzenia np. 'tvip'
     * @param string $mac adres mac urzadzenia np. '10:27:BE:00:00:00'
     * @param string $serial numer seryjny, dla TVIP np. TVIP-1027BE000000'
     * @param string $userName nazwa uzytkownika np. '0000'
     * @return array
     */
    public function postDeviceCreate($type, $model, $profile, $mac, $serial, $userName)
    {
        return $this->setParams('addDevice', 'devices', array(
            'devices' => array(
                array(
                    'type' => $type,
                    'model' => $model,
                    'profile' => $profile,
                    'mac' => $mac,
                    'serial' => $serial,
                ),
            ),
            'userName' => $userName,
        ))->execCall();
    }

    /**
     * Usuwanie urządzenia lub kilku od wybranego uzytkownika (klienta).
     *
     * @param string $userName nazwa uzytkownika np. '0000'
     * @param array $macs lista mac adresow w tablicy
     * @return array
     */
    public function postDeviceDelete($userName, $macs = array())
    {
        return $this->setParams('removeDevice', 'devices', array(
            'userName' => $userName,
            'macList' => $macs,
        ))->execCall();
    }

}