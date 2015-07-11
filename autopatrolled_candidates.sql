DROP DATABASE IF EXISTS `s51857__c_tmp`;
CREATE DATABASE `s51857__c_tmp`;
CREATE TABLE `s51857__c_tmp`.`tmp_apc_com` ENGINE=MEMORY (
	SELECT REPLACE(`user_name`, ' ', '_') AS 'uname',
		`user_id`,
		`user_name`,
		`user_editcount`,
		`user_registration`
	FROM `user`
		/* there are very few users with > 1000 edits compared to the overall number of users */
		WHERE `user`.`user_editcount` > 1000
		/* DB should be prepared for joining these two (MediaWiki does this all the time); */
		/* user_groups is supposedly a relatively small table */
		AND `user`.`user_id` NOT IN ( SELECT `ug_user` FROM `user_groups` )
		/* do not include brand new users who just experimented with Cat-A-Lot */
		AND (DATEDIFF( NOW(), `user`.`user_registration` ) > 30 OR `user`.`user_registration` IS NULL)
		/* and of course these users should not be blocked currently */
		AND `user`.`user_id` NOT IN ( SELECT `ipb_user` FROM `ipblocks` )
		/* and have an edit or other contribution within the last 30 days */
		/* this makes the query notably slower but should be almost constant */
		/* even if we get a lot new users or operate this wiki another 10 years */
		/* while user_daily_contribs will grow over time and might consume even more */
		/* resources in the future */
		AND `user`.`user_id` IN ( SELECT `rc_user` FROM `recentchanges_userindex` ));

SELECT `user_id`,
	CONVERT(`user_name` USING 'utf8') AS 'name',
	CONVERT(`block_reasons` USING 'utf8') AS 'reason',
	`user_editcount` AS 'editcount',
	DATE_FORMAT(`user_registration`, '%Y-%m-%d') AS 'regdate'
FROM `s51857__c_tmp`.`tmp_apc_com`
LEFT JOIN (SELECT GROUP_CONCAT(`log_comment` SEPARATOR ' // ') AS 'block_reasons', `log_title`
	FROM `logging_userindex`
	WHERE `log_type`='block' AND `log_title` IN
		(SELECT `uname` FROM `s51857__c_tmp`.`tmp_apc_com`)
		GROUP BY `log_title`)
	AS `user_selection`
ON `user_selection`.`log_title`=`s51857__c_tmp`.`tmp_apc_com`.`uname`
ORDER BY `user_editcount` DESC;
DROP DATABASE IF EXISTS `s51857__c_tmp`;

