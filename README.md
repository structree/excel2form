ExcelToForm
====

Overview

## Description
ExcelToForm is a tool that generates HTML forms for use in websites and web applications in a batch from Excel.<br>
If you create a lot of HTML forms manually, careless mistakes are likely to occur. It was created for the purpose of reducing manual labor and mistakes.

## Usage
This tool is operated on the web screen interface after installation.

## Install
Operating environment<br>
PHP7.1<br>
apache2~<br>
<br>
Assume to run in docker environment.<br>
```
FROM php:7.1-apache
RUN apt-get update
RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libmcrypt-dev
RUN docker-php-ext-install gd
RUN docker-php-ext-install -j$(nproc) iconv mcrypt
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd
RUN apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip
COPY ./php/php.ini /usr/local/etc/php/
```

git clone https://github.com/structree/excel2form.git<br>
php composer.phar init<br>
php composer.phar install

## Contribution

## Licence

[MIT](https://github.com/structree/excel2form/blob/master/LICENSE)

## Author

[structree](https://github.com/structree)

## Contact
structree@gmail.com
