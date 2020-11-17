FROM php:7.4-cli

# Install some libs
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y zip unzip wget curl

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user to match host (assuming host is with ID 1000, which is the default)
RUN useradd -m -s /bin/bash -u 1000 app

USER app
