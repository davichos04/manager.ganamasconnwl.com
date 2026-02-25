-- Módulo Capacitaciones
-- Ejecutar en la base de datos del proyecto

CREATE TABLE IF NOT EXISTS `j6bp1_capacitaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `media_type` enum('video','image','pdf') NOT NULL DEFAULT 'video',
  `media_url` varchar(1024) NOT NULL,
  `thumbnail` varchar(1024) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_capacitaciones_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `j6bp1_capacitaciones_quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `capacitacion_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `max_attempts` int(11) DEFAULT NULL,
  `pass_score` int(3) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `reward_mode` enum('none','product','points') NOT NULL DEFAULT 'none',
  `reward_product_id` int(11) DEFAULT NULL,
  `reward_points` int(11) DEFAULT NULL,
  `reward_limit` int(11) NOT NULL DEFAULT 0,
  `reward_awarded_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_quiz_capacitacion` (`capacitacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `j6bp1_capacitaciones_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `image` varchar(1024) DEFAULT NULL,
  `ordering` int(11) DEFAULT 0,
  `type` enum('radio','checkbox','text','textarea','ranking') NOT NULL DEFAULT 'radio',
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_question_quiz` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `j6bp1_capacitaciones_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_answer_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
