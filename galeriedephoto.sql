-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 19 nov. 2026 à 15:30
-- Version du serveur : 8.4.7
-- Version de PHP : 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `galeriemedia_galerie`
--

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `src` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int NOT NULL,
  `srcName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `images`
--

INSERT INTO `images` (`id`, `src`, `id_user`, `srcName`) VALUES
(127, '/assets/Img/6ad08408e8e16.mp4', 1, '【推しの子】Bのリベンジ  B小町【第二十五話「入れ込み」挿入歌】'),
(128, '/assets/Img/6ad0841ed31ff.mp3', 1, '【推しの子】B小町  POP IN 2【MV short】'),
(129, '/assets/Img/6ad08486c603e.png', 1, 'danse'),
(130, '/assets/Img/6ad084cc0e8ac.mp4', 1, '0109'),
(131, '/assets/Img/6ad084d9a1683.mp4', 1, '0106(1)'),
(132, '/assets/Img/6ad085b20c40d.zip', 1, 'Nouveau dossier'),
(133, '/assets/Img/6ad085c37bda4.pdf', 1, 'CV Hugo Franceschi'),
(135, '/assets/Img/6adcfee4c6efa.jpg', 13, '4514ac19e9af42452137285ec798f52a'),
(137, '/assets/Img/6adcff148e492.zip', 13, 'resourcepacks'),
(138, '/assets/Img/6adcff1e1c00f.pdf', 13, 'CV Hugo Franceschi');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `email`) VALUES
(1, 'Nyphi', '$2y$12$v69Bqa25mBv5/bjc1ZWWneuOT.GMuD.YtJaoUNgovssfdTENbgiN.', 'franceschihugo6@gmail.com'),
(3, 'a', '$2y$12$gHdzBafGImvqhh0x71z33.4m13GVPPzDx3mm84/JoZiiD7AjXxv8a', 'a@a.a'),
(11, 'fred', '$2y$12$ynfdJyInsGuRagy.J3Zj7OYSu05iTvWQdpse9r1jk3uEQaU7bUSPG', 'mickey075@hotmail.fr'),
(12, 'Cédric', '$2y$12$jnsR0Vdd3ReFrKRly2o6/eRXjFjEsLtbI.Vcd.2FGm8ozLfp3m7Ti', 'classale@edenschool.fr'),
(13, 'lois', '$2y$12$G6pkwuS478kTw6O2Ng9tX.jlVUErNHekh5W8.nHeyXdzvIJ/BusPe', 'lois.pichat@icloud.com');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
