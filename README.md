## Start API backend


### 1. Clone Repo to your local machine
Open terminal and run this command in terminal.
```
git clone https://github.com/KingStar1001/innoscripta-laravel-api.git
```
```
cd innoscripta-laravel-api
```

### 2. Make .env file
Copy `.env.example` file and rename `.env`

### 3. Build the environment and Run
Run this command in terminal.
```
docker-compose build && docker-compose up -d && docker-compose logs -f
```

### 4. Composer and artisan:
- **Create vendor**
```
docker-compose exec laravel-app composer install
```
- **Key generate**
```
docker-compose exec php artisan key:generate
```
- **JWT token generate**
```
docker-compose exec php artisan jwt:secret
```
- **Migrate tables**
```
docker-compose exec php artisan migrate
```
### 5. Set API keys
Input your News API keys in `.env`
```
NEWSAPI_TOKEN={your_api_key}
GUARDIAN_TOKEN={your_api_key}
NYTIMES_TOKEN={your_api_key}
```
### 6. Check running containers
Once all the containers are up and running, we can check them by `docker ps`

You can check if the API server is running in http://localhost:8000


