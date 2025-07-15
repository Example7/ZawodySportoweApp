<?php
session_start();
require_once 'config.php';
require_once 'funkcje.php';

// Sprawdzanie, czy użytkownik jest zalogowany, jeśli nie, przekierowanie na stronę logowania
if (!isset($_SESSION['zalogowany']) || !$_SESSION['zalogowany']) {
    header('Location: logowanie.php');
    exit();
}

// Deklaracja zmiennych dla błędów, sukcesów oraz bieżących danych użytkownika
$blad_haslo = null;
$blad_dane = null;
$haslo_sukces = null;
$dane_sukces = null;
$current_email = $_SESSION['uzytkownik'];
$current_login = $_SESSION['uzytkownik'];

try {
    $polaczenie = otworz_polaczenie();

    // Zapytanie o dane użytkownika w oparciu o jego login
    $zapytanie = "SELECT email, Imie, Nazwisko, role FROM użytkownicy WHERE login = ?";
    $stmt = mysqli_prepare($polaczenie, $zapytanie);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['uzytkownik']);
    mysqli_stmt_execute($stmt);
    $wynik = mysqli_stmt_get_result($stmt);

    // Przypisanie pobranych danych użytkownika do zmiennych
    if ($wiersz = mysqli_fetch_assoc($wynik)) {
        $current_email = $wiersz['email'];
        $current_imie = $wiersz['Imie'];
        $current_nazwisko = $wiersz['Nazwisko'];
        $current_rola = $wiersz['role'];
    }

    zamknij_polaczenie($polaczenie);
} catch (Exception $e) {
    logError("Wystąpił błąd: " . $e->getMessage());
    $blad = "Wystąpił błąd: " . $e->getMessage();
}

// Obsługa formularza zmiany hasła
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['stare_haslo'], $_POST['nowe_haslo'], $_POST['ponownie_nowe_haslo'])) {
        $stare_haslo = $_POST['stare_haslo'];
        $nowe_haslo = $_POST['nowe_haslo'];
        $ponownie_nowe_haslo = $_POST['ponownie_nowe_haslo'];

        // Sprawdzanie, czy nowe hasła są takie same
        if ($nowe_haslo !== $ponownie_nowe_haslo) {
            $blad_haslo = "Hasła nie pasują do siebie.";
        } else {
            try {
                $polaczenie = otworz_polaczenie();

                // Zapytanie o aktualne hasło użytkownika
                $zapytanie = "SELECT haslo FROM użytkownicy WHERE login = ?";
                $stmt = mysqli_prepare($polaczenie, $zapytanie);
                mysqli_stmt_bind_param($stmt, "s", $_SESSION['uzytkownik']);
                mysqli_stmt_execute($stmt);
                $wynik = mysqli_stmt_get_result($stmt);

                // Sprawdzanie, czy stare hasło jest poprawne
                if ($wiersz = mysqli_fetch_assoc($wynik)) {
                    if (password_verify($stare_haslo, $wiersz['haslo'])) {
                        // Hashowanie nowego hasła
                        $hashed_nowe_haslo = password_hash($nowe_haslo, PASSWORD_DEFAULT);
                        $zapytanie = "UPDATE użytkownicy SET haslo = ? WHERE login = ?";
                        $stmt = mysqli_prepare($polaczenie, $zapytanie);
                        mysqli_stmt_bind_param($stmt, "ss", $hashed_nowe_haslo, $_SESSION['uzytkownik']);
                        mysqli_stmt_execute($stmt);
                        $haslo_sukces = "Hasło zostało zmienione.";
                    } else {
                        $blad_haslo = "Nieprawidłowe stare hasło.";
                    }
                } else {
                    $blad_haslo = "Błąd w bazie danych.";
                }

                zamknij_polaczenie($polaczenie);
            } catch (Exception $e) {
                logError("Wystąpił błąd: " . $e->getMessage());
                $blad_haslo = "Wystąpił błąd: " . $e->getMessage();
            }
        }
    }

    // Obsługa formularza zmiany danych użytkownika (login, email)
    if (isset($_POST['login'], $_POST['email'])) {
        $nowy_login = $_POST['login'];
        $nowy_email = $_POST['email'];

        try {
            $polaczenie = otworz_polaczenie();

            // Zapytanie o sprawdzenie, czy login już istnieje
            $zapytanie = "SELECT * FROM użytkownicy WHERE login = ? AND login != ?";
            $stmt = mysqli_prepare($polaczenie, $zapytanie);
            mysqli_stmt_bind_param($stmt, "ss", $nowy_login, $_SESSION['uzytkownik']);
            mysqli_stmt_execute($stmt);
            $wynik = mysqli_stmt_get_result($stmt);

            // Jeśli login już istnieje, wyświetl błąd
            if (mysqli_num_rows($wynik) > 0) {
                $blad_dane = "Taki login już istnieje.";
            } else {
                // Sprawdzanie, czy email już istnieje
                $zapytanie = "SELECT * FROM użytkownicy WHERE email = ? AND login != ?";
                $stmt = mysqli_prepare($polaczenie, $zapytanie);
                mysqli_stmt_bind_param($stmt, "ss", $nowy_email, $_SESSION['uzytkownik']);
                mysqli_stmt_execute($stmt);
                $wynik = mysqli_stmt_get_result($stmt);

                // Jeśli email już istnieje, wyświetl błąd
                if (mysqli_num_rows($wynik) > 0) {
                    $blad_dane = "Taki email już istnieje.";
                } else {
                    // Aktualizacja danych użytkownika w bazie
                    $zapytanie = "UPDATE użytkownicy SET login = ?, email = ? WHERE login = ?";
                    $stmt = mysqli_prepare($polaczenie, $zapytanie);
                    mysqli_stmt_bind_param($stmt, "sss", $nowy_login, $nowy_email, $_SESSION['uzytkownik']);
                    mysqli_stmt_execute($stmt);

                    // Aktualizacja danych sesji
                    $_SESSION['uzytkownik'] = $nowy_login;
                    $dane_sukces = "Dane zostały zaktualizowane.";

                    $current_email = $nowy_email;
                    $current_login = $nowy_login;
                }
            }

            zamknij_polaczenie($polaczenie);
        } catch (Exception $e) {
            logError("Wystąpił błąd: " . $e->getMessage());
            $blad = "Wystąpił błąd: " . $e->getMessage();
        }
    }
}

