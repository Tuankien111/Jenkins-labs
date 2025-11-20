pipeline {
    agent any

    environment {
        // Đặt tên project để dễ quản lý
        COMPOSE_PROJECT_NAME = "jenkins-test-build"
    }

    stages {
        // Giai đoạn 1: Lấy code về
        stage('Checkout SCM') {
            steps {
                // Jenkins tự động lấy code từ Git (do cấu hình ở bước sau)
                checkout scm
            }
        }

        // Giai đoạn 2: Khởi động môi trường
        stage('Start Docker Environment') {
            steps {
                script {
                    echo '--- 🚀 Đang dựng hệ thống Docker... ---'
                    sh 'cp .env.example .env'
                    sh "sed -i 's/DB_DATABASE=laravel/DB_DATABASE=laravel_db/g' .env"
                    sh "sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/g' .env"
                    sh 'docker compose down || true'

                    // Dựng container lên (-d: chạy ngầm)
                    sh 'docker compose up -d --build'
                    
                    // Đợi 15s cho MySQL kịp khởi động (Hack trick cho MySQL)
                    sh 'sleep 15'
                }
            }
        }

        // Giai đoạn 3: Cài đặt Dependencies
        stage('Install Dependencies') {
            steps {
                script {
                    echo '--- 📦 Đang cài Composer... ---'
                    // -T: Tắt chế độ TTY (Bắt buộc khi chạy trong Jenkins)
                    sh 'docker compose exec -T app composer install --no-interaction --prefer-dist'
                    
                    echo '--- 🔑 Đang tạo Key & Migrate... ---'
                    sh 'docker compose exec -T app php artisan key:generate'
                    sh 'docker compose exec -T app php artisan config:clear'
                    sh 'docker compose exec -T app php artisan migrate:refresh --seed --force'
                }
            }
        }

        // Giai đoạn 4: Chạy Test (Trùm cuối)
        stage('Run Unit Tests') {
            steps {
                script {
                    echo '--- 🧪 Đang chạy Test... ---'
                    // Chạy test với cấu hình SQLite in-memory để tốc độ cao nhất
                    sh 'docker compose exec -T app sh -c "DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test"'
                }
            }
        }
    }

    // Dọn dẹp sau khi chạy xong
    post {
        always {
            script {
                echo '--- 🧹 Dọn dẹp container... ---'
                sh 'docker compose down'
            }
        }
    }
}