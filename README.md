PiktoFlow - Nowoczesna Platforma AAC
PiktoFlow to zaawansowana, webowa aplikacja do komunikacji wspomagającej i alternatywnej (AAC - Augmentative and Alternative Communication). Została stworzona z myślą o dzieciach w spektrum autyzmu, z porażeniem mózgowym oraz innymi trudnościami w komunikacji werbalnej.

Aplikacja łączy w sobie intuicyjny interfejs dla pacjenta z zaawansowanym panelem zarządzania i analityki dla rodzica lub terapeuty, oferując funkcje niespotykane w komercyjnych, statycznych rozwiązaniach.

Główne Funkcje
PiktoFlow to kompleksowe narzędzie terapeutyczne, wyposażone w:

Inteligentny System Audio: Tablica potrafi budować całe zdania, płynnie łącząc systemowy syntezator mowy (Web Speech API / TTS) z autorskimi nagraniami audio dodanymi przez opiekuna (nagrywanymi bezpośrednio w przeglądarce za pomocą Web MediaRecorder API).

Tryb Wysokiego Kontrastu (CVI Mode): Dedykowany widok dla pacjentów z korowymi zaburzeniami widzenia. Zmienia układ kolorystyczny na głęboką czerń z jaskrawymi obramowaniami, maksymalizując widoczność i redukując przebodźcowanie.

Wsparcie dla Eyetrackera: Zoptymalizowane obszary aktywne (hitboxy) oraz wbudowana integracja z WebGazer.js pozwalają na obsługę tablicy za pomocą wzroku, otwierając system na pacjentów ze znaczną niepełnosprawnością ruchową.

Wizualny Plan Dnia: Moduł prezentujący sekwencje chronologiczne, redukujący lęk u pacjentów ze spektrum autyzmu poprzez budowanie rutyny i przewidywalności.

Generator Tablic Offline: Możliwość wygenerowania i eksportu do formatu PDF idealnie sformatowanej siatki piktogramów, gotowej do wydruku.

Integracja z API ARASAAC i Mapowanie Aliasów: Wbudowana wyszukiwarka połączona z bazą ponad 10 000 darmowych piktogramów, wspierana autorskim systemem eliminacji homonimii.

Blokada Rodzicielska: Moduły ustawień i wyjścia z tablicy są zabezpieczone, zapobiegając przypadkowemu opuszczeniu środowiska komunikacyjnego przez dziecko.

Zaawansowana Telemetria: System w tle loguje zdarzenia (kliknięcia, pełne zdania), automatycznie wyliczając wskaźnik postępu językowego MLU (Mean Length of Utterance).

Stack Technologiczny
Projekt opiera się na nowoczesnej architekturze tzw. nowoczesnego monolitu, gwarantującej błyskawiczne działanie interfejsu (SPA) przy zachowaniu wysokiego bezpieczeństwa danych.

Frontend: Vue.js 3 (Composition API), Tailwind CSS, Inertia.js

Backend: Laravel 11, PHP 8.x

Baza Danych: PostgreSQL (uruchamiana w kontenerze Docker)

Środowisko: Laravel Sail (Docker)

Zewnętrzne API: ARASAAC API, Web Speech API, MediaRecorder API

Instalacja i Uruchomienie (Środowisko Lokalne)
Projekt wykorzystuje środowisko konteneryzacji Docker (Laravel Sail). Aby uruchomić aplikację lokalnie, skopiuj po kolei poniższe komendy do swojego terminala:

1. Sklonuj repozytorium i przejdź do katalogu projektu:

git clone https://github.com/MarcinObloj/PiktoFlow.git
cd PiktoFlow
2. Skopiuj domyślny plik środowiskowy:

cp .env.example .env
3. Zainstaluj zależności backendowe:
(Komenda pobiera paczki Composera przez tymczasowy kontener - nie wymaga instalacji PHP na systemie hosta).

docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
4. Uruchom kontenery w tle:

./vendor/bin/sail up -d
5. Skonfiguruj aplikację i zasil bazę danych:
(Generowanie klucza, migracja tabel, wgranie danych testowych oraz linkowanie pamięci masowej).

./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
6. Zainstaluj i skompiluj zasoby frontendowe:

./vendor/bin/sail npm install
./vendor/bin/sail npm run build
Aplikacja jest gotowa do pracy i dostępna pod adresem: http://localhost

Struktura Aplikacji (Moduły Główne)
Dashboard (Panel Główny): Centralny punkt zarządzania profilami pacjentów, konfiguracją syntezatora mowy (TTS) oraz trybami dostępności (np. tryb CVI).

Tablica Komunikacyjna (Board): Główny, zoptymalizowany interfejs pacjenta do budowania komunikatów z wykorzystaniem mechanizmu Paska Zdania.

Trening Syntaktyczny (Quizy): Zgamifikowany moduł edukacyjny wymuszający na pacjencie budowanie poprawnych gramatycznie struktur.

Panel Analityczny: Przestrzeń diagnostyczna renderująca na żywo wykresy telemetrii i prognozę wskaźnika MLU z wykorzystaniem regresji liniowej.

Katalog Zasobów: Zarządzanie własnymi grafikami, przypisywanie piktogramów oraz bezpośrednia komunikacja z API.

Nota Prawna
Piktogramy używane domyślnie w aplikacji pochodzą z bazy ARASAAC (https://arasaac.org), stworzonej przez Sergio Palao w ramach rządu Aragonii (Hiszpania). Są one udostępniane na licencji otwartej Creative Commons BY-NC-SA.
