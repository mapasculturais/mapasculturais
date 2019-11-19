
INSERT INTO usr(auth_provider, auth_uid, email, last_login_timestamp, create_timestamp, status)
VALUES
	(1, 1, 'Admin@local', current_date, current_date, 1);

INSERT INTO agent(user_id, type, name, create_timestamp, status)
	SELECT id, 1, email, current_date, 1
	FROM usr
	WHERE usr.email = 'Admin@local';

UPDATE usr SET profile_id = (SELECT id FROM agent WHERE email = 'Admin@local') WHERE email = 'Admin@local';

INSERT INTO role(usr_id, name)
	SELECT id, 'superAdmin'
	FROM usr
	WHERE usr.email = 'Admin@local';
