<?php
require_once 'config.php';
require_once 'funkcje.php';

session_start();

// Inicjalizacja zmiennej dla błędów logowania
$blad = null;

// Sprawdzanie, czy formularz został wysłany metodą POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Pobieranie danych z formularza logowania
  $login = isset($_POST['login']) ? trim($_POST['login']) : '';  // Login użytkownika
  $haslo = isset($_POST['haslo']) ? trim($_POST['haslo']) : '';    // Hasło użytkownika

  try {
    $polaczenie = otworz_polaczenie();

    // Przygotowanie zapytania do bazy, aby znaleźć użytkownika po loginie
    $zapytanie = "SELECT * FROM użytkownicy WHERE login = ?";
    $stmt = mysqli_prepare($polaczenie, $zapytanie);
    mysqli_stmt_bind_param($stmt, "s", $login);  // Wiązanie parametru zapytania
    mysqli_stmt_execute($stmt);  // Wykonanie zapytania
    $wynik = mysqli_stmt_get_result($stmt);  // Pobranie wyników zapytania

    // Sprawdzanie, czy użytkownik został znaleziony
    if ($wiersz = mysqli_fetch_assoc($wynik)) {
      // Weryfikacja hasła użytkownika
      if (password_verify($haslo, $wiersz['haslo'])) {
        $_SESSION['zalogowany'] = true;
        $_SESSION['uzytkownik'] = $wiersz['login'];
        $_SESSION['role'] = $wiersz['role'];
        $_SESSION['id_uzytkownika'] = $wiersz['id_uzytkownika'];

        // Przekierowanie na stronę główną po pomyślnym zalogowaniu
        header('Location: index.php');
        exit;
      } else {
        // Komunikat o błędnym haśle
        $blad = "Nieprawidłowe hasło.";
      }
    } else {
      // Komunikat o nieistniejącym użytkowniku
      $blad = "Nie znaleziono użytkownika.";
    }

    zamknij_polaczenie($polaczenie);
  } catch (Exception $e) {
    logError("Wystąpił błąd: " . $e->getMessage());
    $blad = "Wystąpił błąd: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logowanie</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="body">
  <div class="logowanie">
    <h2 class="title">Logowanie użytkownika</h2>
    <form action="" method="POST" class="flexbox">
      <!-- Pole do wprowadzenia loginu -->
      <div>
        <label for="login">Login: </label>
        <input type="text" id="login" name="login" required />
      </div>
      <!-- Pole do wprowadzenia hasła -->
      <div>
        <label for="haslo">Hasło: </label>
        <input type="password" id="haslo" name="haslo" required />
      </div>
      <!-- Wyświetlanie błędów logowania (jeśli wystąpiły) -->
      <?php if (!empty($blad)): ?>
        <p style="color: red;"><?= htmlspecialchars($blad) ?></p>
      <?php endif; ?>

      <div class="przyciski">
        <!-- Przycisk do zalogowania się -->
        <input type="submit" value="Zaloguj się" class="button">
        <!-- Przycisk do cofnięcia się do strony głównej -->
        <input type="button" value="Cofnij" class="button" onclick="window.location.href='index.php'">
        <!-- Link do strony rejestracji, jeśli użytkownik nie ma jeszcze konta -->
        <p class="small">Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a></p>
      </div>
    </form>
  </div>
</body>

</html>