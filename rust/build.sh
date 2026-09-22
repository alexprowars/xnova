#!/bin/sh
set -eu

rust_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
project_dir=$(dirname -- "$rust_dir")

case "$(uname -s)" in
    Darwin) extension=dylib ;;
    Linux) extension=so ;;
    *) echo 'Supported build platforms: macOS and Linux.' >&2; exit 1 ;;
esac

if [ "$#" -ne 0 ]; then
    echo 'Usage: ./rust/build.sh (builds for the current host)' >&2
    exit 1
fi

if ! command -v cargo >/dev/null 2>&1; then
    echo 'Cargo is required. Install Rust using https://rustup.rs/.' >&2
    exit 1
fi

host=$(rustc -vV | sed -n 's/^host: //p')
target_dir="$rust_dir/target/production"

cargo build --manifest-path "$rust_dir/Cargo.toml" --locked --release \
    --package battle_engine_ffi --lib --no-default-features \
    --target "$host" --target-dir "$target_dir"

library="libbattle_engine_ffi.$extension"
destination="$project_dir/storage/$library"
temporary=$(mktemp "$destination.XXXXXX")

trap 'rm -f "$temporary"' EXIT HUP INT TERM
cp "$target_dir/$host/release/$library" "$temporary"
chmod 755 "$temporary"
mv -f "$temporary" "$destination"

printf 'Built %s for %s\n' "$destination" "$host"
