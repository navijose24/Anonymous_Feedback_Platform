-- Anonymous Feedback Platform — MySQL Schema
-- Run this in phpMyAdmin or mysql CLI after creating the database.

CREATE DATABASE IF NOT EXISTS anon_feedback
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE anon_feedback;

-- ─── Users ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    username      VARCHAR(40)      NOT NULL,          -- used in /f/<username>
    name          VARCHAR(100)     NOT NULL,
    email         VARCHAR(255)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Feedback ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS feedback (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    receiver_id INT UNSIGNED     NOT NULL,
    category    ENUM('Appreciation','Constructive Criticism','Suggestion','Improvement')
                                 NOT NULL DEFAULT 'Appreciation',
    message     TEXT             NOT NULL,
    is_read     TINYINT(1)       NOT NULL DEFAULT 0,
    is_saved    TINYINT(1)       NOT NULL DEFAULT 0,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_receiver (receiver_id),
    KEY idx_created  (created_at),
    CONSTRAINT fk_feedback_receiver
        FOREIGN KEY (receiver_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reports (optional spam/abuse table) ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS reports (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    feedback_id INT UNSIGNED     NOT NULL,
    reason      VARCHAR(255)     NOT NULL,
    status      ENUM('pending','reviewed','dismissed')
                                 NOT NULL DEFAULT 'pending',
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_report_feedback (feedback_id),
    CONSTRAINT fk_report_feedback
        FOREIGN KEY (feedback_id) REFERENCES feedback (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
