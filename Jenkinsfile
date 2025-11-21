pipeline {
    agent any

    environment {
        COMPOSE_PROJECT_NAME = "jenkins-test-build"
        DOCKER_HUB_USER = "kenejidev" 
        IMAGE_NAME = "jenkins-test-build"
        IMAGE_TAG = "${BUILD_NUMBER}"
        DB_CONNECTION = "mysql"
        DB_PORT = "3306"
        DB_HOST = "db" 
        DB_DATABASE = "laravel_db"
        DB_USERNAME = "laravel"
        DB_PASSWORD = credentials('prod-db-password')
        DB_ROOT_PASSWORD = credentials('prod-db-password') 
        APP_KEY = credentials('prod-app-key') 
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
                    sh 'docker compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader'
                    
                    echo '--- 🔑 Đang tạo Key & Migrate... ---'
                    // Tạo App Key 
                    sh 'docker compose exec -T app php artisan key:generate'
                    sh 'docker compose exec -T app php artisan config:clear'
                    sh 'docker compose exec -T app php artisan migrate:refresh --force'
                    sh 'docker compose exec -T app php artisan db:seed --force || echo "⚠️ Seeding failed or skipped"'
                    sh 'docker compose exec -T app php artisan storage:link'
                    sh 'docker compose exec -T app php artisan route:cache'
                    sh 'docker compose exec -T app php artisan view:cache'
                    
                }
            }
        }

        // Giai đoạn 4: Chạy Test (Trùm cuối)
        stage('Run Unit Tests') {
            steps {
                script {
                    echo '--- 🧪 Đang chạy Test... ---'
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
                        sh 'echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin'
                        sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ."
                        sh "docker tag ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG}"
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                        
                        echo "✅ Đã đẩy ảnh lên: https://hub.docker.com/r/${DOCKER_HUB_USER}/${IMAGE_NAME}"
                    }
                }
            }
        }

        stage('Deploy to Local Prod') {
            steps {
                script {
                    echo '--- 🚀 Đang Deploy ra "Môi trường thật" (Port 8081)... ---'         
                    withEnv(["APP_KEY=${PROD_APP_KEY}"]) {
                        sh 'docker compose -f production/docker-compose.prod.yml -p my-prod-site down --remove-orphans'
                        sh 'docker compose -f production/docker-compose.prod.yml -p my-prod-site pull'
                        sh 'docker compose -f production/docker-compose.prod.yml -p my-prod-site up -d'
                    }
                    
                    echo '--- 🧹 Xóa Cache & Config cũ (Yêu cầu 3) ---'
                    sh 'docker exec my-prod-site-app-1 php artisan config:clear'
                    sh 'docker exec my-prod-site-app-1 php artisan cache:clear'
                    sh 'docker exec my-prod-site-app-1 php artisan route:clear'
                    
                    echo '--- 🗄️ Cập nhật Database & Seed ---'
                    // Migrate
                    sh 'docker exec my-prod-site-app-1 php artisan migrate --force'
                    // Seed (Tạm thời - Lưu ý: Seed nhiều lần có thể gây trùng dữ liệu nếu seeder không chuẩn)
                    sh 'docker exec my-prod-site-app-1 php artisan db:seed --force'
                    
                    echo '--- 🔗 Link Storage ---'
                    sh 'docker exec my-prod-site-app-1 php artisan storage:link'
                    
                    echo '--- ✅ Deploy Production Giả Lập Hoàn Tất! ---'
                }
            }
        }
    }

    post {
        always {
            script {
                echo '--- 🧹 Dọn dẹp môi trường Test... ---'
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