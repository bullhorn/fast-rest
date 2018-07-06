### Install

    pushd ~
    rm -rf tmp
    mkdir tmp
    cd tmp

    wget https://github.com/skvadrik/re2c/releases/download/0.16/re2c-0.16.tar.gz
    tar -xvf re2c-0.16.tar.gz
    push re2c-0.16
    ./configure --prefix=/usr/local
    make
    make install
    popd
    rm -rf re2c-0.16
    rm -rf re2c-0.16.tar.gz


    git clone git://github.com/phalcon/php-zephir-parser.git
    pushd php-zephir-parser
    ./install
    popd
    rm -rf php-zephir-parser
    echo '[Zephir Parser]
    extension=zephir_parser.so' > /etc/php.d/zephir-parser.ini

    service httpd restart

    git clone https://github.com/phalcon/zephir
    cd zephir
    ./install -c

    popd

### Build

    cd fastrest
    rm -rf /etc/php.d/zz-fast-rest.ini
    zephir build

    echo 'extension=bullhornfastrest.so' > /etc/php.d/zz-fast-rest.ini
    php -m | grep bullhorn #This should return 1 row
    service httpd restart