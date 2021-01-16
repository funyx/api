include _$(env).env
export $(shell sed 's/=.*//' _$(env).env)
server:
	@echo starting live server on $$HOST:$$PORT
	nodemon --exec php -S $$HOST:$$PORT -t=phalcon phalcon/app.php
server-static:
	@echo starting static server on $$HOST:$$PORT
	php -S $$HOST:$$PORT -t=phalcon phalcon/app.php
test:
	./vendor/bin/phpunit
