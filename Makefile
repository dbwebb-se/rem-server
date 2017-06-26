#
#
#

# Detect OS
OS = $(shell uname -s)

# Defaults
ECHO = echo

# Make adjustments based on OS
# http://stackoverflow.com/questions/3466166/how-to-check-if-running-in-cygwin-mac-or-linux/27776822#27776822
ifneq (, $(findstring CYGWIN, $(OS)))
	ECHO = /bin/echo -e
endif

# Colors and helptext
NO_COLOR	= \033[0m
ACTION		= \033[32;01m
OK_COLOR	= \033[32;01m
ERROR_COLOR	= \033[31;01m
WARN_COLOR	= \033[33;01m

# Which makefile am I in?
WHERE-AM-I = $(CURDIR)/$(word $(words $(MAKEFILE_LIST)),$(MAKEFILE_LIST))
THIS_MAKEFILE := $(call WHERE-AM-I)

# Echo some nice helptext based on the target comment
HELPTEXT = $(ECHO) "$(ACTION)--->" `egrep "^\# target: $(1) " $(THIS_MAKEFILE) | sed "s/\# target: $(1)[ ]*-[ ]* / /g"` "$(NO_COLOR)"

# Add local bin path for test tools
#PATH := "./.bin:./vendor/bin:./node_modules/.bin:$(PATH)"
#SHELL := env PATH=$(PATH) $(SHELL)
PHPUNIT := .bin/phpunit
PHPLOC 	:= .bin/phploc
PHPCS   := .bin/phpcs
PHPCBF  := .bin/phpcbf
PHPMD   := .bin/phpmd
PHPDOC  := .bin/phpdoc
BEHAT   := .bin/behat



# target: help               - Displays help.
.PHONY:  help
help:
	@$(call HELPTEXT,$@)
	@$(ECHO) "Usage:"
	@$(ECHO) " make [target] ..."
	@$(ECHO) "target:"
	@egrep "^# target:" $(THIS_MAKEFILE) | sed 's/# target: / /g'



# target: prepare            - Prepare for tests and build
.PHONY:  prepare
prepare:
	@$(call HELPTEXT,$@)
	[ -d .bin ] || mkdir .bin
	[ -d build ] || mkdir build
	rm -rf build/*



# target: clean              - Removes generated files and directories.
.PHONY: clean
clean:
	@$(call HELPTEXT,$@)
	rm -rf build



# target: clean-all          - Removes generated files and directories.
.PHONY:  clean-all
clean-all: clean
	@$(call HELPTEXT,$@)
	rm -rf .bin vendor composer.lock



# target: check              - Check version of installed tools.
.PHONY:  check
check: check-tools-php
	@$(call HELPTEXT,$@)



# target: test               - Run all tests.
.PHONY:  test
test: phpunit phpcs phpmd phploc behat
	@$(call HELPTEXT,$@)
	composer validate



# target: doc                - Generate documentation.
.PHONY:  doc
doc: phpdoc
	@$(call HELPTEXT,$@)



# target: build              - Do all build
.PHONY:  build
build: test doc #less-compile less-minify js-minify
	@$(call HELPTEXT,$@)



# target: install            - Install all tools
.PHONY:  install
install: prepare install-tools-php
	@$(call HELPTEXT,$@)



# target: update             - Update the codebase and tools.
.PHONY:  update
update:
	@$(call HELPTEXT,$@)
	git pull
	composer update



# target: tag-prepare        - Prepare to tag new version.
.PHONY: tag-prepare
tag-prepare:
	@$(call HELPTEXT,$@)



# target: ssl-cert-create    - One way to create the certificates.
.PHONY: ssl-cert-create
ssl-cert-create:
	sudo certbot certonly --standalone -d $(WWW_SITE) -d www.$(WWW_SITE)



# target: ssl-cert-update    - Update certificates with new expiray date.
.PHONY: ssl-cert-renew
ssl-cert-renew:
	sudo service apache2 stop
	sudo certbot renew
	sudo service apache2 start



# target: virtual-host       - Create entries for the virtual host http.
.PHONY: virtual-host

define VIRTUAL_HOST_80
Define site $(WWW_SITE)
ServerAdmin $(SERVER_ADMIN)

<VirtualHost *:80>
	ServerName $${site}
	ServerAlias local.$${site}
	ServerAlias do1.$${site}
	ServerAlias do2.$${site}
	ServerAlias bth1.$${site}
	DocumentRoot $(HTDOCS_BASE)/$${site}/htdocs

	<Directory />
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
		Order allow,deny
		Allow from all

		Options +ExecCGI
		AddHandler cgi-script .cgi
	</Directory>

	<FilesMatch "\.(jpe?g|png|gif|js|css|svg|ttf|otf|eot|woff|woff2|ico)$">
		ExpiresActive On
		ExpiresDefault "access plus 1 week"
	</FilesMatch>

	<FilesMatch "\.(jpe?g|png|gif|js|css|svg|ttf|otf|eot|woff|woff2|ico)$">
		ExpiresActive On
		ExpiresDefault "access plus 1 week"
	</FilesMatch>

	Include $(HTDOCS_BASE)/$${site}/config/apache-redirects
	Include $(HTDOCS_BASE)/$${site}/config/apache-rewrites 

	#LogLevel alert rewrite:trace6
	# tail -f error.log | fgrep '[rewrite:'

	ErrorLog  $(HTDOCS_BASE)/$${site}/error.log
	CustomLog $(HTDOCS_BASE)/$${site}/access.log combined
</VirtualHost>
endef
export VIRTUAL_HOST_80

define VIRTUAL_HOST_80_WWW
Define site $(WWW_SITE)
ServerAdmin $(SERVER_ADMIN)

<VirtualHost *:80>
	ServerName www.$${site}
	Redirect "/" "http://$${site}/"
</VirtualHost>
endef
export VIRTUAL_HOST_80_WWW

virtual-host:
	@$(call HELPTEXT,$@)
	install -d $(LOCAL_HTDOCS)/htdocs
	$(ECHO) "$$VIRTUAL_HOST_80" | sudo bash -c 'cat > /etc/apache2/sites-available/$(WWW_SITE).conf'
	$(ECHO) "$$VIRTUAL_HOST_80_WWW" | sudo bash -c 'cat > /etc/apache2/sites-available/www.$(WWW_SITE).conf'
	sudo a2ensite $(WWW_SITE) www.$(WWW_SITE)
	sudo a2enmod rewrite expires cgi
	sudo apachectl configtest
	sudo service apache2 reload

# target: virtual-host-https - Create entries for the virtual host https.
.PHONY: virtual-host-https

define VIRTUAL_HOST_443
Define site $(WWW_SITE)
ServerAdmin $(SERVER_ADMIN)

<VirtualHost *:80>
	ServerName $${site}
	ServerAlias do1.$${site}
	ServerAlias do2.$${site}
	ServerAlias bth1.$${site}
	Redirect "/" "https://$${site}/"
</VirtualHost>

<VirtualHost *:443>
	Include $(SSL_APACHE_CONF)
	SSLCertificateFile 		$(SSL_PEM_BASE)/cert.pem
	SSLCertificateKeyFile 	$(SSL_PEM_BASE)/privkey.pem
	SSLCertificateChainFile $(SSL_PEM_BASE)/chain.pem

	ServerName $${site}
	ServerAlias do1.$${site}
	ServerAlias do2.$${site}
	DocumentRoot $(HTDOCS_BASE)/$${site}/htdocs

	<Directory />
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
		Order allow,deny
		Allow from all

		Options +ExecCGI
		AddHandler cgi-script .cgi
	</Directory>

	<FilesMatch "\.(jpe?g|png|gif|js|css|svg|ttf|otf|eot|woff|woff2|ico)$">
		ExpiresActive On
		ExpiresDefault "access plus 1 week"
	</FilesMatch>

	Include $(HTDOCS_BASE)/$${site}/config/apache-redirects
	Include $(HTDOCS_BASE)/$${site}/config/apache-rewrites 

	#LogLevel alert rewrite:trace6
	# tail -f error.log | fgrep '[rewrite:'

	ErrorLog  $(HTDOCS_BASE)/$${site}/error.log
	CustomLog $(HTDOCS_BASE)/$${site}/access.log combined
</VirtualHost>
endef
export VIRTUAL_HOST_443

define VIRTUAL_HOST_443_WWW
Define site $(WWW_SITE)
ServerAdmin $(SERVER_ADMIN)

<VirtualHost *:80>
	ServerName www.$${site}
	Redirect "/" "https://www.$${site}/"
</VirtualHost>

<VirtualHost *:443>
	Include $(SSL_APACHE_CONF)
	SSLCertificateFile 		$(SSL_PEM_BASE)/cert.pem
	SSLCertificateKeyFile 	$(SSL_PEM_BASE)/privkey.pem
	SSLCertificateChainFile $(SSL_PEM_BASE)/chain.pem

	ServerName www.$${site}
	Redirect "/" "https://$${site}/"
</VirtualHost>
endef
export VIRTUAL_HOST_443_WWW

virtual-host-https:
	@$(call HELPTEXT,$@)
	$(ECHO) "$$VIRTUAL_HOST_443" | sudo bash -c 'cat > /etc/apache2/sites-available/$(WWW_SITE).conf'
	$(ECHO) "$$VIRTUAL_HOST_443_WWW" | sudo bash -c 'cat > /etc/apache2/sites-available/www.$(WWW_SITE).conf'
	sudo a2enmod ssl
	sudo apachectl configtest
	sudo service apache2 reload
