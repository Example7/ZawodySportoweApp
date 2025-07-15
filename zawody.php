<?php
session_start();
require_once 'config.php';
require_once 'funkcje.php';

try {
  $polaczenie = otworz_polaczenie();

  // Zapytanie do bazy danych pobierające zawody
  $zapytanie_wyniki = "SELECT id_zawodow, nazwa, lokalizacja, data FROM zawody";
  $wynik_zawody = $polaczenie->query($zapytanie_wyniki); // Wykonanie zapytania

  // Usuwanie zawodów po przekazaniu id w parametrze URL i sprawdzeniu roli organizatora
  if (isset($_GET['usun']) && isset($_SESSION['role']) && $_SESSION['role'] === 'organizator') {
    $id_zawodow = $_GET['usun'];
    $zapytanie_usun = $polaczenie->prepare("DELETE FROM zawody WHERE id_zawodow = ?"); // Przygotowanie zapytania usuwającego
    $zapytanie_usun->bind_param("i", $id_zawodow); // Powiązanie parametru
    $zapytanie_usun->execute(); // Wykonanie zapytania usuwającego

    // Sprawdzenie, czy usunięcie się powiodło
    if ($zapytanie_usun->affected_rows > 0) {
      $_SESSION['dodano_sukces'] = "Zawody zostały usunięte pomyślnie!";
    } else {
      $_SESSION['dodano_blad'] = "Nie udało się usunąć zawodów.";
    }

    header('Location: zawody.php'); // Przekierowanie po usunięciu
    exit();
  }

  // Dodawanie nowych zawodów po przesłaniu formularza
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'organizator') {
    // Pobieranie danych z formularza
    $nazwa = $polaczenie->real_escape_string($_POST['nazwa']);
    $lokalizacja = $polaczenie->real_escape_string($_POST['lokalizacja']);
    $data = $polaczenie->real_escape_string($_POST['data']);
    $id_uzytkownika = $_SESSION['id_uzytkownika'];

    // Zapytanie do dodania nowych zawodów
    $zapytanie_dodaj = "INSERT INTO zawody (nazwa, lokalizacja, data, id_uzytkownika) VALUES ('$nazwa', '$lokalizacja', '$data', $id_uzytkownika)";
    if ($polaczenie->query($zapytanie_dodaj)) {
      $_SESSION['dodano_sukces'] = "$nazwa zostały dodane pomyślnie!";
    } else {
      $_SESSION['dodano_blad'] = "Wystąpił błąd podczas dodawania zawodów: " . $polaczenie->error;
    }

    header('Location: zawody.php'); // Przekierowanie po dodaniu zawodów
    exit();
  }

  zamknij_polaczenie($polaczenie);
} catch (Exception $e) {
  logError("Wystąpił błąd: " . $e->getMessage());
  die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Zawody</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body class="body">
  <header class="flexbox">
    <div class="margines-header flexbox body-bold">
      <div><a href="index.php">Logo</a></div>
      <div class="flexbox header-links">
        <div><a href="aktualności.php">Aktualności</a></div>
        <div><a href="wyniki.php">Wyniki</a></div>
        <div><a href="zawody.php">Zawody</a></div>
        <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
          <div><a href="konto.php">Konto</a></div>
        <?php else: ?>
          <div><a href="logowanie.php">Logowanie</a></div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <section class="flexbox wyniki">
      <div class="margines">
        <h2 class="title">Zawody</h2>

        <!-- Wyświetlanie komunikatów o sukcesie lub błędzie -->
        <?php if (isset($_SESSION['dodano_sukces'])): ?>
          <p class="sukces"><?php echo htmlspecialchars($_SESSION['dodano_sukces']); ?></p>
          <?php unset($_SESSION['dodano_sukces']); ?>
        <?php elseif (isset($_SESSION['dodano_blad'])): ?>
          <p class="blad"><?php echo htmlspecialchars($_SESSION['dodano_blad']); ?></p>
          <?php unset($_SESSION['dodano_blad']); ?>
        <?php endif; ?>

        <!-- Formularz do dodawania nowych zawodów widoczny tylko dla organizatora -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'organizator'): ?>
          <div class="flexbox zawody">
            <h2 class="title">Dodaj zawody</h2>
            <form method="POST" class="flexbox konto-zmiana-hasla">
              <div>
                <label for="nazwa">Nazwa:</label>
                <input type="text" id="nazwa" name="nazwa" required />
              </div>
              <div>
                <label for="lokalizacja">Lokalizacja:</label>
                <input type="text" id="lokalizacja" name="lokalizacja" required />
              </div>
              <div>
                <label for="data">Data:</label>
                <input type="date" id="data" name="data" required />
              </div>
              <div>
                <input type="submit" value="Dodaj zawody" class="button2" />
              </div>
            </form>
          </div>
        <?php endif; ?>

        <!-- Wyświetlanie tabeli z zawodami -->
        <div class="flexbox wyniki-cont">
          <div class="wyniki-tabela">
            <table>
              <thead>
                <tr>
                  <th>Nazwa</th>
                  <th>Lokalizacja</th>
                  <th>Data</th>
                  <!-- Kolumna Usuń widoczna tylko dla organizatora -->
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'organizator'): ?>
                    <th>Usuń</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <!-- Sprawdzanie, czy są dane do wyświetlenia -->
                <?php if ($wynik_zawody && $wynik_zawody->num_rows > 0): ?>
                  <?php while ($wiersz = $wynik_zawody->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($wiersz['nazwa']); ?></td>
                      <td><?php echo htmlspecialchars($wiersz['lokalizacja']); ?></td>
                      <td><?= date('d-m-Y', strtotime($wiersz['data'])) ?></td>
                      <!-- Przycisk do usuwania zawodów -->
                      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'organizator'): ?>
                        <td>
                          <a href="zawody.php?usun=<?php echo $wiersz['id_zawodow']; ?>" class="button">Usuń</a>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4">Brak danych do wyświetlenia.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
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