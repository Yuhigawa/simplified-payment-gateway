.PHONY: test ci

cs-check:
	./vendor/bin/phpcs

cs-fix:
	./vendor/bin/phpcbf

cpd-check:
	phpcpd app

md-check:
	./vendor/bin/phpmd app text phpmd.xml

test:
	composer test

ci: cs-check cpd-check md-check #test
