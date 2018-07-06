rm -rf .temp
rm -rf ext
rm -rf /etc/php.d/zz-fast-rest.ini
zephir build
echo 'extension=bullhornfastrest.so' > /etc/php.d/zz-fast-rest.ini
service httpd restart