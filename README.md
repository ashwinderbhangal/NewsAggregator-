# News Aggregator API

This project is a Laravel-based backend application to fetch and manage news articles from multiple sources, including **NewsAPI**, **The Guardian**, and **The New York Times**. Articles are fetched for configurable sections (categories) and stored in a MySQL database.

---

## Features

- Fetch articles from multiple sources:
  - **NewsAPI**
  - **The Guardian API**
  - **The New York Times API**
- Configurable sections (categories) using `.env`.
- Search and filter articles by query, category, date, source, or author.
- Paginate results for better accessibility.
- Scheduler fetches articles **four times a day**.
- Laravel Queues ensure background job processing for efficient performance.

---

## Requirements

- PHP 8.2 or higher
- Composer (PHP Dependency Manager)
- MySQL 5.7 or higher
- API keys:
   - **NewsAPI** ([Get API Key](https://newsapi.org))
   - **The Guardian API** ([Get API Key](https://open-platform.theguardian.com))
   - **New York Times API** ([Get API Key](https://developer.nytimes.com))

---

## Installation Steps

### 1. Clone the Repository
Run the following commands to clone the project to your local machine:

```bash
1. git clone <repository_url>
cd NewsAggregator


2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy `.env.example` to `.env` and update the database and API keys:
   ```bash
   cp .env.example .env
   ```

   Update the following variables in `.env`:
   # Database Configuration
   DB_DATABASE=news_db
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password

   # API Keys
   NEWS_API_KEY=your_newsapi_key
   GUARDIAN_API_KEY=your_guardian_api_key
   NYT_API_KEY=your_nyt_api_key

   # News Sections (Categories)
   NEWS_SECTIONS=general,technology,business,sports,health,world


4. Run migrations:
   ```bash
   php artisan migrate
   ```

5. Queue setup:
   Start the queue worker:
   ```bash
   php artisan queue:work
   ```

6. Serve the application:
   ```bash
   php artisan serve
   ```

## Scheduler Setup
The scheduler fetches articles three times daily (12 AM, 8 AM, and 4 PM).
Ensure your system supports Laravel scheduling:

1. Open your crontab file:
   ```bash
   crontab -e
   ```

2. Add the following line:
   ```bash
   * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
   ```

## Endpoints
### Fetch Articles
- **GET** `/api/v1/articles`
  - Query Parameters:
    - `query`: Search query (e.g., `?query=climate`)
    - `category`: Filter by category (e.g., `?category=world`)
    - `source`: Filter by source (e.g., `?source=BBC News`)
    - `date`: Filter by date (e.g., `?date=2024-12-16`)
    - `author`: Filter by author (e.g., `?author=John Doe`)
    - `per_page`: Results per page (e.g., `?per_page=10`)
    - `page`: Page number (e.g., `?page=3`)

## License
This project is licensed under the MIT License.
