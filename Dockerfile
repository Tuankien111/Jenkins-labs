# Sử dụng PHP 8.2 FPM làm nền tảng
FROM php:8.2-fpm

# 1. Cài đặt các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# 2. Cài đặt PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 3. Cài đặt Composer (Lấy từ image official)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Thiết lập thư mục làm việc
WORKDIR /var/www

# 5. Tạo user riêng để tránh lỗi permission (như bài trước tôi đã dạy)
# UID 1000 thường trùng với user máy thật
RUN groupadd -g 1000 laravel
RUN useradd -u 1000 -ms /bin/bash -g laravel laravel

# 6. Copy toàn bộ code vào container (Dành cho Production Build sau này)
COPY . /var/www
# Cấp quyền cho folder storage
RUN chown -R laravel:laravel /var/www

# 7. Chuyển sang user laravel
USER laravel

EXPOSE 9000
CMD ["php-fpm"]