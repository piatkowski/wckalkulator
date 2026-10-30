# Plan napraw WC Kalkulator

Stan na 2026-10-30. Plan powstał na podstawie `REVIEW.md` oraz zmian obecnych w katalogu roboczym. „Zaimplementowane” oznacza, że widać zmianę w kodzie; nie oznacza automatycznie potwierdzenia działania na sklepie. Repozytorium ma wiele niezacommitowanych zmian — przed dalszą pracą zachować je i rozdzielić na commity dopiero po przeglądzie.

## Naprawione w kodzie roboczym — wymagają domknięcia dowodami

- **R01, R11, R20, R34 — uprawnienia i zapis konfiguracji:** dodano capability dla CPT, sprawdzanie nonce/uprawnień/obiektu przy zapisie i publikacji, walidację schematu oraz ograniczenie pola własnego JavaScript do `unfiltered_html`. Do weryfikacji: role administratora i managera, autosave, rewizje, błędne schematy i brak eskalacji uprawnień.
- **R02, R14, R15, R17, R21–R23 — wejście i walidacja:** dodano walidację konfiguracji, typów wejścia, opcji i reguł widoczności po stronie serwera oraz bezpieczniejsze obchodzenie się z parametrami i wartościami. Do weryfikacji: spreparowane requesty, `0`/`'0'`/pusty string, wielokrotne produkty oraz zgodność frontendu z backendem.
- **R03, R31, R35 — frontend i renderowanie:** zmieniono źródła JS oraz assety dostarczane; wdrożono bezpieczniejsze składanie elementów i obsługę błędów/podglądu. Wymagane sprawdzenie w przeglądarce, w tym wielu formularzy, znaków HTML/cudzysłowów i zgodności plików minifikowanych ze źródłami.
- **R04, R05, R12, R19, R25 — kontekst i cena:** kod resetuje/odtwarza kontekst fieldsetu, waliduje wyniki cenowe, aktualizuje obsługę ilości i prezentację podatku. Do weryfikacji pozostaje pełny przepływ ceny z filtrami WooCommerce, rabatami innych wtyczek, zmianą ilości, AJAX, odtworzeniem koszyka i zamówieniem.
- **R06, R07 — odmowa walidatora i pojedyncza data:** poprawiono zachowanie odmowy wcześniejszego walidatora oraz zwracanie/parsowanie wartości datepickera. Dodać dowód regresji w klasycznym checkout i pozycji zamówienia.
- **R08–R10 — nowe uploady prywatne i trwałość checkoutu:** dodano prywatny magazyn, kontrolę dostępu do pobierania i walidację/finalizację plików. **Migracja plików i URL-i zapisanych przed zmianą nadal nie jest rozwiązana** — istniejące rekordy muszą mieć osobny, bezpieczny plan migracji/wycofania.
- **R13 — mnożnik stocku:** dodano przeliczenie ilości magazynowej oraz obsługę zwrotów. Wymaga testów produktów i wariantów współdzielących stock, anulowania, refundów i błędnych mnożników.
- **R24, R28, R33 — koszyk, snapshot i cykl życia:** kod używa snapshotu konfiguracji dla pozycji koszyka/zamówienia, dodaje obsługę cyklu uninstall/cron oraz zmienia ścieżkę edycji koszyka. Do sprawdzenia HPOS, multisite, retry checkoutu, edycja i historyczne pozycje.
- **R29 — kontrola jakości/wydanie:** dodano skrypty lintowania, regresji, budowania assetów i pakowania. Nie uznawać ich za potwierdzenie zgodności produkcyjnej; zachować wersje PHP/WP/WC i wynik każdej komendy.
- **R36 — rozpoznawanie produktu/kontekstu:** zmieniono wykrywanie aktywnego produktu i kontekst formuły; sprawdzić strony produktu, shortcode, katalog i wiele instancji.
- **T03 — datepicker:** poprawiono metodę wartości dla zamówienia i kontrolę formatu daty.
- **Uzupełnienie P2 w tej sesji — cache i kontrakt formuły:** cache przypisań fieldsetów i parametrów globalnych jest rozdzielony per blog, a cache przypisań unieważnia się po zmianach fieldsetów i taksonomii produktów. Usunięto zbędny argument `per_page` z zapytania fieldsetów. Panel informuje, że wynik pola formula jest osobny i nie zasila głównego wzoru ceny.

## Wymaga dalszej poprawy lub rozstrzygnięcia

### Priorytet P1 — zamknąć przed wdrożeniem

