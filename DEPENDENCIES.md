# Install dependencies
brew install php
brew install mysql

# Start services
brew services start php
brew services start mysql

# Start the server
php -S localhost:8000

# Kill the server
kill $(lsof -ti :8000)

# Connect to MySQL db
mysql -u root

