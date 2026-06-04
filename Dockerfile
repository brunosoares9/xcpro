FROM php:8.3-apache

# Habilita mod_rewrite para o .htaccess funcionar
RUN a2enmod rewrite

# Permite que o Apache leia .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copia os arquivos do projeto
COPY . /var/www/html/

# Permissão
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
