#!/bin/bash

# Script de Instalação do Sistema de Produtos
# Autor: Ronye Vantzing
# Data: 2025-07-26

set -e

# Script de Instalação do Sistema
# Atualização do sistema e instalação de dependências

echo "Atualizando sistema e instalando pacotes..."
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.2 composer git php8.2-sqlite3 php-xml

echo "Criando estrutura!"
mkdir -p sistema
sudo chown -R www-data:www-data ./sistema
sudo chmod -R 755 ./sistema
cd sistema

COMPOSER_ALLOW_SUPERUSER=1 composer init \
  --no-interaction \
  --name="user/teste" \
  --description="Projeto feito para o DEV{Evolution} ofertado pela IXC Soft S.A." \
  --author="Ronye Vantzing" \
  --stability=stable \
  --type=project \
  --license=MIT \
  --autoload="psr-4:App\\=app/"
NEW_CONF='
{
    "name": "ronye011/app",
    "description": "Projeto feito para o DEV Evolution ofertado pela IXC Soft",
    "type": "project",
    "require": {
    },
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "authors": [
        {
            "name": "Ronye Vantzing",
            "email": "ronyevantzing1@gmail.com"
        }
    ],
    "minimum-stability": "stable"
}'

echo "$NEW_CONF" | sudo tee ./composer.json > /dev/null

rm -rf 'psr-4:App\=app'

echo "Clonando repo!"
git clone https://github.com/ronye011/Projeto-DevEvolution.git
cp -r Projeto-DevEvolution/* Projeto-DevEvolution/.* . 2>/dev/null
rm -rf Projeto-DevEvolution

chmod u+w composer.json
composer require dompdf/dompdf:^3.1

composer dump-autoload

sudo chown -R www-data:www-data /var/lib/php/sessions
sudo chmod 700 /var/lib/php/sessions

echo "Iniciando servidor!"
php -S localhost:8000
