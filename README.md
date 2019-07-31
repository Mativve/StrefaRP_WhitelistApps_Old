# Stare Aplikacje StrefaRP.pl
Jako iż ktoś sprzedawał ten panel jako "swój", postanowiliśmy go udostępnić. Czemu by nie?

_- Zespół Developerów StrefaRP.pl_

## Wgranie na serwer
Po prostu wrzuć pliki tam, skąd serwowany jest content na Twoim serwerze webowym. Najczęściej jest to `/var/www`, lecz może się to różnić.

## Podpięcie pod bazę danych
Utwórz bazę danych pod swój użytek i skonfiguruj do niej uprawnienia. Najlepiej jest utworzyć oddzielnego użytkownika i przyznać mu uprawnienia do indywidualnej bazy, lecz to pozostawiamy już Wam.
Następnie zaimportuj plik apps.sql znajdujący się w tym repozytorium, celem utworzenia wymaganych tabel.

## Konfiguracja skryptu
### Główna
#### user.php
Linijki 9-12 - uzupełnij dane do bazy MySQL.
#### status.php
Linijki 9-12 - uzupełnij dane do bazy MySQL.
#### sendapp.php
Linijki 7-10 - uzupełnij dane do bazy MySQL.
#### index.php
Linijki 9-12 - uzupełnij dane do bazy MySQL.
#### authorize.php
Utwórz nową aplikację w panelu deweloperskim Discorda, zanotuj jej Client Secret oraz Client ID. 
Utwórz nowy Redirect URL w zakładce OAuth, podając adres URL do głównej strony podań.
#### ajax.php
Linijki 27-30 - uzupełnij dane do bazy MySQL. Pomiń linijkę 8.
#### admin.php
Linijki 6-9 - uzupełnij dane do bazy MySQL. Linijki 11-14 - uzupełnij dane do bazy MySQL **swojego forum, jeśli posiadasz forum na silniku __IPS Community Suite__**. W przeciwnym wypadku wykomentuj wszystkie linijki, które wykonują na niej operacje. 

Po wykonaniu powyższych instrukcji na wszystkich powyższych plikach, podmień we wszystkich frazę `aplikacje.strefarp.pl` na Twój adres URL do głównej strony podań.
### Administracja
### Zmiany w plikach
#### ajax.php
Zlokalizuj linijkę 8. Powinna wyglądać tak:
```php
array("NickDiscord",					"4 cyferki po #",				LongID discorda),
```
Następnie, dla każdej osoby, której chcesz przyznać uprawnienia do sprawdzania podań, uzupełnij takową linijkę. Przykład:
```php
array("Michał",					"6104",				412867223925948428),

```
Pamiętaj, że ostatni element tablicy to typ `long`, nie `string`.
Jeśli nie wiesz, skąd wziąć tzw. long id Discorda, otwórz ustawienia swojego konta, przejdź do `Wygląd`, zjedź na sam dół i zaznacz `Tryb dewelopera`. Teraz kliknij prawym na swój awatar w czacie/na liście użytkowników po prawej stronie i wybierz `Kopiuj ID`.

Przykładowa konfiguracja dla trzech administratorów:
```php
array("Michał",					"6104",				412867223925948428),
array("Ezi",					"0001",				217566090073473026),
array("Buli",					"7777",				255509627469430784),
```
#### admin.php
Jeśli w poprzednich punktach nie uzupełniłeś dane do bazy danych swojego forum albo nie korzystasz z wymaganego silnika, pomiń ten punkt. Jeśli jest inaczej, to w linijce `177` podaj ID grupy forum, do której mają być przydzieleni użytkownicy z Whitelistą. U nas było to `22`. Następnie znajdź w tym pliku następujący tekst i zmień go:

`IdSerwera` - ID Twojego serwera Discord. Jego ID pobierzesz tak samo jak swoje.

`IdGrupyWhitelist` - ID roli Whitelist na Twoim serwerze Discord. Tutaj trzeba się nieco bardziej namęczyć, bo jest kilka sposobów na jego pobranie. Możesz użyć bota typu YAGPDB albo Blargbot, uruchomić narzędzia deweloperskie Chrome, albo zrobić to jeszcze inaczej - wyszukaj to, bo bezsensem byłoby to opisywać w tym miejscu.

`TuPodajSwojTokenBota`, `TuPodajTokenBota` - token bota Discord. W konsoli deweloperskiej Discorda otwórz aplikację którą przed chwilą stworzyłeś, wejdź w zakładkę Bot. Skopiuj token, wklej go w tych miejscach. Bota musisz obowiązkowo zaprosić na swój serwer Discord, w przeciwnym razie nie będzie działał poprawnie.

### Panel
Przejdź pod `twojastrona/admin.php`, gdzie `twojastrona` to adres URL strony głównej podań. Tam znajdziesz intuicyjny panel, który powinien we wszystkich funkcjonalnościach mówić sam za siebie.
