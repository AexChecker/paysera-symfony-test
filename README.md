# Paysera exchange (Symfony)

## Description
Simple implementation of currency deposit and withdrawal commission calculation

## Usage
### Via Lando
1. Install Lando locally https://docs.lando.dev/getting-started/
2. Run `lando start`
3. Traditional task:
   - copy `filename.csv` file with input data to `./data` folder
   - update `.env` with given EXCHANGE_RATES_API_KEY or use your own
   - run `lando traditional-task filename.csv` 
4. Alternative task:
   - copy `filename.txt` file with input data to `./data` folder
   - run `lando alternative-task filename.txt`
5. Tests: 
   - run `lando phpunit`
6. Tools:
   - Run symfony console commands:
     - `lando symfony:console <command>`
   - Clear symfony cache:
     - `lando symfony:cc`
   - Coding standards:
     - Check: `lando php-cs-fixer:test`
     - Fix: `lando php-cs-fixer:fix`
   - Xdebug:
     - Enable: `lando xdebug-on`
     - Disable: `lando xdebug-off` 

### Via composer
1. Run `composer install`
2. Traditional task:
   - copy `filename.csv` file with input data to `./data` folder
   - update `.env` with given EXCHANGE_RATES_API_KEY or use your own
   - run `php bin/console traditional-task:process-deposit-withdrawal filename.csv`
3. Alternative task:
   - copy `filename.txt` file with input data to `./data` folder
   - run `php bin/console alternative-task:process-transactions filename.txt`
4. Tests:
   - run `composer phpunit`
