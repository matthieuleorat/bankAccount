FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

ARG HOST_USER

RUN apt-get update && apt-get install -yq --no-install-recommends \
    poppler-utils \
    locales \
    curl \
    gpg-agent \
    software-properties-common \
    php7.4-fpm \
    php7.4-mbstring \
    #php7-mcrypt \
    php7.4-pgsql \
    php7.4-apcu \
    php7.4-curl \
    php7.4-intl \
    php7.4-xdebug \
    php7.4-xml \
    php7.4-zip \
    apt-transport-https \
    unzip \
    iproute2 \
&& apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuration PHP (FPM, CLI) + extensions
COPY 7.4/fpm/php-fpm.conf /etc/php/7.4/fpm/php-fpm.conf
COPY 7.4/fpm/pool.d/www.conf /etc/php/7.4/fpm/pool.d/www.conf
COPY 7.4/usr/local/etc/php/conf.d/zz-xdebug-settings.ini /etc/php/7.4/fpm/conf.d/zz-xdebug-settings.ini

COPY 7.4/mods-available/php-custom.ini /etc/php/7.4/mods-available/php-custom.ini
RUN ln -s /etc/php/7.4/mods-available/php-custom.ini /etc/php/7.4/fpm/conf.d/php-custom.ini
RUN ln -s /etc/php/7.4/mods-available/php-custom.ini /etc/php/7.4/cli/conf.d/php-custom.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# nvm environment variables
ENV NVM_DIR $HOME/.nvm
ENV NODE_VERSION 8.9.0

# install nvm
# https://github.com/creationix/nvm#install-script
RUN curl --silent -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.8/install.sh | bash

# install node and npm
RUN source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# add node and npm to path so the commands are available
ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

## Yarn install
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
&& echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
&& apt-get update \
&& apt-get install -y yarn

WORKDIR /var/www/html

RUN usermod -u ${HOST_USER} www-data

EXPOSE 9000

RUN service php7.4-fpm start

CMD php-fpm7.4 -F