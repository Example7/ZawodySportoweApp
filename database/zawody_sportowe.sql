-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 26, 2025 at 06:04 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

CREATE DATABASE IF NOT EXISTS zawody_sportowe CHARACTER SET utf8 COLLATE utf8_polish_ci;
USE zawody_sportowe;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zawody_sportowe`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `użytkownicy`
--

CREATE TABLE `użytkownicy` (
  `id_uzytkownika` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `Imie` varchar(100) DEFAULT NULL,
  `Nazwisko` varchar(100) DEFAULT NULL,
  `haslo` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('organizator','uczestnik','widz') NOT NULL,
  `data_utworzenia` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `użytkownicy`
--

INSERT INTO `użytkownicy` (`id_uzytkownika`, `login`, `Imie`, `Nazwisko`, `haslo`, `email`, `role`, `data_utworzenia`) VALUES
(8, 'uzytkownik48', 'Adam', 'Nowak', '$2y$10$aOebuidn/Z1Lwlq6MQpd4OqxSscfSKCRZdq9XhzloSTN26OIxSi1O', 'uzytkownik123@example.com', 'uczestnik', '2025-01-25 01:31:10'),
(10, 'kacper', 'Kacper', 'Kałużny', '$2y$10$owu2M/hTfOyyl2Xk6LP6T.J37/gX0/rSZnetRS4Z1qnPXGg/PqA1O', 'email1234@example.com', 'organizator', '2025-01-25 01:40:08'),
(11, 'kacper123', 'Kacper', 'Kałużny', '$2y$10$YfyzgmzUGzhohdoly75AqehNwWq5rkqgLGAvtaqKqJM6Hi99IXwHW', 'email321@example.com', 'uczestnik', '2025-01-25 01:43:35'),
(12, 'kacper2', 'Alfred', 'Łoboda', '$2y$10$Dlc6Z4cpwiTYByH3xD938O88BCEfTgma2HfHVuIOAuIsMdVqGX2NK', 'email5@example.com', 'uczestnik', '2025-01-25 01:47:15'),
(16, 'pawel', 'Paweł', 'Kot', '$2y$10$NLMnJLHvjkNGJYFLpXamPu2JDPhLb1UYqOafasIV4Ay2.scwKcmuG', 'email9@example.com', 'widz', '2025-01-26 00:27:39');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `wyniki`
--

CREATE TABLE `wyniki` (
  `id_wynikow` int(11) NOT NULL,
  `id_zawodow` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `konkurencja` varchar(50) DEFAULT NULL,
  `wartosc_wyniku` varchar(50) DEFAULT NULL,
  `data_wprowadzenia` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `wyniki`
--

INSERT INTO `wyniki` (`id_wynikow`, `id_zawodow`, `id_uzytkownika`, `konkurencja`, `wartosc_wyniku`, `data_wprowadzenia`) VALUES
(6, 1, 8, 'Bieg na 100m', '10.5 s', '2025-06-15 12:30:00'),
(8, 2, 12, 'Skok w dal', '6.2 m', '2025-07-20 09:00:00'),
(9, 2, 11, 'Skok w dal', '6.8 m', '2025-07-20 09:05:00'),
(10, 3, 8, 'Maraton Miejski', '2:15:45', '2025-09-10 15:00:00'),
(48, 13, 12, 'Skok w dal', '4.6m', '2025-01-26 01:44:15'),
(49, 14, 12, 'Bieg na 100m', '9.7s', '2025-01-26 01:44:31'),
(50, 13, 11, 'Bieg na 100m', '9.9s', '2025-01-26 01:44:57'),
(51, 14, 11, 'Skok w dal', '5.4m', '2025-01-26 01:45:05');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zawody`
--

CREATE TABLE `zawody` (
  `id_zawodow` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `lokalizacja` varchar(100) DEFAULT NULL,
  `data` date NOT NULL,
  `id_uzytkownika` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `zawody`
--

INSERT INTO `zawody` (`id_zawodow`, `nazwa`, `lokalizacja`, `data`, `id_uzytkownika`) VALUES
(1, 'Biegi', 'Stadion Narodowy, Warszawa', '2024-06-15', 10),
(2, 'Igrzyska Olimpijskie', 'Hala Sportowa, Kraków', '2024-07-20', 10),
(3, 'Maraton', 'Centrum, Gdańsk', '2024-09-10', 10),
(13, 'Igrzyska Olimpijskie 2020', 'Tokio', '2020-07-28', 10),
(14, 'Igrzyska Olipijskie 2016', 'Rio de Janeiro', '2016-08-12', 10),
(16, 'Igrzyska Olimpijskie 2028', 'Stany Zjednoczone', '2028-07-14', 10);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `użytkownicy`
--
ALTER TABLE `użytkownicy`
  ADD PRIMARY KEY (`id_uzytkownika`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Indeksy dla tabeli `wyniki`
--
ALTER TABLE `wyniki`
  ADD PRIMARY KEY (`id_wynikow`),
  ADD KEY `id_zawodow` (`id_zawodow`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `zawody`
--
ALTER TABLE `zawody`
  ADD PRIMARY KEY (`id_zawodow`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `użytkownicy`
--
ALTER TABLE `użytkownicy`
  MODIFY `id_uzytkownika` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wyniki`
--
ALTER TABLE `wyniki`
  MODIFY `id_wynikow` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `zawody`
--
ALTER TABLE `zawody`
  MODIFY `id_zawodow` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `wyniki`
--
ALTER TABLE `wyniki`
  ADD CONSTRAINT `wyniki_ibfk_1` FOREIGN KEY (`id_zawodow`) REFERENCES `zawody` (`id_zawodow`),
  ADD CONSTRAINT `wyniki_ibfk_2` FOREIGN KEY (`id_uzytkownika`) REFERENCES `użytkownicy` (`id_uzytkownika`);

--
-- Constraints for table `zawody`
--
ALTER TABLE `zawody`
  ADD CONSTRAINT `zawody_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `użytkownicy` (`id_uzytkownika`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