1. **Bezpieczeństwo uploadów istniejących przed zmianą (R08):** ustalić inwentaryzację i migrację starych plików/odnośników; potwierdzić, że nie pozostają publicznie dostępne. Nie usuwać ich bez mapowania do zamówień.
2. **Cena i stock w prawdziwym WooCommerce (R05, R12, R13; T01):** uruchomić scenariusze finansowe na wskazanym środowisku: 1→10→1 szt., rabat 20→18 i dopłata 5, AJAX i zwykły formularz, sesja koszyka, zamówienie, podatek, warianty, refund i kilka pozycji. Ustalić kolejność filtrów cenowych i potwierdzić brak kumulacji przy ponownym `calculate_totals`.
3. **Walidacja widoczności i schematu (R14, R20, T06):** sprawdzić macierz AND/OR, pole wymagane ukryte/widoczne, brak JS, brakującą referencję, cykl i ręcznie spreparowane POST; te same wyniki muszą obowiązywać w podglądzie, add-to-cart i checkout.
4. **Uprawnienia (R01, R11, R34):** potwierdzić testami capability mapę ról, nonce, właściwy typ obiektu, publikację kodu i blokowanie nieautoryzowanych zapisów.

### Priorytet P2 — domknąć funkcjonalność i kompatybilność

- **T02 / R26 — kontrakt pól formuły:** UI informuje teraz, że wynik pola formula nie zasila głównej ceny. Pozostaje sprawdzić tekst pomocy w całym interfejsie i materiałach użytkownika; zmiana semantyki na zależności obliczeń wymaga osobnej decyzji produktowej i migracji zgodności.
- **R18 — Blocks, Store API i HPOS:** obecne adaptery nie stanowią dowodu pełnej integracji. Sprawdzić zakup, walidację, cenę, pliki i metadane w classic, Blocks/Store API oraz HPOS włączonym i wyłączonym; dopiero potem deklarować kompatybilność.
- **T07 / R30 — konflikty i cache:** odtworzyć zgłoszone konflikty z nazwą/wersją dodatku oraz cold/warm page/object cache; sprawdzić wiele kalkulatorów na stronie. Bez reprodukcji raportować „nieodtworzone”, nie „naprawione”.
- **T08 — fieldsety i trwałość przypisania:** zbędny argument `per_page` usunięto, a cache przypisań jest unieważniany po zapisach i zmianach taksonomii. Pozyskać i porównać diff PR #39, następnie sprawdzić przypisania ponad domyślnym limitem, priorytety, publikację oraz nowy request. Nie zmieniać semantyki wyboru fieldsetu bez reprodukcji.
- **T09 — surowy kod w koszyku:** uzyskać dokładne pole, wartość, motyw i konfigurację zgłoszenia; sprawdzić tekst, dozwolony HTML i override motywu.
- **R04/T05 — izolacja kontekstu:** test A→B→produkt bez fieldsetu oraz wiele wywołań AJAX i pozycji koszyka w tym samym requestcie.
- **R24, R33 — edycja i snapshot:** sprawdzić zastąpienie pozycji bez utraty starej przy błędzie, historyczne konfiguracje, odtworzenie sesji i niezmienność znaczenia zamówienia po edycji fieldsetu.
- **R27, R28 — ścieżki i multisite:** zweryfikować niestandardowe ścieżki instalacji, blog-specific cache, przełączanie witryn i parowanie `switch_to_blog()`/`restore_current_blog()`.
- **R32 — bezstratność edytora:** potwierdzić zachowanie opcji domyślnej i stabilnych identyfikatorów po edycji oraz ponownym zapisie.

### Priorytet P3 — jakość produktu

- **R37:** przegląd dostępności, klawiatury/focusu, responsywności oraz tłumaczeń po zakończeniu zmian funkcjonalnych.
- **R29:** utrzymać powtarzalny proces wydania, ale uzupełnić go o macierz środowisk: PHP 7.4 i wspierane PHP 8.x, WordPress 7.1.4, dokładny patch WooCommerce 11.2.x, classic/Blocks, HPOS, role i multisite stosownie do zmian.

## Sygnały z publicznych zgłoszeń

- **#29 i zgłoszenie AJAX add-to-cart:** scenariusz ceny po rabacie jest częścią testów T01; historyczne zgłoszenie nie potwierdza zachowania na docelowym WC.
- **#31:** wymaga decyzji kontraktowej opisanej w T02.
- **#34, #38 i zgłoszenia o cache/widoczności/koszyku:** używać jako przypadków reprodukcyjnych; same tytuły nie dowodzą przyczyny.
- **#40:** wymaga browser testu nazw i etykiet z cudzysłowami/encjami; brak potwierdzonego PoC nie oznacza zakończonej weryfikacji.
- **#42:** test pojedynczej daty i zakresu od walidacji do e-maila.
- **PR #39:** status i diff nie były dostępne w przeglądzie; nie oznaczać jako naprawionego bez porównania patcha i reprodukcji.

## Kryterium zamknięcia

Przy każdej pozycji oznaczyć status w `REVIEW.md` dopiero po sprawdzeniu konkretnego testu/regresji. Zapis audytu powinien zawierać dokładną wersję PHP, WordPressa i WooCommerce oraz ustawienia HPOS/tax/Blocks stosowne do przypadku. Obecny przegląd zmian jest statyczny: nie uruchamiano tutaj testów ani sklepu, więc pozycje opisane wyżej jako wdrożone są implementacją w katalogu roboczym, a nie potwierdzoną naprawą produkcyjną.
