FROM alpine:3.13

# Install packages and remove default server definition
RUN apk --no-cache add php8=8.0.2-r0 php8-fpm php8-opcache php8-tokenizer php8-json \
    php8-openssl php8-curl php8-xml php8-dom php8-phar php8-xmlreader php8-session \
    php8-mbstring php8-gd php8-exif php8-zip php8-fileinfo php8-iconv \
    nginx supervisor curl tzdata htop \
    && rm /etc/nginx/conf.d/default.conf

# Symlink php8 => php
RUN ln -s /usr/bin/php8 /usr/bin/php

# Install PHP tools
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php8/php-fpm.d/www.conf
COPY docker/php.ini /etc/php8/conf.d/custom.ini

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
RUN mkdir -p /var/www/app
RUN mkdir -p /var/www/app/log
RUN mkdir -p /var/www/app/temp/cache
RUN mkdir -p /var/www/app/config.local

# Add application
WORKDIR /var/www/app

COPY . /var/www/app

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/app && \
  chown -R nobody.nobody /run && \
  chown -R nobody.nobody /var/lib/nginx && \
  chown -R nobody.nobody /var/log/nginx

# Switch to use a non-root user from here on
  USER nobody

RUN composer install \
	--ignore-platform-reqs \
	--no-interaction \
	--no-plugins \
	--no-scripts \
	--prefer-dist

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping


