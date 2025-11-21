pipeline {
    agent any

    environment {
        COMPOSE_PROJECT_NAME = "jenkins-test-build"
        DOCKER_HUB_USER = "kenejidev" 
        IMAGE_NAME = "jenkins-test-build"
        IMAGE_TAG = "${BUILD_NUMBER}"
        
        // Cấu hình Database
        DB_CONNECTION = "mysql"
        DB_PORT = "3306"
        DB_HOST = "db" 
        DB_DATABASE = "laravel_db"
        DB_USERNAME = "laravel"
        // Biến này được lấy từ Jenkins Credentials (Secret)
        DB_PASSWORD = credentials('prod-db-password')
        // Thêm biến Root Password cho MySQL setup (Best practice)
        DB_ROOT_PASSWORD = credentials('prod-db-password') 
    }

    stages {
        // Giai đoạn 1: Lấy code về
        stage('Checkout SCM') {
            steps {
                checkout scm
            }
        }

        // Giai đoạn 2: Khởi động môi trường
        stage('Start Docker Environment') {
            steps {
                script {
                    echo '--- 🚀 Đang dựng hệ thống Docker... ---'
                    sh """
                        echo "DB_CONNECTION=${DB_CONNECTION}" > .env
                        echo "DB_PORT=${DB_PORT}" >> .env
                        echo "DB_HOST=${DB_HOST}" >> .env
                        echo "DB_DATABASE=${DB_DATABASE}" >> .env
                        echo "DB_USERNAME=${DB_USERNAME}" >> .env
                        echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
                        echo "MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}" >> .env
                    """
                    sh 'docker compose up -d --build --wait' 
                }
            }
        }

        // Giai đoạn 3: Cài đặt Dependencies
        stage('Install Dependencies') {
            steps {
                script {
                    echo '--- 📦 Đang cài Composer... ---'
                    // Cài vendor
                    sh 'docker compose exec -T app composer install --no-interaction --prefer-dist'
                    
                    echo '--- 🔑 Đang tạo Key & Migrate... ---'
                    // Tạo App Key
                    sh 'docker compose exec -T app php artisan key:generate'
                    // Xóa cache config cũ để nhận biến môi trường mới
                    sh 'docker compose exec -T app php artisan config:clear'
                    // Chạy migration (DB thật trong container)
                    sh 'docker compose exec -T app php artisan migrate:refresh --seed --force'
                    // Link storage
                    sh 'docker compose exec -T app php artisan storage:link'
                }
            }
        }

        // Giai đoạn 4: Chạy Test (Trùm cuối)
        stage('Run Unit Tests') {
            steps {
                script {
                    echo '--- 🧪 Đang chạy Test... ---'
                    // Override DB connection sang sqlite memory để test nhanh hơn
                    sh 'docker compose exec -T app sh -c "DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test"'
                }
            }
        }
    

        // --- GIAI ĐOẠN 5: ĐÓNG GÓI & ĐẨY LÊN KHO ---
        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo '--- 📦 Đang đóng gói Shipping... ---'
                    
                    withCredentials([usernamePassword(credentialsId: 'docker-hub-auth', passwordVariable: 'DOCKER_PASS', usernameVariable: 'DOCKER_USER')]) {
                        
                        // 1. Đăng nhập Docker Hub (Dùng --password-stdin để bảo mật hơn)
                        sh 'echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin'
                        
                        // 2. Build Image (Production Ready)
                        sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ."
                        
                        // 3. Tagging (Versioning + Latest)
                        sh "docker tag ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                        
                        // 4. Push to Registry
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG}"
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                        
                        echo "✅ Đã đẩy ảnh lên: https://hub.docker.com/r/${DOCKER_HUB_USER}/${IMAGE_NAME}"
                    }
                }
            }
        }
    }

    post {
        always {
            script {
                echo '--- 🧹 Dọn dẹp môi trường Test... ---'
                // Luôn dọn dẹp container sau khi chạy xong để tiết kiệm tài nguyên server
                sh 'docker compose down -v' 
            }
        }
        
        // --- BÁO CÁO VỀ DISCORD ---
        success {
            script {
                discordSend(status: 'SUCCESS', message: "✅ Build #${env.BUILD_NUMBER} Thành Công! Image: ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG}")
            }
        }
        failure {
            script {
                discordSend(status: 'FAILURE', message: "❌ Build #${env.BUILD_NUMBER} Thất Bại! Đại vương ơi vào check log ngay!")
            }
        }
    }
}

// Hàm gửi Discord (Đã fix lỗi dấu nháy)
def discordSend(Map args) {
    def color = (args.status == 'SUCCESS') ? '3066993' : '15158332' // Xanh hoặc Đỏ
    def message = args.message
    
    withCredentials([string(credentialsId: 'discord-webhook', variable: 'DISCORD_URL')]) {
        sh """
            curl -H "Content-Type: application/json" \\
            -X POST \\
            -d '{"username": "Jenkins Bot", "embeds": [{"title": "${args.status}", "description": "${message}", "color": ${color}}]}' \\
            \$DISCORD_URL
        """
    }
}