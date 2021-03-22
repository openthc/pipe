#!/bin/bash
#
# OpenTHC Test Runner
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"

output_base="../webroot/test-output"
output_main="$output_base/index.html"
mkdir -p "$output_base"


#
# Lint
echo '<h1>Linting...</h1>' > "$output_main"
find ../bin/ ../lib/ ../sbin/ ../view/ -type f -name '*.php' -exec php -l {} \; \
	| grep -v 'No syntax' || true \
	2>&1 >"$output_base/phplint.txt"
[ -s "$output_base/phplint.txt" ] || echo "Appears Clean" >"$output_base/phplint.txt"


#
# PHPStan
echo '<h1>PHPStan...</h1>' > "$output_main"
../vendor/bin/phpstan analyze --error-format=junit --no-progress > "$output_base/phpstan.xml" || true
# [ -f "report.xsl" ] || wget -q 'https://openthc.com/pub/phpunit/report.xsl'
# xsltproc \
# 	--nomkdir \
# 	--output "$output_base/phpstan.html" \
# 	report.xsl \
# 	"$output_base/phpstan.xml"


#
# PHPUnit
echo '<h1>PHPUnit...</h1>' > "$output_main"
../vendor/bin/phpunit \
	--verbose \
	--log-junit "$output_base/phpunit.xml" \
	--testdox-html "$output_base/testdox.html" \
	--testdox-text "$output_base/testdox.txt" \
	--testdox-xml "$output_base/testdox.xml" \
	"$@" 2>&1 | tee "$output_base/phpunit.txt" || true


#
# Transform
echo '<h1>Transforming...</h1>' > "$output_main"
[ -f "report.xsl" ] || wget -q 'https://openthc.com/pub/phpunit/report.xsl'
xsltproc \
	--nomkdir \
	--output "$output_base/phpunit.html" \
	report.xsl \
	"$output_base/phpunit.xml"


#
# Final Output
dt=$(date)
note=$(tail -n1 "$output_base/phpunit.txt")

cat <<HTML > "$output_main"
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#247420">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<title>Test Result ${dt}</title>
</head>
<body>
<div class="container mt-4">
<div class="jumbotron">

<h1>Test Result ${dt}</h1>
<h2>${note}</h2>

<p>Linting: <a href="phplint.txt">phplint.txt</a></p>
<p>PHPStan: <a href="phpstan.xml">phpstan.xml</a></p>
<p>PHPUnit: <a href="phpunit.txt">phpunit.txt</a>, <a href="phpunit.xml">phpunit.xml</a> and <a href="phpunit.html">phpunit.html</a></p>
<p>Textdox: <a href="testdox.txt">testdox.txt</a>, <a href="testdox.xml">testdox.xml</a> and <a href="testdox.html">testdox.html</a></p>

</div>
</div>
</body>
</html>
HTML
