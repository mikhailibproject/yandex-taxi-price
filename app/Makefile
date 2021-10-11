# include variables
include .env
CURRENT_DT = $(shell date "+%Y%m%d_%H%M%S")
.DEFAULT_GOAL:=help

help: ## Эта справка
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

get-data: ## Получить данные из Яндекс.Такси и данные Яндекс.Погода
	php cli yandextaxi:get-data

init-sql: ## Создать базу двнных и очистить таблицы SQLite
	php cli yandextaxi:init

report: ## Выгружает данные запросов из БД SQLite в CSV файл
	php cli yandextaxi:report -o "./storage/report/taxi_price${CURRENT_DT}.csv"
