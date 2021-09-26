-- Notes:
-- https://www.mysqltutorial.org/mysql-uuid/

CREATE SCHEMA IF NOT EXISTS tttdb DEFAULT CHARACTER SET utf8 ;
CHARACTER SET UTF8
COLLATE utf8_bin;

USE tttdb;

CREATE TABLE IF NOT EXISTS tttdb.player_info (
  player_id INT(11) UNSIGNED AUTO_INCREMENT NOT NULL, 
  token BINARY(16) NOT NULL, 
  client_hash VARCHAR(45),
  PRIMARY KEY (player_id),
  INDEX(token),
  UNIQUE INDEX client_hash_UNIQUE (client_hash ASC) VISIBLE,
  UNIQUE INDEX token_UNIQUE (token ASC) VISIBLE,
  UNIQUE INDEX player_id_UNIQUE (player_id ASC) VISIBLE
  )
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tttdb.game_status (
  game_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  token BINARY(16) NOT NULL,
  turn TINYINT(1) NOT NULL DEFAULT 1,
  state INT NOT NULL DEFAULT 0,
  moves_left TINYINT NULL DEFAULT 9,
  complete TINYINT(1) NOT NULL DEFAULT 0,
  winner TINYINT(1) NOT NULL DEFAULT 0,  
  start_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  end_time DATETIME NULL,
  UNIQUE INDEX game_id_UNIQUE (game_id ASC) VISIBLE,
  UNIQUE INDEX token_UNIQUE (token ASC) VISIBLE,
  PRIMARY KEY (game_id),
  INDEX (token)
  )
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tttdb.game (
  game_id int(11) UNSIGNED NOT NULL,
  player1 int(11) UNSIGNED NOT NULL,
  player2 int(11) UNSIGNED NOT NULL,  
  PRIMARY KEY (game_id, player1, player2),
  UNIQUE INDEX player2_UNIQUE (player2 ASC) VISIBLE,
  UNIQUE INDEX game_id_UNIQUE (game_id ASC) VISIBLE,
  UNIQUE INDEX player1_UNIQUE (player1 ASC) VISIBLE,
  CONSTRAINT player1
    FOREIGN KEY (player1)
    REFERENCES tttdb.player_info (player_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT player2
    FOREIGN KEY (player2)
    REFERENCES tttdb.player_info(player_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT game_id
    FOREIGN KEY (game_id)
    REFERENCES tttdb.game_status (game_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    )
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tttdb.player (
  game_id int(11) UNSIGNED NOT NULL,
  player_id int(11) UNSIGNED NOT NULL,
  mark TINYINT(1) NOT NULL,
  PRIMARY KEY (game_id, player_id),
  UNIQUE INDEX game_id_UNIQUE (game_id ASC) VISIBLE,
  UNIQUE INDEX player_id_UNIQUE (player_id ASC) VISIBLE,
  CONSTRAINT player_id
    FOREIGN KEY (player_id)
    REFERENCES tttdb.player_info (player_id)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  CONSTRAINT game_id
    FOREIGN KEY (game_id)
    REFERENCES tttdb.game_status (game_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    )
ENGINE = InnoDB;