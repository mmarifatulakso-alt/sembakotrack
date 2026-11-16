# Menggunakan base image PHP 8.2 dengan server Apache
FROM php:8.2-apache

# 1. Install komponen sistem (Ganti SQLite menjadi libpq-dev untuk PostgreSQL)
RUN apt-get update && apt-get install -y \
    zip unzip git curl nodejs npm libpq-dev

# 2. Install ekstensi PHP khusus untuk database PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# 3. Masukkan Composer ke dalam sistem Docker
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Tentukan folder kerja server
WORKDIR /var/www/html

# 5. Salin seluruh file proyek Laravel kamu ke dalam server Docker
COPY . .

# 6. Duplikat .env.example menjadi .env agar tidak terjadi error
RUN cp .env.example .env

# 7. Install dependensi Laravel & build tampilan (Tailwind/CSS/JS)
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
RUN php artisan key:generate

# 8. Buka gembok izin akses untuk folder Storage (Migrasi dipindah ke /setup-db)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Arahkan DocumentRoot Apache langsung ke folder /public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 10. Aktifkan penulisan ulang URL (mod_rewrite) untuk rute Laravel
RUN a2enmod rewrite

# 11. Beri tahu Render bahwa web ini menggunakan port 80
EXPOSE 80