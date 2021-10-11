# include variables
include .env

.DEFAULT_GOAL:=help

help: ## Эта справка
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# tests
run-tests: ## Запуск тестов
	./vendor/bin/phpunit --colors --testdox ./tests/

#
get-data: ## Получить данные из Яндекс.Такси "дом -> работа"
	php cli yandextaxi:get-data

init-sql: ## Создать или очистить таблицы SQLite
	php cli yandextaxi:init

report: ## Создать или очистить таблицы SQLite
	php cli yandextaxi:report -o test.csv
