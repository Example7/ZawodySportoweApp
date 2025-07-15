<?php
require_once 'config.php';
require_once 'funkcje.php';

// Sprawdzanie, czy formularz został wysłany metodą POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Pobieranie danych z formularza rejestracji
  $imie = $_POST['imie'];
  $nazwisko = $_POST['nazwisko'];
  $email = $_POST['email'];
  $login = $_POST['login'];
  $haslo = $_POST['haslo'];
  $rola = $_POST['rola'];

  // Szyfrowanie hasła przed zapisaniem do bazy
  $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

  // Otwarcie połączenia z bazą danych
  $polaczenie = otworz_polaczenie();

  // Sprawdzanie, czy email już istnieje w bazie
  $zapytanie_email = "SELECT * FROM użytkownicy WHERE email = '$email'";
  $wynik_email = mysqli_query($polaczenie, $zapytanie_email);

  // Sprawdzanie, czy login już istnieje w bazie
  $zapytanie_login = "SELECT * FROM użytkownicy WHERE login = '$login'";
  $wynik_login = mysqli_query($polaczenie, $zapytanie_login);

  // Jeżeli email lub login już istnieją, wyświetlenie odpowiedniego błędu
  if (mysqli_num_rows($wynik_email) > 0) {
    $blad = "Adres e-mail już jest zajęty!";
  } elseif (mysqli_num_rows($wynik_login) > 0) {
    $blad = "Login jest już zajęty!";
  } else {
    // Jeśli email i login są dostępne, wstawienie danych użytkownika do bazy
    $zapytanie = "INSERT INTO użytkownicy (Imie, Nazwisko, email, login, haslo, role) 
                  VALUES ('$imie', '$nazwisko', '$email', '$login', '$haslo_hash', '$rola')";
    if (mysqli_query($polaczenie, $zapytanie)) {
      // Rejestracja zakończona sukcesem
      $sukces = "Rejestracja zakończona sukcesem!";
    } else {
      // Błąd rejestracji, jeśli nie udało się zapisać danych w bazie
      $blad = "Błąd rejestracji: " . mysqli_error($polaczenie);
      logError("Błąd podczas zapisu danych do bazy.");
    }
  }

  zamknij_polaczenie($polaczenie);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rejestracja</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="body">
  <div class="rejestracja">
    <!-- Nagłówek sekcji rejestracji -->
    <h2 class="title">Rejestracja użytkownika</h2>

    <!-- Wyświetlanie komunikatów o sukcesie lub błędzie -->
    <?php if (isset($sukces)): ?>
      <p class="sukces"><?= $sukces ?></p>
    <?php elseif (isset($blad)): ?>
      <p class="blad"><?= $blad ?></p>
    <?php endif; ?>

    <!-- Formularz rejestracyjny -->
    <form action="rejestracja.php" method="POST" class="flexbox">
      <!-- Pole do wprowadzenia imienia -->
      <div>
        <label for="imie">Imie: </label>
        <input type="text" id="imie" name="imie" required />
      </div>
      <!-- Pole do wprowadzenia nazwiska -->
      <div>
        <label for="nazwisko">Nazwisko: </label>
        <input type="text" id="nazwisko" name="nazwisko" required />
      </div>
      <!-- Pole do wprowadzenia emaila -->
      <div>
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" required />
      </div>
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

      <!-- Wybór roli użytkownika -->
      <div class="select flexbox">
        <label for="rola">Rola: </label>
        <select id="rola" name="rola" required>
          <option value="" disabled selected>- Wybierz rolę -</option>
          <option value="organizator">Organizator</option>
          <option value="uczestnik">Uczestnik</option>
          <option value="widz">Widz</option>
        </select>
      </div>

      <!-- Przycisk do rejestracji i powrotu do strony logowania -->
      <div class="przyciski">
        <input type="submit" value="Zarejestruj się" class="button" />
        <input type="button" value="Cofnij" class="button" onclick="window.location.href='logowanie.php'" />
        <!-- Link do strony logowania w przypadku, gdy użytkownik już posiada konto -->
        <p class="small">Posiadasz już konto? <a href="logowanie.php">Zaloguj się</a></p>
      </div>
    </form>
  </div>
</body>

</html>