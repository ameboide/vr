-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 28, 2011 at 02:21 
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `vr`
--

-- --------------------------------------------------------

--
-- Table structure for table `juegos`
--

CREATE TABLE IF NOT EXISTS `juegos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) NOT NULL,
  `pista` varchar(40) NOT NULL COMMENT 'path al archivo con la imagen',
  `porte` int(11) NOT NULL COMMENT 'pixeles de la grilla',
  `escala` int(11) NOT NULL DEFAULT '100' COMMENT 'porcentaje del porte de la pista',
  `borde` int(11) NOT NULL DEFAULT '5' COMMENT 'cuadrados de borde alrededor de la pista',
  `vueltas` int(11) NOT NULL DEFAULT '1',
  `accion_choque` enum('EF','ES','VF','VS') NOT NULL DEFAULT 'EF' COMMENT 'Entrar por donde salio Frenando, Entrar Sin frenar, Volver a su posicion pre-choque Frenando, Volver Sin frenar',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `jugadores`
--

CREATE TABLE IF NOT EXISTS `jugadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `juego_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `color` varchar(40) NOT NULL DEFAULT '#a00',
  `choque_x` int(11) DEFAULT NULL COMMENT 'si existe, es donde tiene q volver despues de chocar',
  `choque_y` int(11) DEFAULT NULL,
  `vueltas` int(11) NOT NULL DEFAULT '-1',
  `lugar` int(11) DEFAULT NULL COMMENT 'lugar en q termino la carrera',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `juego_id_2` (`juego_id`,`usuario_id`),
  KEY `juego_id` (`juego_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `movimientos`
--

CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jugador_id` int(11) NOT NULL,
  `pos_x` int(11) NOT NULL,
  `pos_y` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `jugador_id` (`jugador_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sugerencias`
--

CREATE TABLE IF NOT EXISTS `sugerencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `txt` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `avatar` varchar(40) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `jugadores_ibfk_3` FOREIGN KEY (`juego_id`) REFERENCES `juegos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jugadores_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `movimientos`
--
ALTER TABLE `movimientos`
  ADD CONSTRAINT `movimientos_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sugerencias`
--
ALTER TABLE `sugerencias`
  ADD CONSTRAINT `sugerencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
