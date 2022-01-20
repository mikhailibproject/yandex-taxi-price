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

reportai: ## Выгружает данные для обучения ти контроля AI из БД SQLite в CSV файл
	php cli yandextaxi:reportai -o "./storage/report" -b "2021-10-25" -e "2021-11-17" -c "2021-11-18" -t "2021-11-24"

stdevdate: ##  БД SQLite в CSV файл
	php cli yandextaxi:statistics -o "./storage/report" -b "2021-10-25" -e "2021-11-17" -c "2021-11-18" -t "2021-11-24" -m "stdev" -p "date"

stdevweek: ##  БД SQLite в CSV файл
	php cli yandextaxi:statistics -o "./storage/report" -b "2021-10-25" -e "2021-11-17" -c "2021-11-18" -t "2021-11-24" -m "stdev" -p "week"

stdevmonth: ##  БД SQLite в CSV файл
	php cli yandextaxi:statistics -o "./storage/report" -b "2021-10-25" -e "2021-11-17" -c "2021-11-18" -t "2021-11-24" -m "stdev" -p "month"

stdevall: ##  БД SQLite в CSV файл
	php cli yandextaxi:statistics -o "./storage/report" -b "2021-10-25" -e "2021-11-17" -c "2021-11-18" -t "2021-11-24" -m "stdev" -p "all"