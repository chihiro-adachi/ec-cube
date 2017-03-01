#!/bin/bash

#
# リリース用パッケージ作成スクリプト
#
# GitHubでリリースタグを作成した際に, リリース用パッケージを作成するスクリプトです.
# zipおよびtar.gzのアーカイブを作成し、各アーカイブのmd5を出力します.
# .travis.ymlのbefore_deployから呼び出すことを想定しています.
#
# ローカルで使用する場合は, 以下の環境変数で対象のレポジトリ/タグ(ブランチ)を指定することができます。
#
# - TRAVIS_REPO_SLUG: デフォルトでは`EC-CUBE/ec-cube`が設定されます.
# - TRAVIS_TAG: デフォルトでは`master`が設定されます.
#
# 対象のレポジトリを設定する
# export TRAVIS_REPO_SLUG=[github_user_name]/ec-cube
# sh release.sh
#
# 対象のタグ(ブランチ)を設定する
# export TRAVIS_TAG=3.0.13
# sh release.sh
#

BASE_DIR=$(cd $(dirname $0) && pwd)/release
TAG=${TRAVIS_TAG:-"master"}
PACKAGE_NAME=eccube-${TAG}
CLONE_DIR=${BASE_DIR}/${PACKAGE_NAME}
REPO_SLUG=${TRAVIS_REPO_SLUG:-"EC-CUBE/ec-cube"}
REPOSITORY=https://github.com/${REPO_SLUG}.git

if [ -d ${BASE_DIR} ]; then
    rm -rf ${BASE_DIR}
fi

mkdir -p ${BASE_DIR}
cd ${BASE_DIR}

mkdir -p ${BASE_DIR}/package

echo ">> git clone ${REPOSITORY}..."
git clone --depth 1 --branch=${TAG} ${REPOSITORY} ${CLONE_DIR}

if [ ! -d ${CLONE_DIR} ]; then
    exit 1;
fi

if [ ! -f composer.phar ]; then
    echo ">> composer setup..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    if [ ! -f composer-setup.php ]; then
        exit 1;
    fi
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
fi

echo ">> composer install..."
php composer.phar install --no-dev --no-interaction -o -d ${CLONE_DIR}

echo ">> remove files..."
rm -rf ${CLONE_DIR}/.gitignore
rm -rf ${CLONE_DIR}/.github
rm -rf ${CLONE_DIR}/.gitmodules
rm -rf ${CLONE_DIR}/.scrutinizer.yml
rm -rf ${CLONE_DIR}/.travis.yml
rm -rf ${CLONE_DIR}/appveyor.yml
rm -rf ${CLONE_DIR}/.coveralls.yml
rm -rf ${CLONE_DIR}/app.json
rm -rf ${CLONE_DIR}/Procfile
rm -rf ${CLONE_DIR}/composer.phar
rm -rf ${CLONE_DIR}/LICENSE.txt
rm -rf ${CLONE_DIR}/README.md
rm -rf ${CLONE_DIR}/release.sh
find ${CLONE_DIR} -name ".git*" -print0 | xargs -0 rm -rf
find ${CLONE_DIR} -name ".git*" -type d -print0 | xargs -0 rm -rf

echo ">> set permission..."
chmod -R a+w ${CLONE_DIR}/html
chmod -R a+w ${CLONE_DIR}/app

echo ">> create archives..."
tar czfp package/${PACKAGE_NAME}.tar.gz ${PACKAGE_NAME} 1> /dev/null
zip -ry package/${PACKAGE_NAME}.zip ${PACKAGE_NAME} 1> /dev/null

echo ">> check md5sum..."
md5sum package/${PACKAGE_NAME}.tar.gz package/${PACKAGE_NAME}.zip

echo ">> done."
