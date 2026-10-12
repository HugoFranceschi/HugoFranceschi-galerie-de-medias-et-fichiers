-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 30 sep. 2026 à 05:49
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
-- Base de données : `galeriedephoto`
--

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `src` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `images`
--

INSERT INTO `images` (`id`, `src`, `id_user`) VALUES
(9, '/assets/Img/6aa963afc4289.png', 1),
(15, '/assets/Img/6aa966964c9bc.jpeg', 2),
(17, '/assets/Img/6aa96750732ab.jpeg', 2),
(18, '/assets/Img/6aa967972819c.jpeg', 2),
(19, '/assets/Img/6aa979d04998d.jpeg', 2),
(20, '/assets/Img/6aa979f0e18cd.jpeg', 10),
(22, '/assets/Img/6aa98438cdab1.jpeg', 11),
(26, '/assets/Img/6aaa963a9cece.jpeg', 1),
(35, '/assets/Img/6aaa9c5838da1.jpeg', 1),
(48, '/assets/Img/6aafe320d3723.jpeg', 1),
(52, '/assets/Img/6aafe394ac812.png', 11),
(55, '/assets/Img/6ab3da997b2c2.jpeg', 1),
(58, '/assets/Img/6ab3e0672083d.gif', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `email`) VALUES
(1, 'a', '$2y$12$v69Bqa25mBv5/bjc1ZWWneuOT.GMuD.YtJaoUNgovssfdTENbgiN.', 'franceschihugo6@gmail.com'),
(2, 'b', '$2y$12$NwIXmLmUDpE5XgUMVM/Ho.lZiSmDcmU.rZilGVxesnhuyovQlNiMu', 'b@b.b'),
(3, 'a', '$2y$12$gHdzBafGImvqhh0x71z33.4m13GVPPzDx3mm84/JoZiiD7AjXxv8a', 'a@a.a'),
(4, 'd', '$2y$12$aMdHRi5WyusDmzJX7ej61uur9Jnc6xinsYheLAzXKg/OjOoXSr5zi', 'd@d.d'),
(5, 'c', '$2y$12$8PLYre.mL9fgDBqetDT3uuXHlAZ2tMw5dx52rCGIQSE7pbalKd/L6', 'c@c.c'),
(6, 'e', '$2y$12$sIRIK4dp7uvuYxYiv0/alO0./KBwzsEDNTYmxnXo/wkMUelmp.vUK', 'e@e.e'),
(7, 'f', '$2y$12$lbTzF9fuOW3CdA3gpkICsOP.tVe4R9Bps8.tDQw.WjopBmixeHieC', 'f@f.f'),
(8, 't', '$2y$12$gWqUik7wbIF7tfU1SXuWH.YKQSD37tr4YyRO93wHcc2FrDxTk4Mwe', 't@t.t'),
(10, 'k', '$2y$12$K0oFhhbJz1dfnYr4k6Oqtebl3Ya5ohla2AsORnkBhX5cvhM4kQVDS', 'k@k.k'),
(11, 'fred', '$2y$12$ynfdJyInsGuRagy.J3Zj7OYSu05iTvWQdpse9r1jk3uEQaU7bUSPG', 'mickey075@hotmail.fr');

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
