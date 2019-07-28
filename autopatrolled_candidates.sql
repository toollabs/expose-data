SELECT
  `user_id`,
  CONVERT(`user_name` USING 'utf8') AS 'name',
  CONVERT(`block_reasons` USING 'utf8') AS 'reason',
  `user_editcount` AS 'editcount',
  DATE_FORMAT(`user_registration`, '%Y-%m-%d') AS 'regdate'
FROM
  (
    SELECT
      REPLACE(`user_name`, ' ', '_') AS 'uname',
      `user_id`,
      `user_name`,
      `user_editcount`,
      `user_registration`
    FROM
      `user`
    WHERE
      `user`.`user_editcount` > 1000
      AND `user`.`user_id` NOT IN(
        SELECT
          `ug_user`
        FROM
          `user_groups`
      )
      AND (
        DATEDIFF(NOW(), `user`.`user_registration`) > 30
        OR `user`.`user_registration` IS NULL
      )
      AND `user`.`user_id` NOT IN(
        SELECT
          `ipb_user`
        FROM
          `ipblocks`
      )
      AND `user`.`user_id` IN(
        SELECT
          `actor_user`
        FROM
          `recentchanges_userindex`
          JOIN actor_recentchanges ON rc_actor = actor_id
      )
  ) AS q1
  LEFT JOIN (
    SELECT
      GROUP_CONCAT(`comment_text` SEPARATOR ' // ') AS 'block_reasons',
      `log_title`
    FROM
      `logging_logindex`
      JOIN `comment_logging` ON `log_comment_id` = `comment_id`
    WHERE
      `log_type` = 'block'
      AND `log_title` IN (
        SELECT
          REPLACE (`user_name`, ' ', '_') AS 'uname'
        FROM
          `user`
        WHERE
          `user`.`user_editcount` > 1000
          AND `user`.`user_id` NOT IN(
            SELECT
              `ug_user`
            FROM
              `user_groups`
          )
          AND (
            DATEDIFF(NOW(), `user`.`user_registration`) > 30
            OR `user`.`user_registration` IS NULL
          )
          AND `user`.`user_id` NOT IN(
            SELECT
              `ipb_user`
            FROM
              `ipblocks`
          )
          AND `user`.`user_id` IN(
            SELECT
              `actor_user`
            FROM
              `recentchanges_userindex`
              JOIN actor_recentchanges ON rc_actor = actor_id
          )
      )
    GROUP BY
      `log_title`
  ) AS `user_selection` ON `user_selection`.`log_title` = `q1`.`uname`
ORDER BY
  `user_editcount` DESC;

