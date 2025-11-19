-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Ноя 19 2025 г., 13:43
-- Версия сервера: 8.0.43-0ubuntu0.24.04.1
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `covoiturage`
--

-- --------------------------------------------------------

--
-- Структура таблицы `participations`
--

CREATE TABLE `participations` (
  `id` int NOT NULL,
  `ride_id` int NOT NULL,
  `passager_id` int NOT NULL,
  `statut` enum('confirme','annule') DEFAULT 'confirme',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `participations`
--

INSERT INTO `participations` (`id`, `ride_id`, `passager_id`, `statut`, `created_at`) VALUES
(2, 2, 3, 'annule', '2025-08-20 10:05:26'),
(3, 2, 3, 'confirme', '2025-08-20 10:25:10'),
(4, 3, 3, 'annule', '2025-08-20 12:32:33'),
(5, 2, 3, 'confirme', '2025-08-20 17:41:05'),
(6, 5, 3, 'confirme', '2025-10-13 08:15:59'),
(7, 5, 4, 'confirme', '2025-10-14 08:16:26'),
(8, 5, 5, 'confirme', '2025-10-14 08:28:12');

-- --------------------------------------------------------

--
-- Структура таблицы `preferences`
--

CREATE TABLE `preferences` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type_preference` varchar(50) DEFAULT NULL,
  `valeur` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `ride_id` int NOT NULL,
  `chauffeur_id` int NOT NULL,
  `passager_id` int NOT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text,
  `valide` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `ride_id`, `chauffeur_id`, `passager_id`, `note`, `commentaire`, `valide`, `created_at`) VALUES
(1, 5, 3, 3, 5, 'Très bien', 1, '2025-10-13 08:17:02');

-- --------------------------------------------------------

--
-- Структура таблицы `rides`
--

CREATE TABLE `rides` (
  `id` int NOT NULL,
  `chauffeur_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `ville_depart` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  `date_depart` date NOT NULL,
  `heure_depart` time NOT NULL,
  `heure_arrivee` time NOT NULL,
  `prix` int NOT NULL,
  `places_restantes` int NOT NULL,
  `eco` tinyint(1) DEFAULT '0',
  `statut` enum('planifie','en_cours','termine','annule') DEFAULT 'planifie'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `rides`
--

INSERT INTO `rides` (`id`, `chauffeur_id`, `vehicle_id`, `ville_depart`, `ville_arrivee`, `date_depart`, `heure_depart`, `heure_arrivee`, `prix`, `places_restantes`, `eco`, `statut`) VALUES
(2, 3, 2, 'Paris', 'Lille', '2025-08-21', '14:30:00', '18:00:00', 10, 2, 1, 'annule'),
(3, 3, 2, 'Milan', 'Lille', '2025-08-22', '14:00:00', '18:00:00', 10, 3, 1, 'termine'),
(4, 3, 2, 'Paris', 'Lille', '2025-08-27', '17:00:00', '21:00:00', 0, 3, 1, 'annule'),
(5, 3, 2, 'Paris', 'Lille', '2025-10-14', '14:30:00', '18:30:00', 10, 0, 1, 'termine'),
(6, 3, 2, 'Paris', 'Lille', '2025-10-15', '14:00:00', '18:00:00', 8, 3, 1, 'planifie');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `credits` int DEFAULT '20',
  `role` enum('utilisateur','employe','admin') DEFAULT 'utilisateur',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type_role` enum('passager','chauffeur','les deux') DEFAULT 'passager',
  `suspendu` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `pseudo`, `email`, `password`, `credits`, `role`, `created_at`, `type_role`, `suspendu`) VALUES
(1, 'chauffeur1', 'chauffeur1@test.com', '1234ab', 50, 'admin', '2025-08-20 09:06:58', 'passager', 1),
(2, 'passager1', 'passager1@test.com', '1234', 20, 'employe', '2025-08-20 09:06:59', 'passager', 1),
(3, 'ecf-user', 'igor.borisievich@gmail.com', '$2y$10$vLjHJ6RgEaHP1sjy3uTP7evw0jGCwoJHMQMSv/t76BgKwLmenR.yO', 46, 'admin', '2025-08-20 09:54:42', 'les deux', 0),
(4, 'ecf-user-employee', 'test.employe@test.com', '$2y$10$uaCQCGF5oduA3bjSVkUvLOoP.AfsPr6G0haZ7Kkh7wesHCWTmOK.a', 0, 'employe', '2025-10-14 08:14:04', 'passager', 0),
(5, 'ecf-user-test', 'test@test.com', '$2y$10$AzeRxdKyF7vWXPvhNGcQxO.oZk6fk2Znn3g1pvVY/jFPUpVXMtZ8m', 10, 'utilisateur', '2025-10-14 08:20:23', 'les deux', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `marque` varchar(50) NOT NULL,
  `modele` varchar(50) NOT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `immatriculation` varchar(20) NOT NULL,
  `date_immatriculation` date NOT NULL,
  `energie` enum('essence','diesel','hybride','electrique') NOT NULL,
  `places_disponibles` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `marque`, `modele`, `couleur`, `immatriculation`, `date_immatriculation`, `energie`, `places_disponibles`) VALUES
(1, 1, 'Tesla', 'Model 3', 'Noir', 'AB-123-CD', '2022-01-01', 'electrique', 3),
(2, 3, 'Tesla', 'Y', 'Blanche', 'KK999LL', '2025-08-07', 'electrique', 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ride_id` (`ride_id`),
  ADD KEY `passager_id` (`passager_id`);

--
-- Индексы таблицы `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ride_id` (`ride_id`),
  ADD KEY `chauffeur_id` (`chauffeur_id`),
  ADD KEY `passager_id` (`passager_id`);

--
-- Индексы таблицы `rides`
--
ALTER TABLE `rides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chauffeur_id` (`chauffeur_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `participations`
--
ALTER TABLE `participations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `preferences`
--
ALTER TABLE `preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `rides`
--
ALTER TABLE `rides`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `participations_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participations_ibfk_2` FOREIGN KEY (`passager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `preferences`
--
ALTER TABLE `preferences`
  ADD CONSTRAINT `preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`chauffeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`passager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `rides`
--
ALTER TABLE `rides`
  ADD CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`chauffeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
