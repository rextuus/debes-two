#!/bin/bash

# Replace the placeholders with appropriate values
DB_USER="debes_user"
DB_PASSWORD="password"
DB_NAME="debes"
BACKUP_DIR="."

# Create the backup file with a timestamp in the filename
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/$DB_NAME\_backup_$(date +%Y%m%d%H%M%S).sql