
rm /var/www/html/theme/lambda/moodleapp.css
touch /var/www/html/theme/lambda/moodleapp.css
cat > /var/www/html/theme/lambda/moodleapp.css << EOL
ion-app.app-root page-core-login-credentials .item-input {
	display: none;
}

ion-app.app-root page-core-login-credentials .item-heading {
	display: none;
}

Addon-block-timeline {
	display: none;
}

ion-app.app-root page-core-login-credentials .button {
	display: none;
}

EOL
cat /var/www/html/theme/lambda/moodleapp.css


