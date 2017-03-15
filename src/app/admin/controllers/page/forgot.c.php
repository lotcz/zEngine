<?php

	include_once $home_dir . 'classes/emails.php';
	
	$page_title = t('Forgotten Password');
	
	if (isset($_POST['email'])) {
		$zUser = new User($db);
		$zUser->loadByLoginOrEmail($_POST['email']);
		if ($zUser->is_loaded) {
			$reset_token = generateToken(50);
			$expires = time() + (60*60*24*User::$reset_password_expires_days);
			$zUser->data['user_reset_password_hash'] = Authentication::hashPassword($reset_token);			
			$zUser->data['user_reset_password_expires'] = ModelBase::mysqlTimestamp($expires);
			$zUser->save();
			
			$email_text = t('To reset your password, visit this link: %s/admin/reset-password/%d?reset_token=%s. This link is only valid for %d days.', $base_url, $zUser->val('user_id'), $reset_token, User::$reset_password_expires_days);			
			Emails::sendPlain($globals['emails_from'], $zUser->val('user_email'), '', t('Forgotten Password'), $email_text);
			$messages->add(t('An e-mail was sent to your address with reset password instructions.'));
		} else {
			// increase ip address failed attempts here
			// *
			$messages->error(t('This e-mail address or login was not found in our database.'));
		}
	}