// Eksport użytkowników do pliku CSV (dostępne tylko dla organizatora)
if (isset($_GET['export']) && $current_rola === 'organizator') {
    exportUsersToCSV();
    header("Location: konto.php");
    exit();
}

// Wylogowanie użytkownika
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konto</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="body">
    <header class="flexbox">
        <div class="margines-header flexbox body-bold">
            <div><a href="index.php">Logo</a></div>
            <div class="flexbox header-links">
                <div><a href="aktualności.php">Aktualności</a></div>
                <div><a href="wyniki.php">Wyniki</a></div>
                <div><a href="zawody.php">Zawody</a></div>
                <!-- Jeśli użytkownik jest zalogowany, pojawia się link do strony konta, w przeciwnym razie link do logowania -->
                <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
                    <div><a href="konto.php">Konto</a></div>
                <?php else: ?>
                    <div><a href="logowanie.php">Logowanie</a></div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section class="flexbox konto">
            <div class="margines">
                <h2 class="title">Zarządzaj kontem</h2>
                <!-- Powitanie użytkownika i wyświetlanie jego imienia, nazwiska oraz roli -->
                <p>Witaj <span class="body-bold"><?= htmlspecialchars($current_imie) ?></span> <span class="body-bold"><?= htmlspecialchars($current_nazwisko) ?></span> w systemie. Twoja rola to <span class="body-bold"><?= htmlspecialchars($current_rola) ?></span>.</p>

                <!-- Jeśli hasło zostało zmienione lub wystąpił błąd przy zmianie hasła, wyświetlenie komunikatu -->
                <?php if ($haslo_sukces): ?>
                    <p class="sukces"><?= htmlspecialchars($haslo_sukces) ?></p>
                <?php elseif ($blad_haslo): ?>
                    <p class="blad"><?= htmlspecialchars($blad_haslo) ?></p>
                <?php endif; ?>

                <!-- Formularz zmiany hasła -->
                <ul class="title">Zmień hasło</ul>
                <li>
                    <form action="" method="POST" class="flexbox konto-zmiana-hasla">
                        <div><label for="stare_haslo">Wprowadź Stare Hasło: </label>
                            <input type="password" name="stare_haslo" id="stare_haslo" required>
                        </div>
                        <div><label for="nowe_haslo">Wprowadź Nowe Hasło: </label>
                            <input type="password" name="nowe_haslo" id="nowe_haslo" required>
                        </div>
                        <div><label for="ponownie_nowe_haslo">Wprowadź Ponownie Nowe Hasło: </label>
                            <input type="password" name="ponownie_nowe_haslo" id="ponownie_nowe_haslo" required>
                        </div>
                        <input type="submit" value="Zapisz" class="button">
                    </form>
                </li>

                <!-- Jeśli dane zostały zaktualizowane lub wystąpił błąd, wyświetlenie komunikatu -->
                <?php if ($dane_sukces): ?>
                    <p class="sukces"><?= htmlspecialchars($dane_sukces) ?></p>
                <?php elseif ($blad_dane): ?>
                    <p class="blad"><?= htmlspecialchars($blad_dane) ?></p>
                <?php endif; ?>

                <!-- Formularz zmiany danych użytkownika -->
                <ul class="title">Zmień dane</ul>
                <li>
                    <form action="" method="POST" class="flexbox konto-zmiana-danych">
                        <div><label for="login">Login: </label>
                            <input type="text" name="login" value="<?= htmlspecialchars($current_login) ?>" id="login" required>
                        </div>
                        <div><label for="email">Email: </label>
                            <input type="email" name="email" value="<?= htmlspecialchars($current_email) ?>" id="email" required>
                        </div>
                        <input type="submit" value="Zapisz" class="button">
                    </form>
                </li>

                <!-- Przyciski: wylogowanie oraz eksport użytkowników do CSV (dostępne tylko dla organizatora) -->
                <div class="flexbox konto-przyciski">
                    <div class="flexbox">
                        <a href="?logout=true" class="button2">Wyloguj</a>
                    </div>
                    <?php if ($current_rola === 'organizator'): ?>
                        <div class="flexbox">
                            <a href="?export=true" class="button2">Eksportuj użytkowników do CSV</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="flexbox">
        <div class="margines flexbox footer-cont">
            <div class="small footer-item"><a href="#">Ustawienia plików cookies</a></div>
            <div class="small footer-item"><a href="#">Zarządzaj Danymi</a></div>
            <div class="small footer-item"><a href="#">Cookie</a></div>
            <div class="small footer-item"><a href="#">Polityka prywatności</a></div>
            <div class="small footer-item"><a href="#">Regulamin</a></div>
            <div class="small"><a href="#">Informacje Spółki</a></div>
        </div>
    </footer>
</body>

</html>