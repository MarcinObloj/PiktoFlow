# PiktoFlow - Nowoczesna Platforma AAC

PiktoFlow to zaawansowana, webowa aplikacja do komunikacji wspomagającej i alternatywnej (AAC - Augmentative and Alternative Communication). Została stworzona z myślą o dzieciach w spektrum autyzmu, z porażeniem mózgowym oraz innymi trudnościami w komunikacji werbalnej. 

Aplikacja łączy w sobie intuicyjny interfejs dla dziecka z potężnym panelem zarządzania dla rodzica lub terapeuty, oferując funkcje niespotykane w wielu komercyjnych rozwiązaniach.

---

## Główne Funkcje

PiktoFlow nie jest zwykłą tablicą obrazkową. To kompleksowe narzędzie terapeutyczne wyposażone w:

* **Inteligentny System Audio:** Tablica potrafi budować całe zdania, płynnie łącząc standardowy syntezator mowy (Text-to-Speech) z własnymi nagraniami audio dodanymi przez opiekuna (nagrywanymi bezpośrednio w przeglądarce za pomocą Web MediaRecorder API).
* **CVI Mode (Wysoki Kontrast):** Dedykowany tryb dla dzieci z korowymi zaburzeniami widzenia. Zmienia układ kolorystyczny na głęboką czerń z jaskrawo żółtymi elementami, maksymalizując widoczność i skupienie.
* **Wsparcie dla Eyetrackera:** Wbudowana integracja z WebGazer.js pozwala na obsługę tablicy za pomocą wzroku, co otwiera aplikację na dzieci ze znaczną niepełnosprawnością ruchową.
* **Wizualny Plan Dnia:** Dedykowany moduł prezentujący sekwencje "Najpierw -> Potem -> Następnie", redukujący lęk u dzieci ze spektrum autyzmu poprzez budowanie przewidywalności.
* **Generator Tablic Offline:** Możliwość wygenerowania i wydrukowania (do PDF) idealnie sformatowanej siatki piktogramów, gotowej do zalaminowania i używania w miejscach bez dostępu do elektroniki.
* **Integracja z API ARASAAC:** Wbudowana wyszukiwarka ponad 10 000 darmowych piktogramów z hiszpańskiej bazy ARASAAC. System potrafi również automatycznie dobrać i przypisać początkowe obrazki na podstawie hobby wpisanego podczas tworzenia profilu dziecka.
* **Blokada Rodzicielska:** Moduły ustawień i wychodzenia z tablicy są zabezpieczone kodem PIN, aby dziecko nie mogło przypadkowo opuścić środowiska komunikacyjnego.
* **Analityka i Statystyki:** System w tle loguje kliknięcia w poszczególne piktogramy, co w przyszłości pozwala terapeutom analizować najczęściej używane słowa i postępy w komunikacji.

---

## Stack Technologiczny

Projekt opiera się na nowoczesnej architekturze połączonej (monolit z reaktywnym frontendem), gwarantującej błyskawiczne działanie bez przeładowywania stron (SPA).

| Warstwa | Technologie |
| :--- | :--- |
| **Frontend** | Vue.js 3 (Composition API), Tailwind CSS, Inertia.js |
| **Backend** | Laravel 11, PHP 8.x |
| **Baza Danych** | MySQL (uruchamiana w kontenerze Docker) |
| **Środowisko** | Laravel Sail (Docker) |
| **Zewnętrzne API** | ARASAAC API, Web Speech API, MediaRecorder API |

---

## Instalacja i Uruchomienie (Lokalnie)

Aby uruchomić aplikację w swoim środowisku lokalnym za pomocą Docker / Laravel Sail, wykonaj poniższe kroki:

**1. Sklonuj repozytorium**
```bash
git clone [https://github.com/TwojLogin/PiktoFlow.git](https://github.com/TwojLogin/PiktoFlow.git)
cd PiktoFlow
```
**2. Zainstaluj zależności PHP

```bash
composer install
```
**3. Skonfiguruj plik środowiskowy

```bash
cp .env.example .env
```
**4. Uruchom kontenery (Laravel Sail)

```bash
./vendor/bin/sail up -d
```
**5. Wygeneruj klucz aplikacji i zmigruj bazę danych

```bash

./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
```
**6. Zainstaluj i skompiluj zasoby frontendowe

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```
**Aplikacja będzie dostępna w przeglądarce pod adresem: http://localhost.

**Struktura Aplikacji
Dashboard / Moje Dzieci: Centralny punkt zarządzania profilami podopiecznych (edycja awatara, przełącznik trybu CVI).

Tablica (Board): Główny ekran komunikacyjny dla dziecka. Obsługuje przeciąganie (Drag&Drop), odtwarzanie własnych nagrań i syntezę mowy.

Plan Dnia (Schedule): Wizualny harmonogram dzienny.

Katalog Słów: Zarządzanie przypisanymi piktogramami, wyszukiwanie w API ARASAAC oraz kreator własnych kart graficznych.

Nota prawna dot. Piktogramów:
Piktogramy używane domyślnie w aplikacji pochodzą z bazy ARASAAC (https://www.google.com/search?q=http://arasaac.org), stworzonej przez Sergio Palao. Są one rozpowszechniane na licencji Creative Commons BY-NC-SA.
