# API Hibox
## Komunikacja poprzez API do systemu 4box

Dostęp do API jest wyłącznie na życzenie Operora, w tym celu należy się zgłosić do NaszaWizja o przydzielenie dostępu. Wymagania systemowe potrzebne do komunikacji:
- Przydzielony login i hasło do platformy 4BOX moduł API
- wyłączony moduł Payment modules – verified biling. Właczony Payment modules – none opcjonalnie dodatkowo voucher.
- Zgodna nazwa external_code subskrypcji w systemie operatora oraz na platformie 4BOX
- Zgodna nazwa użytkownika w systemie operatora z username po stronie platformy 4BOX

> Użytkownicy założeni w systemie przed uruchomieniem API do poprawnego działania komunikacji API powinni być założeni z dodatkowym prefixem. Jeśli operator ma kod 99 to jego wszyscy użytkownicy powinni mieć prefix "99-" (słownie dziewięćdziesiąt dziewięć myślnik) a następnie id z systemu operatora - pełny wygląd nazwy użytkownika -> 99-00123 lub 99-stefan.jakis itp. 

## Instalacja

Bibliotekę pobieramy i instalujemy poprzez composer:

```sh
composer require xsme/php-hibox-api
```

## Funkcje

Spis wszystkich funkcji z opisem i odpowiedzią zwrotną.


```php
// $location - uzyskujemy z NaszaWizja
// $username - uzyskujemy z NaszaWizja
// $password - uzyskujemy z NaszaWizja
$hibox = new Hibox($location, $username, $password)

// Pobieranie ustawień ISP z systemu Hibox.
$test = $hibox->getSystemSettings();

// Pobieranie wszystkich metod płatności.
$test = $hibox->getSystemPaymentMethods();

// Pobieranie danych o uzytwkoniku według jego identyfikatora.
$test = $hibox->getCustomerByName(1234);

// Pobieranie wszystkich uzytkowników naleacych do ISP.
$test = $hibox->getCustomerAll();

// Pobieranie listy wszystkich subskrypcji dla uzytkownika.
$test = $hibox->getCustomerPurchases(
    1234,
    '2020-09-30T23:59:59.999',
    '2020-09-30T23:59:59.999',
    'PLN'
);

// Pobieranie listy aktywnych subskrypcji dla uztkownika.
$test = $hibox->getCustomerActivePurchases(1234, 'PLN');

// Pobieranie dodatkowych informacji o uzytkowniku.
// PIN do zakupow, PIN do kontroli rodzicielskiej.
$test = $hibox->getCustomerRights(1234);

// Dodawanie nowego uzytkownika do systemu.
$test = $hibox->postCustomerCreate();

// Usuwanie uzytkownika z systemu Hibox.
$test = $hibox->postCustomerDelete();

// Zmiana kodu PIN uzytkownika do weryfikowania zakupów.
// To nie jest kod PIN do kontroli rodzicielskiej.
$test = $hibox->postCustomerUpdatePinCode();

// Zmiana kodu PIN do kontroli rodzicielskiej.
$test = $hibox->postCustomerUpdateLockCode();

// Zmiana hasła do autoryzacji uzytkownika na STB w systemie.
$test = $hibox->postCustomerUpdatePassword();

// Pobieranie wszystkich subskrypcji.
$test = $hibox->getSubscriptionAll();

// Pobieranie listy wszystkich subskrypcji zakupionych/aktywowanych pomiedzy datami.
$test = $hibox->getSubscriptionPurchasedBetween();

// Anulowanie subskrypcji uzytkownika na koniec okresu rozliczeniowego.
// Aby pobrać $serviceId, trzeba najpierw wywołać listę aktywnych usług na uzytwkoniku,
// nastepnie podać id usługi np. 2307058, data zakończenai jest na ostatni dzień miesiąca.
$test = $hibox->postSubscriptionCancel();

// Anulowanie subskrypcji uzytkownika natychmiastowo.
$test = $hibox->postSubscriptionCancelForced();

// Tworzenie nowej subskrypcji dla uzytkownika.
$test = $hibox->postSubscriptionCreate();

// Pobiera listę urzadzeń (stb) przypisanch do uzytkownika (klienta).
$test = $hibox->getDevicesAssignedToClient();

// Dodawanie nowego urządzenia i przypisanie go do uzytkownika.
$test = $hibox->postDeviceCreate();

// Usuwanie urządzenia lub kilku od wybranego uzytkownika (klienta).
$test = $hibox->postDeviceDelete();
```