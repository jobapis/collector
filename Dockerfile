FROM php:cli

ADD ./ /app

WORKDIR /app

CMD ["php", "artisan", "list"]