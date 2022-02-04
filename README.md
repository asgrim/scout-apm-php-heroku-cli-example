# Heroku CLI Example using Scout PHP

Simple Heroku CLI command example for Scout

## Running Heroku

NOTE: add your scout key to the example below:

```bash
heroku create
heroku config:set SCOUT_KEY=<add your scout key here> SCOUT_NAME=heroku-cli-example SCOUT_MONITOR=true
git push heroku main
heroku ps:scale web=0 # Not using web nodes for this example
heroku run php long-running-process.php
```

## Running Locally

NOTE: add your scout key to the example below:

```bash
export SCOUT_KEY=<add your scout key here>
export SCOUT_NAME=heroku-cli-example
export SCOUT_MONITOR=true

LONG_PROCESS_ITERATION_COUNT=3 WAIT_AFTER_ITERATION_SECS=1 SINGLE_ITERATION_SLEEP_SECS=1 php long-running-process.php
php short-process.php
```
