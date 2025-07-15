<?php
session_start();
require_once 'config.php';
require_once 'funkcje.php';

try {
  $polaczenie = otworz_polaczenie();

  // Inicjalizujemy tablicę na zawody
  $zawody = [];

  // Zapytanie do bazy danych w celu pobrania wszystkich zawodów
  $zapytanie_zawody = "SELECT id_zawodow, nazwa FROM zawody";
  $wynik_zawody = $polaczenie->query($zapytanie_zawody);

  // Pobieramy wszystkie zawody i zapisujemy je do tablicy
  while ($wiersz = $wynik_zawody->fetch_assoc()) {
    $zawody[] = $wiersz;
  }

  // Inicjalizujemy tablicę na konkurencje
  $konkurencje = [];

  // Sprawdzamy, czy formularz został wysłany metodą POST i czy zawody zostały wybrane
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['zawody'])) {
    // Tworzymy listę wybranych zawodów w formacie liczbowym
    $wybrane_zawody = implode(',', array_map('intval', $_POST['zawody']));

    // Zapytanie do bazy danych w celu pobrania wyników dla wybranych zawodów
    $zapytanie_konkurencje = "
            SELECT w.konkurencja, z.nazwa, z.lokalizacja, z.data, u.Imie, u.Nazwisko, w.wartosc_wyniku
            FROM wyniki w
            JOIN zawody z ON w.id_zawodow = z.id_zawodow
            JOIN użytkownicy u ON w.id_uzytkownika = u.id_uzytkownika
            WHERE w.id_zawodow IN ($wybrane_zawody)
        ";

    // Wykonanie zapytania do bazy danych
    $wynik_konkurencje = $polaczenie->query($zapytanie_konkurencje);

    // Pobieramy konkurencje i zapisujemy je do tablicy
    while ($wiersz = $wynik_konkurencje->fetch_assoc()) {
      $konkurencje[] = $wiersz;
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
  <title>Zawody sportowe</title>
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

        <!-- Jeśli użytkownik jest zalogowany, wyświetlamy link do konta, w przeciwnym razie do logowania -->
        <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
          <div><a href="konto.php">Konto</a></div>
        <?php else: ?>
          <div><a href="logowanie.php">Logowanie</a></div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <section class="section1 flexbox">
      <div class="margines">
        <h2 class="title">Podgląd zawodów z wynikami uczestników</h2>

        <div class="flexbox sb">
          <!-- Formularz do wybierania zawodów -->
          <form method="post" class="flexbox kategorie">
            <?php
            // Ustalamy, które zawody były zaznaczone w formularzu
            $zaznaczoneZawody = isset($_POST['zawody']) ? $_POST['zawody'] : [];
            ?>

            <!-- Wyświetlamy checkboxy dla wszystkich zawodów -->
            <?php foreach ($zawody as $zawod): ?>
              <div>
                <label for="zawod<?= $zawod['id_zawodow'] ?>">
                  <?= htmlspecialchars($zawod['nazwa']) ?>
                </label>
                <input
                  type="checkbox"
                  name="zawody[]"
                  value="<?= $zawod['id_zawodow'] ?>"
                  id="zawod<?= $zawod['id_zawodow'] ?>"
                  <?= in_array($zawod['id_zawodow'], $zaznaczoneZawody) ? 'checked' : '' ?>>
              </div>
            <?php endforeach; ?>

            <div>
              <input type="submit" value="Wybierz" class="button">
            </div>
          </form>

          <!-- Wyświetlamy konkurencje w zależności od wyboru zawodów -->
          <div class="flexbox konkurencje-lista">
            <?php if (!empty($konkurencje)): ?>
              <?php foreach ($konkurencje as $konkurencja): ?>
                <div class="flexbox konkurencja-karta">
                  <div class="konkurencja-logo">
                    <img src="<?= htmlspecialchars(getLogoPath($konkurencja['nazwa'])) ?>" alt="Logo">
                  </div>
                  <div class="konkurencja-info">
                    <h3 class="konkurencja-tytul"><?= htmlspecialchars($konkurencja['konkurencja']) ?></h3>
                    <p class="konkurencja-uczestnik">Uczestnik: <?= htmlspecialchars($konkurencja['Imie'] . " " . $konkurencja['Nazwisko']) ?></p>
                    <p class="konkurencja-wartosc-wyniku">Wartość wyniku: <?= htmlspecialchars($konkurencja['wartosc_wyniku']) ?></p>
                    <p class="konkurencja-lokalizacja">Lokalizacja: <?= htmlspecialchars($konkurencja['lokalizacja']) ?></p>
                    <p class="konkurencja-data">Data: <?= htmlspecialchars($konkurencja['data']) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Jeśli brak konkurencji, wyświetlamy komunikat -->
              <p>Wybierz zawody, aby zobaczyć konkurencje.</p>
            <?php endif; ?>
          </div>
        </div>
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