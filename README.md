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
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'zawody_sportowe';
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

## 👨‍💻 Autor
**Kacper Kałużny** ([Example7](https://github.com/Example7))
