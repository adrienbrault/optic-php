ARG PHP_VERSION

# See https://github.com/thecodingmachine/docker-images-php
FROM thecodingmachine/php:${PHP_VERSION}-v4-cli

RUN sudo sh -c "$(curl --location https://taskfile.dev/install.sh)" -- -d -b /usr/local/bin

ENV PATH="${PATH}:./vendor/bin"
