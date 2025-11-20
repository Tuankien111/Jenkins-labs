# jenkins-setup/Dockerfile
FROM jenkins/jenkins:lts-jdk17

USER root

# 1. Cài đặt các gói cần thiết để cài Docker
RUN apt-get update && apt-get install -y lsb-release

# 2. Tải và cài đặt Docker CLI (Client)
RUN curl -fsSLo /usr/share/keyrings/docker-archive-keyring.asc \
  https://download.docker.com/linux/debian/gpg
RUN echo "deb [arch=$(dpkg --print-architecture) \
  signed-by=/usr/share/keyrings/docker-archive-keyring.asc] \
  https://download.docker.com/linux/debian \
  $(lsb_release -cs) stable" > /etc/apt/sources.list.d/docker.list
RUN apt-get update && apt-get install -y docker-ce-cli

# 3. Tạo nhóm docker (GID 999 thường trùng với group docker trên Linux/WSL)
# Hack: "|| true" để nếu group đã tồn tại thì không báo lỗi
RUN groupadd -g 999 docker || true
RUN usermod -aG docker jenkins

# Quay về user jenkins để chạy cho an toàn
USER jenkins