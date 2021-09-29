CREATE SCHEMA IF NOT EXISTS tttdb DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE tttdb;

CREATE TABLE IF NOT EXISTS tttdb.player (
  player_id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
  token BINARY(16) NOT NULL, 
  client_hash VARCHAR(45),
  PRIMARY KEY (player_id),
  INDEX(token),
  UNIQUE INDEX client_hash_UNIQUE (client_hash ASC) VISIBLE,
  UNIQUE INDEX token_UNIQUE (token ASC) VISIBLE,
  UNIQUE INDEX player_id_UNIQUE (player_id ASC) VISIBLE
  )
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tttdb.game (
  game_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  token BINARY(16) NOT NULL,
  player1 INT UNSIGNED DEFAULT NULL,
  player2 INT UNSIGNED DEFAULT NULL,  
  PRIMARY KEY (game_id),
  INDEX(token),
  UNIQUE INDEX game_id_UNIQUE (game_id ASC) VISIBLE,
  CONSTRAINT player1
    FOREIGN KEY (player1)
    REFERENCES tttdb.player (player_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT player2
    FOREIGN KEY (player2)
    REFERENCES tttdb.player (player_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
    )
ENGINE = InnoDB;