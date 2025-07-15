# 🏃‍♂️ ZawodySportoweApp

Aplikacja webowa napisana w PHP do obsługi zawodów sportowych. Umożliwia rejestrację użytkowników, logowanie, zarządzanie zawodami i generowanie raportów PDF. Projekt powstał w ramach studiów na kierunku **Programowanie Internetowych Aplikacji Biznesowych**.

---

## 🧰 Technologie

- PHP 7+
- MySQL
- HTML, CSS
- PDF (generowanie raportów)
- phpMyAdmin / XAMPP
- Git

---

## ⚙️ Funkcje aplikacji

- Rejestracja i logowanie użytkowników
- Zarządzanie zawodami sportowymi
- Przegląd wyników
- Eksport raportu do pliku PDF
- Panel konta użytkownika
- Walidacja danych
- Bezpieczne logowanie (hasła hashowane)

---

## ▶️ Jak uruchomić lokalnie

### 1. Wymagania

- XAMPP lub podobny serwer lokalny (Apache + MySQL)
- phpMyAdmin
- Przeglądarka

### 2. Krok po kroku

1. Skopiuj cały folder projektu do `C:\xampp\htdocs\ZawodySportoweApp`
2. Uruchom `XAMPP` → włącz **Apache** i **MySQL**
3. Wejdź w przeglądarce na:  
http://localhost/ZawodySportoweApp/

5. W `phpMyAdmin` utwórz nową bazę danych, np. `zawody_sportowe`
6. Zaimportuj plik SQL:  
📁 `database/zawody_sportowe.sql`
7. Sprawdź dane połączeniowe w `config.php`:

```php
define("SERWER", "127.0.0.1");
define("UZYTKOWNIK", "root");
define("HASLO", "");
define("NAZWA_BAZY", "zawody_sportowe");
```

## 💾 Folder database/

Zawiera gotowy skrypt SQL:
- zawody_sportowe.sql

Struktura bazy danych obejmuje:
- Tabele użytkowników
- Tabele zawodów
- Wyniki
- Kategorie
- Sesje logowania itd.

---

## 🗂️ Zawartość projektu

- `index.php`, `logowanie.php`, `rejestracja.php`, `konto.php` – logika logowania i rejestracji użytkowników
- `zawody.php`, `wyniki.php`, `raport_pdf.php` – zarządzanie zawodami i generowanie raportów
- `funkcje.php` – funkcje pomocnicze (m.in. do bazy)
- `config.php` – konfiguracja połączenia z bazą danych
- `composer.json` – plik Composera (używany do generowania PDF-ów)
- `style.css` – stylowanie
- `images/` – zawiera:
  - `raport_wynikow.pdf` – przykładowy raport PDF
  - `logos/` – loga zawodów
- `uzytkownicy/` – wygenerowane przez organizatora listy użytkowników z bazy danych
- `errors/error_log.txt` – logi błędów (np. problemów z połączeniem do bazy)
- `database/zawody_sportowe.sql` – skrypt SQL tworzący i wypełniający bazę danych

---

## 🔐 Dane testowe do logowania

Możesz zalogować się na jedno z poniższych kont:

| Login        | Hasło | Rola        |
|--------------|-------|-------------|
| uzytkownik48 | haslo | uczestnik   |
| kacper       | haslo | organizator |

---

## 👨‍💻 Autor
**Kacper Kałużny** ([Example7](https://github.com/Example7))
