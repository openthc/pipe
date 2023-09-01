#!/bin/bash
#
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail


BIN_SELF=$(readlink -f "$0")
APP_ROOT=$(dirname "$BIN_SELF")
cd "$APP_ROOT"

composer update --no-ansi --no-dev --no-progress --quiet --classmap-authoritative

npm install --quiet >/dev/null

mkdir -p webroot/vendor/bootstrap webroot/vendor/jquery webroot/vendor/libsodium/

cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js        webroot/js/
cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js        webroot/vendor/bootstrap/

cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map    webroot/js/
cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map    webroot/vendor/bootstrap/

cp node_modules/bootstrap/dist/css/bootstrap.min.css      webroot/css/
cp node_modules/bootstrap/dist/css/bootstrap.min.css      webroot/vendor/bootstrap/

cp node_modules/bootstrap/dist/css/bootstrap.min.css.map  webroot/css/
cp node_modules/bootstrap/dist/css/bootstrap.min.css.map  webroot/vendor/bootstrap/

cp node_modules/jquery/dist/jquery.min.js                 webroot/js/
cp node_modules/jquery/dist/jquery.min.js                 webroot/vendor/jquery/


# libsodium is a special case
tmp=$(mktemp -d)
cd "$tmp"
git clone https://github.com/jedisct1/libsodium.js.git ./
git checkout 0.7.11
# cp dist/browsers/sodium.js
install -D dist/browsers/sodium.js "$APP_ROOT/webroot/vendor/libsodium/"
cd "$APP_ROOT"


#
# Document CrashCourse?
# mkdir -p ./webroot/crash-course

#asciidoctor \
#	--verbose \
#	--backend=html5 \
#	--require=asciidoctor-diagram \
#	--section-numbers \
#	--out-file=./webroot/crash-course.html \
#	./content/crash-course.ad

#asciidoctor \
#	--verbose \
#	--backend=revealjs \
#	--require=asciidoctor-diagram \
#	--require=asciidoctor-revealjs \
#	--out-file=./webroot/crash-course-slides.html \
#	./content/crash-course.ad
