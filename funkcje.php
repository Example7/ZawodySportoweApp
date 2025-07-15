<?php

// Ładowanie pliku konfiguracyjnego z danymi do połączenia z bazą danych
require_once 'config.php';

// Funkcja do otwierania połączenia z bazą danych
function otworz_polaczenie()
{
    // Tworzymy połączenie z bazą danych
    $polaczenie = mysqli_connect(SERWER, UZYTKOWNIK, HASLO, NAZWA_BAZY);

    // Jeśli połączenie się nie uda zapis błądu i wyjątek
    if (!$polaczenie) {
        logError("Nieudane połączenie z serwerm: " . mysqli_connect_error());
        throw new Exception("Nieudane połączenie z serwerem: " . mysqli_connect_error());
    }

    return $polaczenie;
}

// Funkcja do zamykania połączenia z bazą danych
function zamknij_polaczenie($polaczenie)
{
    mysqli_close($polaczenie);
}

// Funkcja do wykonywania zapytań SQL
function wykonaj_zapytanie($polaczenie, $zapytanie)
{
    $wynik = mysqli_query($polaczenie, $zapytanie); // Wykonanie zapytania SQL

    // Jeśli zapytanie się nie udało log błąde i zatrzymanie skryptu
    if (!$wynik) {
        logError("Niepoprawne zapytanie.");
        die("Błąd zapytania: " . mysqli_error($polaczenie));
    }

    return $wynik;
}

// Funkcja do pobierania ścieżki do logo na podstawie nazwy zawodu
function getLogoPath($nazwaZawodu)
{
    $basePath = 'images/logos/';
    $nazwaZawoduLower = strtolower($nazwaZawodu);

    // Określenie słów kluczowych i przypisanych nazw plików logo
    $keywordsToLogos = [
        'biegi' => 'Biegi',
        'skok' => 'Skok',
        'igrzyska' => 'Igrzyska',
        'maraton' => 'Maraton',
    ];

    // Iteracja przez słowa kluczowe i sprawdzanie, czy zawód zawiera to słowo
    foreach ($keywordsToLogos as $keyword => $logoName) {
        if (strpos($nazwaZawoduLower, $keyword) !== false) {
            // Jeśli słowo kluczowe znajduje się w nazwie zawodów, zwracamy odpowiadające logo
            $fullPath = $basePath . $logoName . '.jpg';
            return file_exists($fullPath) ? $fullPath : 'images/logos/default.jpg'; // Zwracamy ścieżkę do logo lub domyślne logo
        }
    }
    // Jeśli żadne słowo kluczowe nie pasuje, zwracamy domyślne logo
    return 'images/logos/default.jpg';
}

// Funkcja do logowania błędów w pliku logu
function logError($message)
{
    // Określenie lokalizacji pliku logu
    $logFile = 'C:\xampp\htdocs\PHP\zawody_sportowe\errors\error_log.txt';

    // Zapisujemy datę, czas i wiadomość błędu
    $date = date('Y-m-d H:i:s');
    $logMessage = "[$date] $message" . PHP_EOL;

    // Dodajemy do pliku (jeśli plik nie istnieje, zostanie stworzony)
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Funkcja do eksportowania użytkowników do pliku CSV
function exportUsersToCSV()
{
    try {
        $polaczenie = otworz_polaczenie();

        $zapytanie = "SELECT * FROM użytkownicy";
        $wynik = mysqli_query($polaczenie, $zapytanie);

        if (!$wynik) {
            logError("Niepoprawne zapytanie.");
            die("Błąd zapytania: " . mysqli_error($polaczenie));
        }

        // Stworzenie lub otworzenie folderu do zapisania pliku CSV
        $folder = 'użytkownicy/';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true); // Tworzymy folder, jeśli nie istnieje
        }

        // Generujemy nazwę pliku CSV z datą i czasem
        $filename = $folder . 'użytkownicy_' . date('Y-m-d_H-i-s') . '.csv';
        $file = fopen($filename, 'w'); // Otwarcie pliku do zapisu

        // Nagłówki kolumn w CSV
        $columns = ['Id', 'login', 'Imie', 'Nazwisko', 'Email', 'Rola'];
        fputcsv($file, $columns); // Zapisujemy nagłówki

        // Wypisujemy dane użytkowników do pliku CSV
        while ($row = mysqli_fetch_assoc($wynik)) {
            $userData = [
                $row['id_uzytkownika'],
                $row['login'],
                $row['Imie'],
                $row['Nazwisko'],
                $row['email'],
                $row['role']
            ];

            fputcsv($file, $userData); // Zapisujemy dane użytkownika
        }

        fclose($file); // Zamknięcie pliku

        zamknij_polaczenie($polaczenie);

        // Ustawienie komunikatu o zakończeniu eksportu w sesji
        $_SESSION['export_message'] = "Eksport zakończony. Plik zapisany jako: " . $filename;

        // Przekierowanie do strony, aby wyświetlić alert o zakończeniu eksportu
        header("Location: konto.php");
        exit();
    } catch (Exception $e) {
        // Jeśli wystąpił błąd, zapisujemy go i przekierowujemy na stronę z błędem
        logError("Wystąpił błąd: " . $e->getMessage());
        $_SESSION['export_message'] = "Wystąpił błąd: " . $e->getMessage();
        header("Location: konto.php");
        exit();
    }
}
