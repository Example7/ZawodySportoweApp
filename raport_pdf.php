<?php
require 'vendor/autoload.php'; // Ładowanie autoloadera Composer

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
require_once 'config.php';
require_once 'funkcje.php';

try {
    $polaczenie = otworz_polaczenie();

    // Pobieranie danych tak jak w wyniki.php
    $zapytanie_wyniki = "
      SELECT z.nazwa, u.Imie, u.Nazwisko, u.email, w.konkurencja, z.lokalizacja, z.data, w.wartosc_wyniku
      FROM wyniki w
      JOIN zawody z ON w.id_zawodow = z.id_zawodow
      JOIN użytkownicy u ON w.id_uzytkownika = u.id_uzytkownika
      WHERE z.data < CURDATE()
      ORDER BY z.data ASC
    ";

    $wynik_zawody = $polaczenie->query($zapytanie_wyniki);

    $zawody = [];
    if ($wynik_zawody) {
        while ($wiersz = $wynik_zawody->fetch_assoc()) {
            $zawody[] = $wiersz;
        }
    }

    zamknij_polaczenie($polaczenie);

    // Generowanie treści HTML do PDF
    $html = '<html><head>
        <style>
            html, body {
                background-color: #0b1318;
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                color: #e3e9ed;
            }
            body { 
                font-family: "DejaVu Sans", sans-serif; 
                font-size: 10px; 
            }
                h1{
                margin-left: 15px;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0;
                table-layout: auto;
                color: #e3e9ed;
            }
            th, td { 
                border: 1px solid #8fbedc;
                padding: 3px 3px;
                text-align: left; 
                word-wrap: break-word;
                overflow: hidden;
            }
            th { 
                background-color: #0b1318; 
            }
            td {
                word-wrap: break-word;
                background-color: #1c5c87;
            }
        </style>
    </head><body>';
    $html .= '<h1>Raport wyników</h1>';
    $html .= '<table>';
    $html .= '<thead>
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
              </thead><tbody>';

    if (!empty($zawody)) {
        foreach ($zawody as $zawod) {
            $html .= '<tr>
                <td>' . htmlspecialchars($zawod['nazwa']) . '</td>
                <td>' . htmlspecialchars($zawod['konkurencja']) . '</td>
                <td>' . htmlspecialchars($zawod['Imie']) . '</td>
                <td>' . htmlspecialchars($zawod['Nazwisko']) . '</td>
                <td>' . htmlspecialchars($zawod['email']) . '</td>
                <td>' . htmlspecialchars($zawod['lokalizacja']) . '</td>
                <td>' . date('d-m-Y', strtotime($zawod['data'])) . '</td>
                <td>' . htmlspecialchars($zawod['wartosc_wyniku']) . '</td>
              </tr>';
        }
    } else {
        $html .= '<tr><td colspan="8">Brak wyników do wyświetlenia.</td></tr>';
    }

    $html .= '</tbody></table></body></html>';

    // Konfiguracja Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');

    // Renderowanie dokumentu
    $dompdf->render();

    // Wyświetlenie pliku PDF w przeglądarce
    $dompdf->stream("raport_wynikow.pdf", ["Attachment" => false]); // false = otwiera w przeglądarce
} catch (Exception $e) {
    logError("Wystąpił błąd: " . $e->getMessage());
    die("Błąd: " . $e->getMessage());
}
