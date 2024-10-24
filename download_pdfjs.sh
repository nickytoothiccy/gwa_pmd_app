#!/bin/bash

# Create the js directory if it doesn't exist
mkdir -p /var/www/html/gwa_pmd_app/public/js

# Download the PDF.js files
wget -O /var/www/html/gwa_pmd_app/public/js/pdf.min.js https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js
wget -O /var/www/html/gwa_pmd_app/public/js/pdf.worker.min.js https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.worker.min.js

# Set appropriate permissions
chmod 644 /var/www/html/gwa_pmd_app/public/js/pdf.min.js
chmod 644 /var/www/html/gwa_pmd_app/public/js/pdf.worker.min.js

echo "PDF.js files have been downloaded and permissions set."
