FROM nginx:1.15-alpine

# ensure www-data user exists
RUN set -x \
  && addgroup -g 82 -S www-data \
  && adduser -u 82 -D -S -G www-data www-data

COPY config/ /etc/nginx/

WORKDIR /var/www
