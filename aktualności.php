<?php
session_start();
require_once('config.php');
require_once('funkcje.php');

try {
  $polaczenie = otworz_polaczenie();

  // Sprawdzanie, czy użytkownik jest zalogowany i czy próbuję zapisać się na zawody
  if (isset($_GET['zapisz']) && isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']) {
    // Pobieranie ID zawodów i użytkownika
    $id_zawodow = $_GET['zapisz'];
    $id_uzytkownika = $_SESSION['id_uzytkownika'];

    // Sprawdzenie, czy użytkownik już jest zapisany na zawody
    $zapytanie_sprawdz = $polaczenie->prepare("SELECT * FROM wyniki WHERE id_zawodow = ? AND id_uzytkownika = ?");
    $zapytanie_sprawdz->bind_param("ii", $id_zawodow, $id_uzytkownika);
    $zapytanie_sprawdz->execute();
    $wynik_sprawdz = $zapytanie_sprawdz->get_result();

    if ($wynik_sprawdz->num_rows == 0) {
      // Jeśli nie jest zapisany, zapisujemy użytkownika na zawody
      $zapytanie_zapisz = $polaczenie->prepare("INSERT INTO wyniki (id_zawodow, id_uzytkownika, data_wprowadzenia) 
                                                  VALUES (?, ?, NOW())");
      $zapytanie_zapisz->bind_param("ii", $id_zawodow, $id_uzytkownika);
      $zapytanie_zapisz->execute();

      // Sprawdzamy, czy zapis był udany
      if ($zapytanie_zapisz->affected_rows > 0) {
        $_SESSION['zapis_sukces'] = "Zostałeś zapisany na zawody!";
      } else {
        $_SESSION['zapis_blad'] = "Wystąpił błąd podczas zapisywania na zawody.";
      }
    } else {
      // Jeśli użytkownik jest już zapisany, informujemy o błędzie
      $_SESSION['zapis_blad'] = "Jesteś już zapisany na te zawody.";
    }
    // Przekierowanie na stronę aktualności po zapisaniu
    header('Location: aktualności.php');
    exit();
  }

  // Pobieranie danych o nadchodzących zawodach, których użytkownik jeszcze nie uczestniczy
  $zapytanie_zawody = "
        SELECT z.id_zawodow, z.nazwa, z.lokalizacja, z.data 
        FROM zawody z
        LEFT JOIN wyniki w ON z.id_zawodow = w.id_zawodow AND w.id_uzytkownika = ?
        WHERE z.data > CURDATE()
        ";

  // Przygotowanie zapytania z bindowaniem parametrów
  $zapytanie_zawody_przygotuj = $polaczenie->prepare($zapytanie_zawody);
  $zapytanie_zawody_przygotuj->bind_param("i", $_SESSION['id_uzytkownika']);
  $zapytanie_zawody_przygotuj->execute();
  $wynik_zawody = $zapytanie_zawody_przygotuj->get_result();

  // Zapytanie do pobierania wyników zawodów
  $zapytanie_zawody2 = "
        SELECT w.id_wynikow, z.id_zawodow, z.nazwa, u.Imie, u.Nazwisko, u.email, w.konkurencja, w.wartosc_wyniku, w.data_wprowadzenia
        FROM wyniki w
        JOIN zawody z ON w.id_zawodow = z.id_zawodow
        JOIN użytkownicy u ON w.id_uzytkownika = u.id_uzytkownika
        WHERE z.data > CURDATE()
        ";
} catch (Exception $e) {
  // Logowanie błędu i wyświetlanie komunikatu
  logError("Wystąpił błąd: " . $e->getMessage());
  die("Błąd: " . $e->getMessage());
}

// Obsługa zapisywania wyników przez organizatora
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zapisz'])) {
  $id_wynikow = $_POST['id_wynikow'];
  $konkurencja = $_POST['konkurencja'];
  $wartosc_wyniku = $_POST['wartosc_wyniku'];

  // Walidacja danych wejściowych
  if (!empty($id_wynikow) && !empty($konkurencja) && !empty($wartosc_wyniku)) {
    // Aktualizacja wyniku zawodnika
    $zapytanie_aktualizacja = $polaczenie->prepare(
      "UPDATE wyniki SET konkurencja = ?, wartosc_wyniku = ?, data_wprowadzenia = NOW() 
       WHERE id_wynikow = ?"
    );
    $zapytanie_aktualizacja->bind_param("ssi", $konkurencja, $wartosc_wyniku, $id_wynikow);
    $zapytanie_aktualizacja->execute();

    // Sprawdzanie, czy wynik został zaktualizowany
    if ($zapytanie_aktualizacja->affected_rows > 0) {
      $_SESSION['zapis_sukces'] = "Wynik został zaktualizowany.";
    } else {
      $_SESSION['zapis_blad'] = "Nie udało się zaktualizować wyniku.";
    }
  } else {
    $_SESSION['zapis_blad'] = "Nieprawidłowe dane wejściowe.";
  }

  // Przekierowanie po zapisaniu
  header('Location: aktualności.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Aktualności</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body class="body">
  <header class="flexbox">
    <div class="margines-header flexbox body-bold">
      <!-- Logo i linki nawigacyjne -->
      <div><a href="index.php">Logo</a></div>
      <div class="flexbox header-links">
        <div><a href="aktualności.php">Aktualności</a></div>
        <div><a href="wyniki.php">Wyniki</a></div>
        <div><a href="zawody.php">Zawody</a></div>
        <!-- Sprawdzanie, czy użytkownik jest zalogowany i wyświetlanie odpowiednich opcji -->
        <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
          <div><a href="konto.php">Konto</a></div>
        <?php else: ?>
          <div><a href="logowanie.php">Logowanie</a></div>
        <?php endif; ?>
      </div>
    </div>
  </header>
  <main>
    <section class="flexbox">
      <div class="margines">
        <h1 class="title">Najbliższe zawody</h1>
        <div class="flexbox wyniki-cont">
          <!-- Wyświetlanie komunikatów o sukcesie lub błędzie zapisu -->
          <?php if (isset($_SESSION['zapis_sukces'])): ?>
            <p class="sukces"><?php echo htmlspecialchars($_SESSION['zapis_sukces']); ?></p>
            <?php unset($_SESSION['zapis_sukces']); ?>
          <?php elseif (isset($_SESSION['zapis_blad'])): ?>
            <p class="blad"><?php echo htmlspecialchars($_SESSION['zapis_blad']); ?></p>
            <?php unset($_SESSION['zapis_blad']); ?>
          <?php endif; ?>
          <!-- Tabela z nadchodzącymi zawodami -->
          <div class="wyniki-tabela">
            <table>
              <thead>
                <tr>
                  <th>Nazwa</th>
                  <th>Lokalizacja</th>
                  <th>Data</th>
                  <!-- Sprawdzanie, czy użytkownik ma rolę 'uczestnik' i wyświetlanie opcji zapisu -->
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'uczestnik'): ?>
                    <th>Rejestracja na zawody</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php if ($wynik_zawody && $wynik_zawody->num_rows > 0): ?>
                  <?php while ($wiersz = $wynik_zawody->fetch_assoc()): ?>
                    <?php
                    // Sprawdzanie, czy użytkownik jest zapisany na zawody
                    $zapytanie_sprawdz = $polaczenie->prepare("SELECT * FROM wyniki WHERE id_zawodow = ? AND id_uzytkownika = ?");
                    $zapytanie_sprawdz->bind_param("ii", $wiersz['id_zawodow'], $_SESSION['id_uzytkownika']);
                    $zapytanie_sprawdz->execute();
                    $wynik_sprawdz = $zapytanie_sprawdz->get_result();

                    if ($wynik_sprawdz->num_rows > 0) {
                      // Jeśli użytkownik jest zapisany, pokaż "Zrezygnuj z zawodów"
                      $przycisk = 'Zrezygnuj z zawodów';
                      $akcja = 'zrezygnuj'; // Parametr do obsługi skryptu rezygnacji
                      $klasaWiersz = 'zapisany'; // Dodanie klasy CSS
                      $klasaButton = 'button2'; // Zmiana stylizacji przycisku
                    } else {
                      // Jeśli użytkownik nie jest zapisany, pokaż "Zapisz się"
                      $przycisk = 'Zapisz się';
                      $akcja = 'zapisz'; // Parametr do obsługi skryptu zapisu
                      $klasaWiersz = ''; // Brak klasy CSS
                      $klasaButton = 'button'; // Standardowy button
                    }
                    ?>
                    <tr class="<?php echo $klasaWiersz; ?>">
                      <td><?php echo htmlspecialchars($wiersz['nazwa']); ?></td>
                      <td><?php echo htmlspecialchars($wiersz['lokalizacja']); ?></td>
                      <td><?= date('d-m-Y', strtotime($wiersz['data'])) ?></td>
                      <!-- Sprawdzanie, czy użytkownik ma rolę 'uczestnik' i umożliwienie zapisu -->
                      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'uczestnik'): ?>
                        <td>
                          <a href="aktualności.php?zawod=<?php echo $wiersz['id_zawodow']; ?>&akcja=<?php echo $akcja; ?>"
                            class="<?php echo $klasaButton; ?>">
                            <?php echo $przycisk; ?>
                          </a>
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
    <section class="flexbox">
      <div class="margines">
        <h2 class="title">Edycja najbliższych zawodów</h2>
        <!-- Tylko organizatorzy mają dostęp do edytowania zawodów -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'organizator'): ?>
          <div class="wyniki-tabela">
            <table>
              <thead>
                <tr>
                  <th>Nazwa</th>
                  <th>Imie</th>
                  <th>Nazwisko</th>
                  <th>Email uczestnika</th>
                  <th>Konkurencja</th>
                  <th>Wartość wyniku</th>
                  <th>Data Wprowadzenia</th>
                  <th>Akcje</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $zapytanie_zawody2_przygotuj = $polaczenie->prepare($zapytanie_zawody2);
                $zapytanie_zawody2_przygotuj->execute();
                $wynik_zawody2 = $zapytanie_zawody2_przygotuj->get_result();

                if ($wynik_zawody2 && $wynik_zawody2->num_rows > 0): ?>
                  <?php while ($wiersz2 = $wynik_zawody2->fetch_assoc()): ?>
                    <tr>
                      <form action="aktualności.php" method="POST">
                        <input type="hidden" name="id_wynikow" value="<?php echo isset($wiersz2['id_wynikow']) ? htmlspecialchars($wiersz2['id_wynikow']) : ''; ?>">
                        <td><?php echo htmlspecialchars($wiersz2['nazwa']); ?></td>
                        <td><?php echo htmlspecialchars($wiersz2['Imie']); ?></td>
                        <td><?php echo htmlspecialchars($wiersz2['Nazwisko']); ?></td>
                        <td><?php echo htmlspecialchars($wiersz2['email']); ?></td>
                        <td>
                          <input type="text" name="konkurencja" value="<?php echo htmlspecialchars($wiersz2['konkurencja']); ?>">
                        </td>
                        <td>
                          <input type="text" name="wartosc_wyniku" value="<?php echo htmlspecialchars($wiersz2['wartosc_wyniku']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($wiersz2['data_wprowadzenia']); ?></td>
                        <td>
                          <button type="submit" name="zapisz" class="button">Zapisz</button>
                        </td>
                      </form>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8">Brak wyników do wyświetlenia.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p>Brak dostępu do edycji wyników. Tylko organizatorzy mogą edytować zawody.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>
  <footer class="flexbox">
    <div class="margines flexbox footer-cont">
      <!-- Linki w stopce -->
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

