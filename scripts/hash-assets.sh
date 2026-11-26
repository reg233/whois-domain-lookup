#!/bin/sh

set -eu

mkdir -p assets

{
  echo '<?php'
  echo
  echo 'declare(strict_types=1);'
  echo
  echo 'return ['
  find public -type f ! -name '.*' | sort | while IFS= read -r file; do
    name=$(basename "$file")
    hash=$(md5sum "$file" | cut -c1-8)
    target="assets/${name%.*}.$hash.${name##*.}"
    cp "$file" "$target"
    printf '  "%s" => "%s",\n' "$file" "$target"
  done
  echo '];'
} > assets/manifest.php
