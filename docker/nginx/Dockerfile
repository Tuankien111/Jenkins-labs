FROM nginx:stable-alpine

# 1. Copy cấu hình Nginx vào trong ảnh
COPY default.conf /etc/nginx/conf.d/default.conf

# 2. Copy code frontend (public folder) để Nginx phục vụ file tĩnh
# Lưu ý: Đường dẫn này tính từ context (thư mục gốc) mà ta sẽ khai báo ở bước sau
COPY public /var/www/public