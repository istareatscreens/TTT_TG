CREATE SCHEMA IF NOT EXISTS tttdb DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE tttdb;

CREATE TABLE IF NOT EXISTS tttdb.player (
  player_id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
  player_token BINARY(16) NOT NULL, 
  client_hash VARCHAR(45),
  date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (player_id),
  INDEX(player_token),
  UNIQUE INDEX client_hash_UNIQUE (client_hash ASC) VISIBLE,
  UNIQUE INDEX player_token_UNIQUE (player_token ASC) VISIBLE,
  UNIQUE INDEX player_id_UNIQUE (player_id ASC) VISIBLE
  )
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tttdb.game (
  game_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  game_token BINARY(16) NOT NULL,
  player1 INT UNSIGNED DEFAULT NULL,
  player2 INT UNSIGNED DEFAULT NULL,  
  PRIMARY KEY (game_id),
  INDEX(game_token),
  UNIQUE INDEX game_id_UNIQUE (game_id ASC) VISIBLE,
  UNIQUE INDEX game_token_UNIQUE (game_token ASC) VISIBLE,
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

DROP EVENT IF EXISTS `remove_old_players`;
CREATE EVENT `remove_old_players`  ON SCHEDULE EVERY 1 DAY 
STARTS '2010-01-01 00:00:00' 
DO 
DELETE FROM `tttdb`.`players` where DATEDIFF(now(),`date_created`) > 1;

ALTER EVENT `remove_old_players` ON  COMPLETION PRESERVE ENABLE;