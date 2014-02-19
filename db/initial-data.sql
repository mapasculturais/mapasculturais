
INSERT INTO usr(auth_provider, auth_uid, email, last_login_timestamp, create_timestamp, status)
VALUES
	(1, 1, 'Admin@local', current_date, current_date, 1);

INSERT INTO agent(user_id, type, name, is_user_profile, create_timestamp, status)
	SELECT id, 1, email, true, current_date, 1
	FROM usr
	WHERE usr.email = 'Admin@local';

INSERT INTO role(usr_id, name)
	SELECT id, 'superAdmin'
	FROM usr
	WHERE usr.email = 'Admin@local';
