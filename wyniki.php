<?php
session_start();
require_once 'config.php';
require_once 'funkcje.php';

try {
  $polaczenie = otworz_polaczenie();

  // Ustalenie kolumny i kierunku sortowania
  $sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'data';
  $sort_direction = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'ASC';

  // Zdefiniowane dozwolone kolumny i kierunki sortowania
  $valid_columns = ['data', 'nazwa', 'lokalizacja', 'Imie', 'Nazwisko'];
  $valid_directions = ['ASC', 'DESC'];

  // Weryfikacja, czy parametry sortowania są prawidłowe
  if (!in_array($sort_column, $valid_columns)) {
    $sort_column = 'data'; // Jeśli nieprawidłowa kolumna, ustaw domyślną
  }
  if (!in_array($sort_direction, $valid_directions)) {
    $sort_direction = 'ASC'; // Jeśli nieprawidłowy kierunek, ustaw domyślny
  }

  // Obsługa wyszukiwania
  $search_term = isset($_GET['wyszukaj']) ? $_GET['wyszukaj'] : '';

  if ($search_term) {
    // Jeśli wprowadzono wyszukiwanie, przygotuj zapytanie
    $search_term = "%$search_term%";
    $zapytanie_wyniki = "
      SELECT z.nazwa, u.Imie, u.Nazwisko, u.email, w.konkurencja, z.lokalizacja, z.data, w.wartosc_wyniku
      FROM wyniki w
      JOIN zawody z ON w.id_zawodow = z.id_zawodow
      JOIN użytkownicy u ON w.id_uzytkownika = u.id_uzytkownika
      WHERE z.data < CURDATE() AND 
      (z.nazwa LIKE ? OR u.Imie LIKE ? OR u.Nazwisko LIKE ? OR w.konkurencja LIKE ? OR z.data LIKE ? OR u.email LIKE ? OR w.wartosc_wyniku LIKE ? OR z.lokalizacja LIKE ?)
      ORDER BY $sort_column $sort_direction
    ";
    // Przygotowanie zapytania i jego wykonanie
    $stmt = $polaczenie->prepare($zapytanie_wyniki);
    $stmt->bind_param('ssssssss', $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();

    // Pobranie wyników
    $wynik_zawody = $stmt->get_result();
  } else {
    // Jeśli brak wyszukiwania, wykonaj zapytanie bez filtrów
    $zapytanie_wyniki = "
      SELECT z.nazwa, u.Imie, u.Nazwisko, u.email, w.konkurencja, z.lokalizacja, z.data, w.wartosc_wyniku
      FROM wyniki w
      JOIN zawody z ON w.id_zawodow = z.id_zawodow
      JOIN użytkownicy u ON w.id_uzytkownika = u.id_uzytkownika
      WHERE z.data < CURDATE()
      ORDER BY $sort_column $sort_direction
    ";
    $wynik_zawody = $polaczenie->query($zapytanie_wyniki);
  }

  $zawody = [];

  // Przechowywanie wyników w tablicy
  if ($wynik_zawody) {
    while ($wiersz = $wynik_zawody->fetch_assoc()) {
      $zawody[] = $wiersz; // Dodawanie wyników do tablicy
    }
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
  <title>Wyniki</title>
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
        <h2 class="title">Wyniki</h2>
        <div class="flexbox wyniki-cont">
          <div class="wyniki-panel flexbox body-bold">
            <div class="flexbox wyniki-sort">
              <form action="" method="GET" class="flexbox wyniki-szukaj">
                <label for="wyszukaj">Szukaj: </label>
                <input type="text" id="wyszukaj" name="wyszukaj" value="<?= isset($_GET['wyszukaj']) ? htmlspecialchars($_GET['wyszukaj']) : '' ?>" />
                <input type="hidden" name="sort_column" id="hidden_sort_column" value="<?= isset($_GET['sort_column']) ? htmlspecialchars($_GET['sort_column']) : 'data' ?>" />
                <input type="hidden" name="sort_direction" id="hidden_sort_direction" value="<?= isset($_GET['sort_direction']) ? htmlspecialchars($_GET['sort_direction']) : 'ASC' ?>" />

                <div>Sortuj po:</div>
                <div class="sort-rodzaj">
                  <select name="sort_column" id="sort_column">
                    <!-- Opcje do wyboru kolumny sortowania -->
                    <option value="data" <?= isset($_GET['sort_column']) && $_GET['sort_column'] == 'data' ? 'selected' : '' ?>>Data</option>
                    <option value="nazwa" <?= isset($_GET['sort_column']) && $_GET['sort_column'] == 'nazwa' ? 'selected' : '' ?>>Nazwa zawodów</option>
                    <option value="lokalizacja" <?= isset($_GET['sort_column']) && $_GET['sort_column'] == 'lokalizacja' ? 'selected' : '' ?>>Lokalizacja</option>
                    <option value="Imie" <?= isset($_GET['sort_column']) && $_GET['sort_column'] == 'Imie' ? 'selected' : '' ?>>Imie</option>
                    <option value="Nazwisko" <?= isset($_GET['sort_column']) && $_GET['sort_column'] == 'Nazwisko' ? 'selected' : '' ?>>Nazwisko</option>
                  </select>
                </div>
                <div>
                  <select name="sort_direction" id="sort_direction">
                    <!-- Opcje do wyboru kierunku sortowania -->
                    <option value="ASC" <?= isset($_GET['sort_direction']) && $_GET['sort_direction'] == 'ASC' ? 'selected' : '' ?>>Rosnąco</option>
                    <option value="DESC" <?= isset($_GET['sort_direction']) && $_GET['sort_direction'] == 'DESC' ? 'selected' : '' ?>>Malejąco</option>
                  </select>
                </div>
                <input type="submit" value="Wyszukaj" class="button2 body-bold" />
              </form>
              <form action="raport_pdf.php" method="post" target="_blank">
                <button type="submit" class="button2 body-bold">Generuj PDF</button>
              </form>
            </div>
          </div>

          <div class="wyniki-tabela">
            <table>
              <thead>
                <tr>
                  <th>Nazwa Zawodów</th>
                  <th>Konkurencja</th>
                  <th>Imię</th>
                  <th>Nazwisko</th>
                  <th>Email</th>
                  <th>Lokalizacja</th>
                  <th>Data zawodów</th>
                  <th>Wynik</th>
                </tr>
              </thead>
              <tbody>
                <!-- Wyświetlanie wyników -->
                <?php if (!empty($zawody)): ?>
                  <?php foreach ($zawody as $zawod): ?>
                    <tr>
                      <td><?= htmlspecialchars($zawod['nazwa']) ?></td>
                      <td><?= htmlspecialchars($zawod['konkurencja']) ?></td>
                      <td><?= htmlspecialchars($zawod['Imie']) ?></td>
                      <td><?= htmlspecialchars($zawod['Nazwisko']) ?></td>
                      <td><?= htmlspecialchars($zawod['email']) ?></td>
                      <td><?= htmlspecialchars($zawod['lokalizacja']) ?></td>
                      <td><?= date('d-m-Y', strtotime($zawod['data'])) ?></td>
                      <td><?= htmlspecialchars($zawod['wartosc_wyniku']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8">Brak wyników do wyświetlenia.</td>
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

  <script>
    // Obsługa zmiany sortowania
    document.querySelectorAll("#sort_column, #sort_direction").forEach(element => {
      element.addEventListener("change", function() {
        document.getElementById("hidden_sort_column").value = document.getElementById("sort_column").value;
        document.getElementById("hidden_sort_direction").value = document.getElementById("sort_direction").value;
      });
    });
  </script>
</body>

</html>