<?php
// Skrypt do obsługi zapisu i rezygnacji z zawodów przez uczestników
if (isset($_GET['akcja'])) {
  $akcja = $_GET['akcja'];
  $id_zawodow = $_GET['zawod'];
  $id_uzytkownika = $_SESSION['id_uzytkownika'];

  if ($akcja == 'zapisz') {
    // Zapisywanie użytkownika na zawody
    $zapytanie_zapisz = $polaczenie->prepare("INSERT INTO wyniki (id_zawodow, id_uzytkownika, data_wprowadzenia) 
                                              VALUES (?, ?, NOW())");
    $zapytanie_zapisz->bind_param("ii", $id_zawodow, $id_uzytkownika);
    $zapytanie_zapisz->execute();
    $_SESSION['zapis_sukces'] = "Zostałeś zapisany na zawody!";
  } elseif ($akcja == 'zrezygnuj') {
    // Rezygnowanie z zawodów
    $zapytanie_rezygnacja = $polaczenie->prepare("DELETE FROM wyniki WHERE id_zawodow = ? AND id_uzytkownika = ?");
    $zapytanie_rezygnacja->bind_param("ii", $id_zawodow, $id_uzytkownika);
    $zapytanie_rezygnacja->execute();
    $_SESSION['zapis_sukces'] = "Zrezygnowałeś z zawodów.";
  }
  header('Location: aktualności.php'); // Przekierowanie po operacji
  exit();
}
?>