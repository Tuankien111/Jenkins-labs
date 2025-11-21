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
                    sh 'touch .env'
                    sh 'docker compose up -d --build --wait' 
                }
            }
        }

        // Giai đoạn 3: Cài đặt Dependencies
        stage('Install Dependencies') {
            steps {
                script {
                    echo '--- 📦 Đang cài Composer... ---'
                    sh 'docker compose exec -T app composer install --no-interaction --prefer-dist'
                    
                    echo '--- 🔑 Đang tạo Key & Migrate... ---'
                    sh 'docker compose exec -T app php artisan key:generate' //
                    sh 'docker compose exec -T app php artisan config:clear' //
                    sh 'docker compose exec -T app php artisan migrate:refresh --seed --force'
                    sh 'docker compose exec -T app php artisan storage:link'

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
    

    // --- GIAI ĐOẠN MỚI: ĐÓNG GÓI & ĐẨY LÊN KHO ---
        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo '--- 📦 Đang đóng gói Shipping... ---'
                    
                    // Lấy thông tin đăng nhập từ Jenkins Credentials
                    withCredentials([usernamePassword(credentialsId: 'docker-hub-auth', passwordVariable: 'DOCKER_PASS', usernameVariable: 'DOCKER_USER')]) {
                        
                        // 1. Đăng nhập Docker Hub
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
    
    }
    post {
        always {
            script {
                echo '--- 🧹 Dọn dẹp môi trường Test... ---'
                // sh 'docker compose down'
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