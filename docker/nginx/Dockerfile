FROM nginx:1.27.2

# Set the working directory
WORKDIR /usr/share/nginx/html

RUN rm -f /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/nginx.conf

# Nginx config
COPY ./docker/nginx/nginx.conf /etc/nginx

# Sites configs
COPY ./docker/nginx/laravel.conf /etc/nginx/conf.d/laravel.conf
COPY ./docker/nginx/phpmyadmin.conf /etc/nginx/conf.d/phpmyadmin.conf

# SSL
COPY ./docker/nginx/ssl /etc/nginx/ssl

# Entrypoint script
COPY ./docker/nginx/nginx-entrypoint.sh /usr/local/bin/nginx-entrypoint.sh

# Make the entrypoint script executable
RUN chmod +x /usr/local/bin/nginx-entrypoint.sh

# Set the entrypoint to the script
ENTRYPOINT ["/usr/local/bin/nginx-entrypoint.sh"]

# Expose the port Nginx is running on
EXPOSE 80 443
